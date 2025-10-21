<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreateSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/subscription",
     *     summary="Get all subscriptions for authenticated user",
     *     tags={"Subscriptions"},
     *     security={{"apiKey": {}, "sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscriptions retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Subscription")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return $this->error('Unauthenticated. Please provide a valid Bearer token.', 401);
        }

        $subscriptions = $user->subscriptions()->get();

        return $this->success($subscriptions, 'Subscriptions retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscription",
     *     summary="Create a new subscription",
     *     tags={"Subscriptions"},
     *     security={{"apiKey": {}, "sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateSubscriptionRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Subscription created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscription created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Subscription")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return $this->error('Unauthenticated. Please provide a valid Bearer token.', 401);
        }

        $subscription = $user->subscriptions()->create($request->validated());

        return $this->success($subscription, 'Subscription created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscription/{subscription_id}",
     *     summary="Get a specific subscription",
     *     tags={"Subscriptions"},
     *     security={{"apiKey": {}, "sanctum": {}}},
     *     @OA\Parameter(
     *         name="subscription_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscription retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Subscription")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription not found"
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return $this->error('Unauthenticated. Please provide a valid Bearer token.', 401);
        }

        $subscription = $user->subscriptions()->findOrFail($id);

        return $this->success($subscription, 'Subscription retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/subscription/{subscription_id}",
     *     summary="Update a subscription",
     *     tags={"Subscriptions"},
     *     security={{"apiKey": {}, "sanctum": {}}},
     *     @OA\Parameter(
     *         name="subscription_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSubscriptionRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscription updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Subscription")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(UpdateSubscriptionRequest $request, string $id): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return $this->error('Unauthenticated. Please provide a valid Bearer token.', 401);
        }

        $subscription = $user->subscriptions()->findOrFail($id);
        $subscription->update($request->validated());

        return $this->success($subscription, 'Subscription updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/subscription/{subscription_id}",
     *     summary="Delete a subscription",
     *     tags={"Subscriptions"},
     *     security={{"apiKey": {}, "sanctum": {}}},
     *     @OA\Parameter(
     *         name="subscription_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscription deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription not found"
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return $this->error('Unauthenticated. Please provide a valid Bearer token.', 401);
        }

        $subscription = $user->subscriptions()->findOrFail($id);
        $subscription->delete();

        return $this->success(null, 'Subscription deleted successfully');
    }
}
