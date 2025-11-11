<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register new user (API).
     */
    public function register(Request $request)
{
    $request->validate([
        'first_name'   => 'required|string|max:255',
        'middle_name'  => 'required|string|max:255',
        'last_name'    => 'required|string|max:255',
        'phone_number' => 'required|string|max:20',
        'email'        => 'required|email|unique:users,email',
        'password'     => 'required|string|min:6|confirmed',
    ]);

    $user = User::create([
        'first_name'   => $request->first_name,
        'middle_name'  => $request->middle_name,
        'last_name'    => $request->last_name,
        'phone_number' => $request->phone_number,
        'email'        => $request->email,
        'password'     => Hash::make($request->password),
        'UserTypeID'   => 3, // Default to User (UserTypeID = 3)
    ]);

    $token = $user->createToken('mobile-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'user'    => $user,
        'token'   => $token
    ], 201);
}

    /**
     * Login user (API).
     */
    public function login(Request $request)
    {
        $key = Str::lower((string) $request->input('email')).'|'.$request->ip();
        $maxAttempts = 10;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again in '.$seconds.' seconds.'
            ], 429);
        }

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key);
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        RateLimiter::clear($key);

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user'    => $user,
            'token'   => $token
        ]);
    }

    /**
     * Get logged-in user.
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Update user profile.
     */
}
