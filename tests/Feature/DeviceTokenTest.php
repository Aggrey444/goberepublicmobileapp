<?php

namespace Tests\Feature;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_a_device_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/device-tokens', [
                'token' => 'ExponentPushToken[test-token-123]',
                'platform' => 'android',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $user->id,
            'token' => 'ExponentPushToken[test-token-123]',
            'platform' => 'android',
        ]);
    }

    public function test_duplicate_token_assigns_to_new_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $token = 'ExponentPushToken[shared-token]';

        $this->actingAs($userA, 'sanctum')->postJson('/api/v1/device-tokens', ['token' => $token])->assertOk();
        $this->actingAs($userB, 'sanctum')->postJson('/api/v1/device-tokens', ['token' => $token])->assertOk();

        $this->assertSame(1, DeviceToken::where('token', $token)->count());
        $this->assertSame($userB->id, DeviceToken::where('token', $token)->first()->user_id);
    }

    public function test_unregisters_a_device_token(): void
    {
        $user = User::factory()->create();
        $token = 'ExponentPushToken[remove-me]';

        DeviceToken::create(['user_id' => $user->id, 'token' => $token]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/device-tokens', ['token' => $token])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('device_tokens', ['token' => $token]);
    }
}
