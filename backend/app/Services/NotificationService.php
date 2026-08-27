<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function __construct(
        protected PushNotificationService $push,
    ) {
    }

    /**
     * Create a notification for a user and attempt to deliver a push.
     *
     * @param  int  $userId
     * @param  string  $title
     * @param  string  $body
     * @param  string  $type
     * @param  array<string, mixed>  $data
     */
    public function notify(int $userId, string $title, string $body, string $type = 'general', array $data = []): Notification
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => data_get($data, 'payload', $data),
        ]);

        $tokens = DeviceToken::where('user_id', $userId)->pluck('token')->all();

        if ($tokens) {
            $this->push->send($tokens, $title, $body, $data);
        }

        return $notification;
    }
}
