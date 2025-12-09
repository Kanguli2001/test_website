<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token_with_expiry()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'user',
            'access_token',
            'token_type',
            'expires_in',
            'expires_at',
        ]);

        $this->assertNotNull($response->json('access_token'));
        $this->assertEquals('Bearer', $response->json('token_type'));
        $this->assertEquals(3600, $response->json('expires_in')); // 60 minutes in seconds
    }

    public function test_user_can_login_and_receive_token_with_expiry()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'user',
            'access_token',
            'token_type',
            'expires_in',
            'expires_at',
        ]);

        $this->assertNotNull($response->json('access_token'));
    }

    public function test_invalid_credentials_return_unauthorized()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_user_can_access_protected_route_with_valid_token()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/auth/me');

        $response->assertStatus(200);
        $response->assertJsonPath('user.id', $user->id);
    }

    public function test_user_cannot_access_protected_route_without_token()
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_user_can_logout_and_revoke_tokens()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        // Verify token works before logout
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/auth/me');

        $response->assertStatus(200);

        // Logout
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Logged out successfully']);

        // Verify all tokens were deleted
        $this->assertEquals(0, $user->fresh()->tokens()->count());
    }

    public function test_user_can_refresh_token()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $oldToken = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $oldToken",
        ])->postJson('/api/auth/refresh');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
            'expires_in',
            'expires_at',
        ]);

        $newToken = $response->json('access_token');
        $this->assertNotEquals($oldToken, $newToken);

        // Verify only one token exists (old one was revoked)
        $this->assertEquals(1, $user->fresh()->tokens()->count());

        // New token should work
        $response = $this->withHeaders([
            'Authorization' => "Bearer $newToken",
        ])->getJson('/api/auth/me');

        $response->assertStatus(200);
    }

    public function test_unverified_user_cannot_access_chirps_api()
    {
        $user = User::factory()->create([
            'email_verified_at' => null, // Not verified
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/chirps');

        $response->assertStatus(403); // Forbidden
    }
}
