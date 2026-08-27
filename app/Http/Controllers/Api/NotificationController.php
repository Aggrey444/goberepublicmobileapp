<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->limit($request->integer('limit', 50))
            ->get();

        return ApiResponse::success('Notifications retrieved successfully.', NotificationResource::collection($notifications));
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        abort_if($notification->user_id !== $request->user()->id, 403, 'You do not have access to this notification.');

        $notification->update(['read_at' => now()]);

        return ApiResponse::success('Notification marked as read.', new NotificationResource($notification->fresh()));
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return ApiResponse::success('All notifications marked as read.');
    }
}
