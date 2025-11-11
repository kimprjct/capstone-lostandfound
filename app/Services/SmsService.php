<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private Client $httpClient;
    private string $apiUrl;
    private string $apiKey;
    private string $senderName;

    public function __construct(?Client $client = null)
    {
        $this->httpClient = $client ?: new Client(['timeout' => 10]);
        $this->apiUrl = config('services.sms.url', env('SMS_API_URL', 'https://api.semaphore.co/api/v4/messages'));
        $this->apiKey = config('services.sms.key', env('SMS_API_KEY', ''));
        $this->senderName = config('services.sms.sender', env('SMS_SENDER_NAME', ''));
    }

    /**
     * Normalize phone number to ensure it has country code format.
     * Assumes Philippines (+63) if no country code is present.
     */
    private function normalizePhoneNumber(string $phoneNumber): string
    {
        // Remove all non-digit characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $phoneNumber);
        
        // If it starts with +, return as is
        if (strpos($cleaned, '+') === 0) {
            return $cleaned;
        }
        
        // If it starts with 0, replace with +63 (Philippines)
        if (strpos($cleaned, '0') === 0) {
            return '+63' . substr($cleaned, 1);
        }
        
        // If it starts with 63, add +
        if (strpos($cleaned, '63') === 0) {
            return '+' . $cleaned;
        }
        
        // If it's 9 digits (Philippines mobile), add +63
        if (strlen($cleaned) === 9) {
            return '+63' . $cleaned;
        }
        
        // If it's 10 digits and starts with 9, add +63
        if (strlen($cleaned) === 10 && strpos($cleaned, '9') === 0) {
            return '+63' . $cleaned;
        }
        
        // Return as is if we can't determine format
        return $cleaned;
    }

    /**
     * Send an SMS message.
     * Returns true on success, false otherwise.
     */
    public function send(string $recipientNumber, string $message): bool
    {
        if (!$this->apiKey || !$this->senderName) {
            Log::warning('SMS service missing API key or sender name', [
                'has_api_key' => !empty($this->apiKey),
                'has_sender_name' => !empty($this->senderName),
            ]);
            return false;
        }

        // Normalize phone number
        $normalizedNumber = $this->normalizePhoneNumber($recipientNumber);
        
        // Remove + for Semaphore API (it expects format like 639123456789)
        $apiNumber = str_replace('+', '', $normalizedNumber);

        try {
            // Semaphore API v4 format
            $payload = [
                'apikey' => $this->apiKey,
                'number' => $apiNumber,
                'message' => $message,
                'sendername' => $this->senderName,
            ];

            Log::info('Sending SMS', [
                'recipient' => $apiNumber,
                'sender' => $this->senderName,
                'message_length' => strlen($message),
                'api_url' => $this->apiUrl,
            ]);

            $response = $this->httpClient->post($this->apiUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'form_params' => $payload,
            ]);

            $status = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            
            Log::info('SMS API response', [
                'status' => $status,
                'response' => $responseBody,
            ]);

            if ($status >= 200 && $status < 300) {
                // Parse response to check for errors in body
                $responseData = json_decode($responseBody, true);
                
                // Semaphore API returns array with status
                if (is_array($responseData)) {
                    // Check if there's an error in the response
                    if (isset($responseData[0]['status']) && $responseData[0]['status'] !== 'QUEUED') {
                        Log::error('SMS send failed: API returned error status', [
                            'status' => $responseData[0]['status'],
                            'response' => $responseData,
                        ]);
                        return false;
                    }
                    
                    // Success if status is QUEUED or similar
                    if (isset($responseData[0]['status']) && in_array($responseData[0]['status'], ['QUEUED', 'SENT', 'PENDING'])) {
                        Log::info('SMS queued successfully', ['response' => $responseData]);
                        return true;
                    }
                }
                
                // If we get here and status is 200, assume success
                return true;
            }

            Log::error('SMS send failed: non-2xx status', [
                'status' => $status,
                'response' => $responseBody,
            ]);
            return false;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBody = $response ? $response->getBody()->getContents() : 'No response body';
            
            Log::error('SMS send client error', [
                'message' => $e->getMessage(),
                'status' => $response ? $response->getStatusCode() : 'N/A',
                'response' => $responseBody,
            ]);
            return false;
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $response = $e->getResponse();
            $responseBody = $response ? $response->getBody()->getContents() : 'No response body';
            
            Log::error('SMS send server error', [
                'message' => $e->getMessage(),
                'status' => $response ? $response->getStatusCode() : 'N/A',
                'response' => $responseBody,
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('SMS send error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}


