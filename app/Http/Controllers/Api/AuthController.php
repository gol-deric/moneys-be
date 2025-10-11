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
     * If device_id is provided and matches a guest account, merge the guest account into the new real account.
     */
    public function register(Request $request): JsonResponse
    {
        $isGuest = $request->boolean('is_guest', false);

        $rules = [
            'email' => 'required|email|unique:users,email',
            'full_name' => 'required|string|max:255',
            'device_id' => 'nullable|string|max:255',
        ];

        // If not a guest user, password is required
        if (!$isGuest) {
            $rules['password'] = 'required|string|min:8';
        }

        $request->validate($rules);

        // Check if there's an existing guest account with the same device_id
        $guestAccount = null;
        if (!$isGuest && $request->filled('device_id')) {
            $guestAccount = User::where('device_id', $request->device_id)
                ->where('is_guest', true)
                ->first();
        }

        // If guest account exists, upgrade it to a real account
        if ($guestAccount) {
            $guestAccount->update([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'full_name' => $request->full_name,
                'is_guest' => false,
                'last_logged_in' => now(),
            ]);

            $token = $guestAccount->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => $guestAccount->fresh(),
                'token' => $token,
                'message' => 'Guest account upgraded successfully',
            ], 200);
        }

        // Create new user account
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($isGuest ? 'Moneys@2025' : $request->password),
            'full_name' => $request->full_name,
            'is_guest' => $isGuest,
            'device_id' => $request->device_id,
            'last_logged_in' => now(),
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
            'device_id' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Update last logged in timestamp and device_id
        $user->update([
            'last_logged_in' => now(),
            'device_id' => $request->device_id ?? $user->device_id,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->fresh(),
            'token' => $token,
        ]);
    }

    /**
     * Create guest user.
     */
    public function guest(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string|max:255',
        ]);

        // Check if guest account with this device_id already exists
        $existingGuest = User::where('device_id', $request->device_id)
            ->where('is_guest', true)
            ->first();

        if ($existingGuest) {
            // Update last logged in
            $existingGuest->update(['last_logged_in' => now()]);

            $token = $existingGuest->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => $existingGuest->fresh(),
                'token' => $token,
                'message' => 'Existing guest account retrieved',
            ], 200);
        }

        // Create new guest user
        $user = User::create([
            'email' => 'guest_' . Str::uuid() . '@moneys.app',
            'password' => Hash::make('Moneys@2025'),
            'full_name' => 'Guest User',
            'is_guest' => true,
            'device_id' => $request->device_id,
            'last_logged_in' => now(),
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
