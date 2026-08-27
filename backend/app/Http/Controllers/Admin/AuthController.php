<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController
{
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return ApiResponse::error('Invalid credentials.', null, 401);
        }

        if (!$user->isAdmin()) {
            return ApiResponse::error('You are not authorized to access the admin area.', null, 403);
        }

        if ($user->status !== User::STATUS_ACTIVE) {
            return ApiResponse::error('Your account is inactive.', null, 403);
        }

        $token = $user->createToken('admin')->plainTextToken;

        return ApiResponse::success('Admin login successful.', [
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function me(\Illuminate\Http\Request $request): JsonResponse
    {
        return ApiResponse::success('Profile retrieved successfully.', new UserResource($request->user()));
    }

    public function logout(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success('Logged out successfully.');
    }
}
