<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'platform' => ['nullable', 'string', 'max:50'],
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $request->string('token')],
            [
                'user_id' => $request->user()->id,
                'platform' => $request->input('platform'),
            ]
        );

        return ApiResponse::success('Device token registered successfully.');
    }

    public function unregister(Request $request): JsonResponse
    {
        $request->validate(['token' => ['required', 'string']]);

        DeviceToken::where('token', $request->string('token'))->delete();

        return ApiResponse::success('Device token removed successfully.');
    }
}
