<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends push notifications to mobile devices via the Expo Push API.
 *
 * The customer mobile app registers an Expo push token (ExpoPushToken[...])
 * which is stored in the device_tokens table. This service targets the
 * Expo push service endpoint so no native FCM/APNs app secrets are required.
 */
class PushNotificationService
{
    protected string $endpoint = 'https://exp.host/--/api/v2/push/send';

    protected ?string $accessToken;

    public function __construct()
    {
        $this->accessToken = config('services.expo.access_token') ?: null;
    }

    public function client(): PendingRequest
    {
        $client = Http::timeout(15)->acceptJson()->asJson();

        if ($this->accessToken) {
            $client->withToken($this->accessToken);
        }

        return $client;
    }

    /**
     * @param  array<int, string>  $tokens  Expo push tokens
     */
    public function send(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokens = array_values(array_filter($tokens));

        if (empty($tokens)) {
            return ['success' => true, 'skipped' => true, 'count' => 0];
        }

        $messages = array_map(fn (string $token) => [
            'to' => $token,
            'title' => $title,
            'body' => $body,
            'sound' => 'default',
            'priority' => 'high',
            'data' => $data,
        ], $tokens);

        try {
            $response = $this->client()->post($this->endpoint, $messages);

            if ($response->failed()) {
                Log::warning('Expo push request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return ['success' => false, 'http_status' => $response->status()];
            }

            $body = $response->json();

            $errors = data_get($body, 'data', []);
            $failedTickets = collect($errors)
                ->filter(fn ($t) => data_get($t, 'status') === 'error')
                ->count();

            return [
                'success' => $failedTickets === 0,
                'count' => count($tokens),
                'failed' => $failedTickets,
            ];
        } catch (\Throwable $e) {
            Log::error('Expo push exception.', ['message' => $e->getMessage()]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
