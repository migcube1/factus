<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\Api\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Support\Str;

class TokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_access_token()
    {
        // Arrange
        $service = new AuthService();

        $user = User::factory()->create();

        $test_access_token = Str::random(40);

        $test_refresh_token = Str::random(40);

        // Act
        Http::fake([
            config('api.url') . '/oauth/token' => Http::response([
                'access_token' =>  $test_access_token,
                'refresh_token' => $test_refresh_token,
                'expires_in' => now()->addSecond(3600),
            ], 200),
        ]);

        $data = $service->getAccessToken($user);

        // Assert
        $this->assertEquals($test_access_token, $data['access_token']);

        $this->assertDatabaseHas('access_tokens', [
            'user_id' => $user->id,
            'access_token' =>  $test_access_token,
        ]);
    }
}
