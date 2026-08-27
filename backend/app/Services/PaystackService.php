<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = (string) config('services.paystack.secret_key');
    }

    private function client()
    {
        return Http::withToken($this->secretKey)
            ->acceptJson()
            ->baseUrl($this->baseUrl)
            ->timeout(30);
    }

    public function initializeTransaction(string $email, int $amountInKobo, string $reference, array $metadata = []): array
    {
        $response = $this->client()->post('/transaction/initialize', [
            'email' => $email,
            'amount' => $amountInKobo,
            'reference' => $reference,
            'metadata' => $metadata,
        ]);

        return $response->json();
    }

    public function verifyTransaction(string $reference): array
    {
        $response = $this->client()->get("/transaction/verify/{$reference}");

        return $response->json();
    }
}
