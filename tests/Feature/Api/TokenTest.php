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

        Http::fake([
            config('api.url') . '/oauth/token' => Http::response([
                'access_token' =>  $test_access_token,
                'refresh_token' => $test_refresh_token,
                'expires_in' => now()->addSecond(3600),
            ], 200),
        ]);

        // Act
        $data = $service->getAccessToken($user);

        // Assert
        $this->assertEquals($test_access_token, $data['access_token']);

        $this->assertDatabaseHas('access_tokens', [
            'user_id' => $user->id,
            'access_token' =>  $test_access_token,
        ]);
    }

    public function test_can_resolve_authorization_if_token_not_expired()
    {
        // Arrange
        $service = new AuthService();

        $user = User::factory()->create();

        $valid_token = Str::random(40);

        $user->accessToken()->create([
            'service_id' => Str::uuid(),
            'access_token' =>  $valid_token,
            'refresh_token' => Str::random(40),
            'expires_at' => now()->addSecond(3600),
        ]);

        // Act
        $token = $service->resolveAuthorization($user);

        // Assert
        $this->assertEquals($valid_token, $token);
    }


    public function test_can_resolve_authorization_if_token_expired()
    {
        // Arrange
        $service = new AuthService();

        $user = User::factory()->create();

        $expired_access_token = Str::random(40);

        $user->accessToken()->create([
            'service_id' => Str::uuid(),
            'access_token' =>  $expired_access_token,
            'refresh_token' =>  Str::random(40),
            'expires_at' => now()->subMinutes(5),
        ]);

        $new_access_token = Str::random(40);

        Http::fake([
            config('api.url') . '/oauth/token' => Http::response([
                'access_token' =>  $new_access_token,
                'refresh_token' => Str::random(40),
                'expires_in' => 3600,
            ], 200),
        ]);

        // Act
        $new_token = $service->resolveAuthorization($user);

        // Assert
        $this->assertEquals($new_access_token, $new_token);

        $this->assertDatabaseHas('access_tokens', [
            'user_id' => $user->id,
            'access_token' => $new_access_token,
        ]);
    }


    public function test_can_resolve_authorization_if_token_cannot_refresh()
    {
        // Arrange
        $service = new AuthService();

        $user = User::factory()->create();

        $access_token = Str::random(40);

        $user->accessToken()->create([
            'service_id' => Str::uuid(),
            'access_token' =>  $access_token,
            'refresh_token' =>  Str::random(40),
            'expires_at' => now()->subMinutes(5),
        ]);

        Http::fake([
            config('api.url') . '/oauth/token' => Http::response([
                "error" => "invalid_request",
                "error_description" => "The refresh token is invalid.",
                "hint" => "Token has been revoked",
                "message" => "The refresh token is invalid."
            ], 401),
        ]);

        // Act
        $response = $service->resolveAuthorization($user);

        // Assert
        $this->assertEquals(401, $response->getStatusCode());

        $this->assertDatabaseMissing('access_tokens', [
            'user_id' => $user->id,
            'access_token' =>  $access_token,
        ]);
    }
}
