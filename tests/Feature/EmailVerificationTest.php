<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_see_verification_notice()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirectToRoute('verification.notice');
    }

    public function test_user_sees_verification_page_when_not_verified()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.verify-email');
    }

    public function test_user_can_verify_email_with_valid_link()
    {
        Event::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect('/');
    }

    public function test_user_cannot_verify_with_invalid_hash()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_can_resend_verification_email()
    {
        Event::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('verification.send'));

        $response->assertSessionHas('status', 'Verification link sent!');
    }

    public function test_verified_user_can_create_chirps()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/chirps', [
            'message' => 'This is my first chirp!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chirps', [
            'user_id' => $user->id,
            'message' => 'This is my first chirp!',
        ]);
    }

    public function test_unverified_user_cannot_create_chirps()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->post('/chirps', [
            'message' => 'This is my first chirp!',
        ]);

        $response->assertRedirectToRoute('verification.notice');
    }

    public function test_already_verified_user_sees_verification_page()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('verification.notice'));

        // Already verified users should be redirected away
        $response->assertRedirect('/');
    }
}
