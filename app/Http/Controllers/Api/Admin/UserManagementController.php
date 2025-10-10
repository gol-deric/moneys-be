<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * List all users (admin only).
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filter by tier
        if ($request->has('subscription_tier')) {
            $query->where('subscription_tier', $request->subscription_tier);
        }

        // Filter by is_guest
        if ($request->has('is_guest')) {
            $query->where('is_guest', $request->boolean('is_guest'));
        }

        // Filter by is_admin
        if ($request->has('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount('subscriptions')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Get single user details (admin only).
     */
    public function show(string $id): JsonResponse
    {
        $user = User::withCount('subscriptions')->findOrFail($id);

        return response()->json($user);
    }

    /**
     * Update user (admin only).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'full_name' => 'sometimes|string|max:255',
            'avatar_url' => 'sometimes|nullable|string|max:500',
            'is_admin' => 'sometimes|boolean',
            'is_guest' => 'sometimes|boolean',
            'subscription_tier' => 'sometimes|in:free,pro',
            'subscription_expires_at' => 'sometimes|nullable|date',
            'language' => 'sometimes|string|max:10',
            'currency' => 'sometimes|string|size:3',
            'theme' => 'sometimes|in:light,dark',
            'notifications_enabled' => 'sometimes|boolean',
            'email_notifications' => 'sometimes|boolean',
        ]);

        $user->update($request->only([
            'email',
            'full_name',
            'avatar_url',
            'is_admin',
            'is_guest',
            'subscription_tier',
            'subscription_expires_at',
            'language',
            'currency',
            'theme',
            'notifications_enabled',
            'email_notifications',
        ]));

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Delete user (admin only).
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'error' => 'Cannot delete your own account',
                'message' => 'You cannot delete your own admin account.',
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Create new user (admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'full_name' => 'required|string|max:255',
            'is_admin' => 'sometimes|boolean',
            'subscription_tier' => 'sometimes|in:free,pro',
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'is_admin' => $request->boolean('is_admin', false),
            'is_guest' => false,
            'subscription_tier' => $request->get('subscription_tier', 'free'),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Get user's subscriptions (admin only).
     */
    public function subscriptions(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $subscriptions = $user->subscriptions()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($subscriptions);
    }
}
