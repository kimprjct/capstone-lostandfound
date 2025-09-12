<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

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
            // Middle name is optional
            $validationRules['middle_name'] = 'nullable|string|max:100';
            // Phone is required by the database
            $validationRules['phone_number'] = 'required|string|max:20';
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
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Create user data array with required fields
        $userData = [
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $request->input('role', 'user'),
        ];
        
        // Handle name fields based on what's provided in the request
        if ($request->has('first_name')) {
            $userData['first_name'] = $request->first_name;
            $userData['last_name'] = $request->last_name;
            $userData['middle_name'] = $request->middle_name ?? ''; // Empty string if not provided
            $userData['phone_number'] = $request->phone_number;
            
            // Add optional fields
            if ($request->has('address')) {
                $userData['address'] = $request->address;
            }
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

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'User registered successfully',
            'token'   => $token,
            'user'    => $user
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
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
            return response()->json([
                'status'  => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

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
}
