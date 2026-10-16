<?php

namespace Tests\Feature\Auth;

use App\Models\MagicLoginToken;
use App\Models\User;
use App\Notifications\AdminMagicLoginNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Admin Sign In');
        $response->assertSee('Enter your authorized email address to receive a secure sign-in link.');
        $response->assertSee('Send Secure Login Link');
        $response->assertDontSee('type="password"', false);
    }

    public function test_unauthorized_email_cannot_receive_login_link_and_returns_generic_message(): void
    {
        Notification::fake();

        $response = $this->post('/login', [
            'email' => 'unknown@example.com',
        ]);

        $response->assertSessionHas('status', 'If your email is eligible, you will receive a login link shortly.');
        $this->assertDatabaseMissing('users', ['email' => 'unknown@example.com']);
        Notification::assertNothingSent();
    }

    public function test_login_request_never_creates_arbitrary_users(): void
    {
        $this->post('/login', [
            'email' => 'newuser@example.com',
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'newuser@example.com']);
    }

    public function test_student_account_cannot_receive_magic_login_link(): void
    {
        Notification::fake();

        $student = User::factory()->create([
            'email' => 'student@example.com',
            'role' => 'student',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'student@example.com',
        ]);

        $response->assertSessionHas('status', 'If your email is eligible, you will receive a login link shortly.');
        Notification::assertNothingSent();
        $this->assertDatabaseMissing('magic_login_tokens', ['user_id' => $student->id]);
    }

    public function test_inactive_admin_cannot_receive_magic_login_link_and_receives_generic_response(): void
    {
        Notification::fake();

        $inactiveAdmin = User::factory()->create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@hbnotes.com',
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@hbnotes.com',
        ]);

        $response->assertSessionHas('status', 'If your email is eligible, you will receive a login link shortly.');
        Notification::assertNothingSent();
        $this->assertDatabaseMissing('magic_login_tokens', ['user_id' => $inactiveAdmin->id]);
    }

    public function test_email_normalization_trims_whitespace_and_converts_to_lowercase(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'name' => 'Case Sensitive Admin',
            'email' => 'superadmin@hbnotes.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Pass uppercase email with leading and trailing whitespace
        $response = $this->post('/login', [
            'email' => '   SUPERADMIN@HBNotes.COM   ',
        ]);

        $response->assertSessionHas('status', 'If your email is eligible, you will receive a login link shortly.');
        Notification::assertSentTo($admin, AdminMagicLoginNotification::class);
        $this->assertDatabaseHas('magic_login_tokens', [
            'user_id' => $admin->id,
            'used_at' => null,
        ]);
    }

    public function test_authorized_admin_receives_magic_link_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'name' => 'Dr. Admin',
            'email' => 'admin@hbnotes.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@hbnotes.com',
        ]);

        $response->assertSessionHas('status', 'If your email is eligible, you will receive a login link shortly.');
        Notification::assertSentTo($admin, AdminMagicLoginNotification::class);
        $this->assertDatabaseHas('magic_login_tokens', [
            'user_id' => $admin->id,
            'used_at' => null,
        ]);
    }

    public function test_magic_link_get_request_displays_confirmation_and_does_not_authenticate(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $plainToken = Str::random(64);
        MagicLoginToken::create([
            'user_id' => $admin->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->get('/login/magic/' . $plainToken);

        $response->assertStatus(200);
        $response->assertSee('Confirm Sign In');
        $response->assertSee($admin->name);
        $this->assertGuest(); // Crucial: scanners must not authenticate the admin
    }

    public function test_valid_token_authenticates_via_post_and_regenerates_session(): void
    {
        $admin = User::factory()->create([
            'name' => 'Authorized Admin',
            'email' => 'authadmin@hbnotes.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $plainToken = Str::random(64);
        $record = MagicLoginToken::create([
            'user_id' => $admin->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->post('/login/magic/' . $plainToken);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));

        $this->assertNotNull($record->fresh()->used_at);
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_expired_token_is_rejected(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $plainToken = Str::random(64);
        MagicLoginToken::create([
            'user_id' => $admin->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->subMinute(), // Expired
        ]);

        $response = $this->post('/login/magic/' . $plainToken);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_invalid_or_tampered_token_is_rejected(): void
    {
        $response = $this->post('/login/magic/tampered-token-12345');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_used_token_cannot_be_reused(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $plainToken = Str::random(64);
        MagicLoginToken::create([
            'user_id' => $admin->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addMinutes(10),
            'used_at' => now()->subMinutes(2), // Already consumed
        ]);

        $response = $this->post('/login/magic/' . $plainToken);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_login_request_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'spam@example.com']);
        }

        $response = $this->post('/login', ['email' => 'spam@example.com']);

        // 6th attempt should be throttled
        $response->assertStatus(429);
    }

    public function test_session_lifetime_is_configured_to_120_minutes(): void
    {
        $this->assertEquals(120, config('session.lifetime'));
    }

    public function test_administrator_can_logout_via_post_invalidating_session(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
