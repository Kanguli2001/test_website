<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user and return an API token with expiry.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        // Create API token with automatic expiry (from config/sanctum.php)
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully. Please verify your email.',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 60 * 60, // 60 minutes in seconds (matches config)
            'expires_at' => now()->addMinutes(60)->toIso8601String(),
        ], 201);
    }

    /**
     * Login user and return an API token with expiry.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Create API token with automatic expiry
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 60 * 60, // 60 minutes in seconds
            'expires_at' => now()->addMinutes(60)->toIso8601String(),
        ], 200);
    }

    /**
     * Logout user by revoking all tokens.
     */
    public function logout(Request $request)
    {
        $request->user('sanctum')->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Get current authenticated user.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user('sanctum'),
        ], 200);
    }

    /**
     * Refresh the API token (revoke old, create new).
     */
    public function refresh(Request $request)
    {
        // Revoke all existing tokens
        $request->user('sanctum')->tokens()->delete();

        // Create new token
        $token = $request->user('sanctum')->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 60 * 60,
            'expires_at' => now()->addMinutes(60)->toIso8601String(),
        ], 200);
    }
}
