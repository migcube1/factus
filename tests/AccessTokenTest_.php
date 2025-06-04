<?php

namespace Tests\Unit;

use App\Models\User;
use App\Traits\Token;
use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessTokenTest extends TestCase
{
    use RefreshDatabase;
    use Token;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * A basic unit test example.
     */
    public function test_can_get_an_access_token(): void
    {
        // Arrange

        // Act
        $response = $this->getAccessToken($this->user);

        $response->assertJsonStructure(['token']);
    }
}
