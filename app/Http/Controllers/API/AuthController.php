<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Login and return a personal access token.
     */
     public function register(Request $request)
    {
        // Determine validation rules based on what fields are present
        $validationRules = [
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:6',
        ];
        
        // For mobile app request (uses first_name, last_name)
        if ($request->has('first_name')) {
            $validationRules['first_name'] = 'required|string|max:100';
            $validationRules['last_name'] = 'required|string|max:100';
            // Middle name is required by database but can be empty string
            $validationRules['middle_name'] = 'nullable|string|max:100';
            // Phone is required by the database
            $validationRules['phone_number'] = 'required|string|max:20';
            // Address is required by the mobile form
            $validationRules['address'] = 'required|string|max:255';
        } 
        // For web request (might be using name)
        else if ($request->has('name')) {
            $validationRules['name'] = 'required|string|max:255';
        }
        // If neither is present, require first_name and last_name
        else {
            $validationRules['first_name'] = 'required|string|max:100';
            $validationRules['last_name'] = 'required|string|max:100';
        }
        
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            // Log the validation errors for debugging
            Log::error('Registration validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);
            
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Map role to UserTypeID: admin=1, tenant=2, user=3
        $roleMap = [
            'admin' => 1,
            'tenant' => 2,
            'user' => 3,
        ];
        $requestedRole = $request->input('role', 'user');
        $userTypeId = $roleMap[$requestedRole] ?? 3; // Default to User (3)

        // Create user data array with required fields
        $userData = [
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'UserTypeID' => $userTypeId,
        ];
        
        // Handle name fields based on what's provided in the request
        if ($request->has('first_name')) {
            $userData['first_name'] = $request->first_name;
            $userData['last_name'] = $request->last_name;
            $userData['middle_name'] = $request->middle_name ?? ''; // Empty string if not provided
            $userData['phone_number'] = $request->phone_number;
            $userData['address'] = $request->address ?? ''; // Empty string if not provided
        }
        // If only 'name' is present (web form), split it into components
        else if ($request->has('name')) {
            $nameParts = explode(' ', $request->name);
            
            if (count($nameParts) >= 2) {
                $userData['first_name'] = $nameParts[0];
                $userData['last_name'] = end($nameParts);
                
                // If there are more than 2 parts, treat middle parts as middle name
                if (count($nameParts) > 2) {
                    $middleNames = array_slice($nameParts, 1, count($nameParts) - 2);
                    $userData['middle_name'] = implode(' ', $middleNames);
                } else {
                    $userData['middle_name'] = '';
                }
            } else {
                // Only one word name, treat as first name
                $userData['first_name'] = $request->name;
                $userData['last_name'] = $request->name; // Just duplicate it
                $userData['middle_name'] = '';
            }
            
            // Since phone is required, set a placeholder if not provided
            if (!$request->has('phone_number')) {
                $userData['phone_number'] = 'Not provided';
            } else {
                $userData['phone_number'] = $request->phone_number;
            }
        }

        $user = User::create($userData);

        // Generate verification code and send email
        $verificationCode = $user->generateVerificationCode();
        
        try {
            // Send email using notification
            $user->notify(new \App\Notifications\SendVerificationCode($verificationCode));
            
            Log::info('Verification email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'verification_code' => $verificationCode,
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                    'username' => config('mail.mailers.smtp.username') ? '***' : 'not set',
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name')
                ]
            ]);
        } catch (\Swift_TransportException $e) {
            // SMTP/Transport errors - most common issue
            Log::error('SMTP Transport error sending verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'verification_code' => $verificationCode,
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port')
                ]
            ]);
        } catch (\Swift_RfcComplianceException $e) {
            // Email address format errors
            Log::error('Email address format error', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'verification_code' => $verificationCode
            ]);
        } catch (\Exception $e) {
            // Other errors
            Log::error('Failed to send verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'verification_code' => $verificationCode
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Registration successful! Please check your email for the verification code.',
            'user'    => [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email_verified_at' => $user->email_verified_at
            ],
            'email_sent' => true
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $key = Str::lower((string) $request->input('email')).'|'.$request->ip();
        $maxAttempts = 10;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'status' => false,
                'message' => 'Too many login attempts. Please try again in '.$seconds.' seconds.',
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key);
            return response()->json([
                'status'  => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'status'  => false,
                'message' => 'Please verify your email address before logging in. Check your email for a verification link.',
                'email_verified' => false,
                'user_id' => $user->id
            ], 403);
        }

        // Successful login; clear attempts
        RateLimiter::clear($key);

        // delete old tokens before issuing a new one (optional)
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user
        ], 200);
    }
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            // currentAccessToken() may be typed as HasAbilities by some analyzers.
            // Use a phpdoc to tell the analyzer this is a PersonalAccessToken instance.
            /** @var PersonalAccessToken|null $token */
            $token = $user->currentAccessToken();

            if ($token instanceof PersonalAccessToken) {
                // analyzer and runtime both happy
                $token->delete();
            } else {
                // Fallback: if analyzer still can't see the id, delete by token id if present
                // (this branch is defensive — usually token will be PersonalAccessToken)
                if (is_object($token) && property_exists($token, 'id')) {
                    $user->tokens()->where('id', $token->id)->delete();
                }
            }
        }

        return response()->json(['message' => 'Logged out'], 200);
    }
     public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'user'   => $request->user()
        ], 200);
    }
   public function user(Request $request)
    {
        $user = $request->user(); // ✅ safer, uses sanctum auth

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'id'           => $user->id,
            'first_name'   => $user->first_name,
            'middle_name'  => $user->middle_name,
            'last_name'    => $user->last_name,
            'address'      => $user->address,
            'phone_number' => $user->phone_number,
            'email'        => $user->email,
            'role'         => $user->role,
        ]);
    }
    
    /**
     * Update user profile
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated',
            ], 401);
        }
        
        $validator = Validator::make($request->all(), [
            'first_name'   => 'sometimes|required|string|max:100',
            'middle_name'  => 'sometimes|nullable|string|max:100',
            'last_name'    => 'sometimes|required|string|max:100',
            'address'      => 'sometimes|nullable|string|max:255',
            'phone_number' => 'sometimes|nullable|string|max:20',
            'email'        => 'sometimes|required|email|max:255|unique:users,email,'.$user->id,
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }
        
        // Update fields that are present in the request
        if ($request->has('first_name')) {
            $user->first_name = $request->first_name;
        }
        
        if ($request->has('middle_name')) {
            $user->middle_name = $request->middle_name;
        }
        
        if ($request->has('last_name')) {
            $user->last_name = $request->last_name;
        }
        
        if ($request->has('address')) {
            $user->address = $request->address;
        }
        
        if ($request->has('phone_number')) {
            $user->phone_number = $request->phone_number;
        }
        
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        
        // Save the changes
        $user->save();
        
        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully',
            'user'    => $user
        ]);
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verifyEmail(Request $request, $id, $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid verification link'
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => true,
                'message' => 'Email already verified'
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully! You can now log in to your account.'
        ]);
    }

    /**
     * Resend the email verification notification.
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        // Generate new verification code and send email
        $verificationCode = $user->generateVerificationCode();
        
        try {
            $user->notify(new \App\Notifications\SendVerificationCode($verificationCode));
            Log::info('Verification email resent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'verification_code' => $verificationCode
            ]);
            
            return response()->json([
                'status' => true,
                'message' => 'Verification code sent successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to resend verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'verification_code' => $verificationCode // Log code for debugging
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to send verification email. Please check your email configuration or contact support.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Verify email with verification code.
     */
    public function verifyEmailWithCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'verification_code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        if ($user->verifyCode($request->verification_code)) {
            // Refresh the user model to get updated data
            $user = $user->fresh();
            
            // Generate token for automatic login
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Email verified successfully!',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email_verified_at' => $user->email_verified_at
                ]
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired verification code'
            ], 400);
        }
    }
}
