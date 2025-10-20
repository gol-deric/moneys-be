<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AddDeviceRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/user/register",
     *     summary="Register a new user",
     *     description="Create a new user account (regular or guest) with device information",
     *     tags={"Authentication"},
     *     security={{"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"full_name", "device_id", "is_guest"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Required if is_guest=false"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Required if is_guest=false"),
     *             @OA\Property(property="full_name", type="string", example="John Doe"),
     *             @OA\Property(property="device_id", type="string", example="device-uuid-123"),
     *             @OA\Property(property="device_type", type="string", enum={"android", "ios", "web"}, example="android"),
     *             @OA\Property(property="device_name", type="string", example="Samsung Galaxy S21"),
     *             @OA\Property(property="fcm_token", type="string", example="fcm-token-123"),
     *             @OA\Property(property="is_guest", type="boolean", example=false),
     *             @OA\Property(property="language", type="string", example="en"),
     *             @OA\Property(property="currency", type="string", example="USD")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="device", ref="#/components/schemas/UserDevice"),
     *                 @OA\Property(property="token", type="string", example="1|abc123...")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Invalid API key")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'full_name' => $request->full_name,
                'is_guest' => $request->is_guest,
                'language' => $request->language ?? 'en',
                'currency' => $request->currency ?? 'USD',
                'is_active' => true,
                'last_logged_in' => now(),
            ]);

            // Create device
            $device = UserDevice::create([
                'user_id' => $user->id,
                'device_id' => $request->device_id,
                'device_name' => $request->device_name,
                'device_type' => $request->device_type,
                'fcm_token' => $request->fcm_token,
                'is_active' => true,
            ]);

            // Create token
            $token = $user->createToken('auth-token')->plainTextToken;

            DB::commit();

            return $this->success([
                'user' => $user,
                'device' => $device,
                'token' => $token,
            ], 'User registered successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/login",
     *     summary="Login user",
     *     description="Authenticate user with email and password",
     *     tags={"Authentication"},
     *     security={{"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="2|xyz789...")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials', 401);
        }

        $user = Auth::user();

        // Update last logged in
        $user->update(['last_logged_in' => now()]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/forgot-password",
     *     summary="Request password reset",
     *     description="Send password reset link to user's email",
     *     tags={"Authentication"},
     *     security={{"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset email sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset link sent to your email")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success(null, 'Password reset link sent to your email');
        }

        return $this->error('Unable to send reset link', 500);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/reset-password",
     *     summary="Reset password",
     *     description="Reset user password using token from email",
     *     tags={"Authentication"},
     *     security={{"apiKey": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "email", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", example="zCrCeWf0tz0oFpTw8v1viNFYIlzqKaeiS6xmXQmpeNSrWZbbAGmMx5a6IHR4"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset successfully")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid or expired token"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success(null, 'Password reset successfully');
        }

        return $this->error('Invalid or expired token', 400);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/me",
     *     summary="Get authenticated user",
     *     description="Get current user information",
     *     tags={"Authentication"},
     *     security={{"apiKey": {}}, {"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User information retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function me(): JsonResponse
    {
        return $this->success(Auth::user());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/device",
     *     summary="Add new device",
     *     description="Register a new device for authenticated user",
     *     tags={"Devices"},
     *     security={{"apiKey": {}}, {"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"device_id", "device_name", "device_type"},
     *             @OA\Property(property="device_id", type="string", example="device-uuid-456"),
     *             @OA\Property(property="device_name", type="string", example="iPhone 13 Pro"),
     *             @OA\Property(property="device_type", type="string", enum={"android", "ios", "web"}, example="ios"),
     *             @OA\Property(property="fcm_token", type="string", example="fcm-token-456", description="Optional FCM token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Device added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Device added successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserDevice")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function addDevice(AddDeviceRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if device already exists for this user
            $existingDevice = UserDevice::where('user_id', $user->id)
                ->where('device_id', $request->device_id)
                ->first();

            if ($existingDevice) {
                // Update existing device
                $existingDevice->update([
                    'device_name' => $request->device_name,
                    'device_type' => $request->device_type,
                    'fcm_token' => $request->fcm_token,
                    'is_active' => true,
                ]);

                return $this->success($existingDevice, 'Device updated successfully');
            }

            // Create new device
            $device = UserDevice::create([
                'user_id' => $user->id,
                'device_id' => $request->device_id,
                'device_name' => $request->device_name,
                'device_type' => $request->device_type,
                'fcm_token' => $request->fcm_token,
                'is_active' => true,
            ]);

            return $this->success($device, 'Device added successfully', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to add device: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/user/device/{device_id}",
     *     summary="Remove device",
     *     description="Delete a device for authenticated user (logout action)",
     *     tags={"Devices"},
     *     security={{"apiKey": {}}, {"sanctum": {}}},
     *     @OA\Parameter(
     *         name="device_id",
     *         in="path",
     *         required=true,
     *         description="Device ID to remove",
     *         @OA\Schema(type="string", example="device-uuid-456")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Device removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Device removed successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Device not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function removeDevice(string $device_id): JsonResponse
    {
        try {
            $user = Auth::user();

            $device = UserDevice::where('user_id', $user->id)
                ->where('device_id', $device_id)
                ->first();

            if (!$device) {
                return $this->error('Device not found', 404);
            }

            $device->delete();

            return $this->success(null, 'Device removed successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to remove device: ' . $e->getMessage(), 500);
        }
    }
}
