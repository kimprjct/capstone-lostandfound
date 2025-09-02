<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
        'role'         => 'user',
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
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

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
