<?php

namespace App\Services;

use GuzzleHttp\Client;

class ExpoPushService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://exp.host/--/api/v2/push/',
            'timeout' => 5.0,
        ]);
    }

    /**
     * Send push notification via Expo.
     * @param array $tokens array of expo push tokens
     * @param string $title
     * @param string $body
     * @param array $data optional payload
     */
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        if (empty($tokens)) return;
        $messages = [];
        foreach ($tokens as $token) {
            $messages[] = [
                'to' => $token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'priority' => 'high',
            ];
        }
        $this->client->post('send', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $messages,
        ]);
    }
}


