<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (empty($validated['email']) && empty($validated['phone'])) {
            return ApiResponse::error('Provide at least an email or a phone number.', null, 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $token = $user->createToken('customer')->plainTextToken;

        return ApiResponse::success('Account created successfully.', [
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $query = User::query();
        if (!empty($credentials['email'])) {
            $query->where('email', $credentials['email']);
        } else {
            $query->where('phone', $credentials['phone']);
        }

        $user = $query->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return ApiResponse::error('Invalid credentials.', null, 401);
        }

        if (!$user->isCustomer() || $user->status !== User::STATUS_ACTIVE) {
            return ApiResponse::error('You are not authorized to access the customer application.', null, 403);
        }

        $token = $user->createToken('customer')->plainTextToken;

        return ApiResponse::success('Login successful.', [
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success('Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success('Profile retrieved successfully.', new UserResource($request->user()));
    }
}
