<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $isGuest = $request->boolean('is_guest', false);

        $rules = [
            'email' => 'required|email|unique:users,email',
            'full_name' => 'required|string|max:255',
        ];

        // If not a guest user, password is required
        if (!$isGuest) {
            $rules['password'] = 'required|string|min:8';
        }

        $request->validate($rules);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($isGuest ? 'Moneys@2025' : $request->password),
            'full_name' => $request->full_name,
            'is_guest' => $isGuest,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Create guest user.
     */
    public function guest(): JsonResponse
    {
        $user = User::create([
            'email' => 'guest_' . Str::uuid() . '@moneys.app',
            'password' => Hash::make(Str::random(32)),
            'full_name' => 'Guest User',
            'is_guest' => true,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Refresh token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Upgrade guest user to regular user.
     */
    public function upgradeGuest(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->is_guest) {
            return response()->json(['message' => 'User is not a guest'], 400);
        }

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'full_name' => 'required|string|max:255',
        ]);

        $user->update([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'is_guest' => false,
        ]);

        return response()->json([
            'user' => $user->fresh(),
            'message' => 'Guest account upgraded successfully',
        ]);
    }
}
