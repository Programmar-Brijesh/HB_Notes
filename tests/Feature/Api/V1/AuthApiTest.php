<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_student_can_register_with_valid_data(): void
    {
        Notification::fake();

        $payload = [
            'name' => 'Brijesh Student',
            'email' => 'student@hbnotes.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        $response = $this->postJson('/api/v1/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.name', 'Brijesh Student')
            ->assertJsonPath('data.user.email', 'student@hbnotes.test')
            ->assertJsonPath('data.user.role', 'student')
            ->assertJsonPath('data.user.email_verified', false)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'role', 'is_active', 'email_verified'],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'student@hbnotes.test',
            'role' => 'student',
            'is_active' => true,
        ]);

        $user = User::where('email', 'student@hbnotes.test')->firstOrFail();
        $this->assertTrue(Hash::check('SecurePass123!', $user->password));

        // Verification email should be sent
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'existing@hbnotes.test',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Another Student',
            'email' => 'existing@hbnotes.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_requires_password_confirmation_and_min_length(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test Student',
            'email' => 'student2@hbnotes.test',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_student_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@hbnotes.test',
            'password' => Hash::make('CorrectPassword123!'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'login@hbnotes.test',
            'password' => 'CorrectPassword123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'login@hbnotes.test')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user',
                ],
            ]);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'login@hbnotes.test',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'login@hbnotes.test',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid email or password.');
    }

    public function test_login_fails_for_inactive_account(): void
    {
        User::factory()->create([
            'email' => 'inactive@hbnotes.test',
            'password' => Hash::make('CorrectPassword123!'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'inactive@hbnotes.test',
            'password' => 'CorrectPassword123!',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Your account is deactivated. Please contact support.');
    }

    public function test_forgot_password_sends_notification_and_returns_generic_response(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'forgot@hbnotes.test',
        ]);

        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'forgot@hbnotes.test',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'If an account exists for this email, password reset instructions will be sent.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_with_non_existent_email_returns_identical_generic_response(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'nonexistent@hbnotes.test',
        ]);

        // Security check: Never reveal whether an email exists
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'If an account exists for this email, password reset instructions will be sent.');

        Notification::assertNothingSent();
    }

    public function test_student_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'resetme@hbnotes.test',
            'password' => Hash::make('OldPassword123!'),
        ]);

        // Create an existing API token that should be revoked on password reset
        $token = $user->createToken('device_token')->plainTextToken;
        $this->assertCount(1, $user->tokens);

        $resetToken = Password::broker()->createToken($user);

        $response = $this->postJson('/api/v1/reset-password', [
            'token' => $resetToken,
            'email' => 'resetme@hbnotes.test',
            'password' => 'BrandNewPassword123!',
            'password_confirmation' => 'BrandNewPassword123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Your password has been reset successfully. Please log in with your new password.');

        $user->refresh();
        $this->assertTrue(Hash::check('BrandNewPassword123!', $user->password));

        // Security check: all previous tokens revoked
        $this->assertCount(0, $user->tokens);
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        User::factory()->create([
            'email' => 'resetme@hbnotes.test',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->postJson('/api/v1/reset-password', [
            'token' => 'invalid-fake-token',
            'email' => 'resetme@hbnotes.test',
            'password' => 'BrandNewPassword123!',
            'password_confirmation' => 'BrandNewPassword123!',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_password_reset_token_cannot_be_reused(): void
    {
        $user = User::factory()->create([
            'email' => 'reuse@hbnotes.test',
            'password' => Hash::make('OldPass123!'),
        ]);

        $resetToken = Password::broker()->createToken($user);

        // First reset succeeds
        $this->postJson('/api/v1/reset-password', [
            'token' => $resetToken,
            'email' => 'reuse@hbnotes.test',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ])->assertOk();

        // Second reset with the same token must fail
        $response = $this->postJson('/api/v1/reset-password', [
            'token' => $resetToken,
            'email' => 'reuse@hbnotes.test',
            'password' => 'AnotherPass123!',
            'password_confirmation' => 'AnotherPass123!',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_authenticated_student_can_fetch_profile_via_me_endpoint(): void
    {
        $user = User::factory()->create([
            'name' => 'Active Student',
            'email' => 'student_me@hbnotes.test',
            'role' => 'student',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'student_me@hbnotes.test')
            ->assertJsonPath('data.name', 'Active Student');
    }

    public function test_unauthenticated_request_to_me_endpoint_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    public function test_student_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'email' => 'changepass@hbnotes.test',
            'password' => Hash::make('CurrentPassword123!'),
        ]);

        $currentToken = $user->createToken('current_device')->plainTextToken;
        $otherToken = $user->createToken('other_device')->plainTextToken;
        $this->assertCount(2, $user->tokens);

        $response = $this->withHeader('Authorization', "Bearer {$currentToken}")
            ->putJson('/api/v1/password', [
                'current_password' => 'CurrentPassword123!',
                'password' => 'UpdatedPassword999!',
                'password_confirmation' => 'UpdatedPassword999!',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $user->refresh();
        $this->assertTrue(Hash::check('UpdatedPassword999!', $user->password));

        // Other device token revoked, current token preserved
        $this->assertCount(1, $user->tokens);
    }

    public function test_change_password_fails_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'email' => 'changepass_wrong@hbnotes.test',
            'password' => Hash::make('CurrentPassword123!'),
        ]);

        $token = $user->createToken('current_device')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/v1/password', [
                'current_password' => 'WrongCurrentPassword',
                'password' => 'UpdatedPassword999!',
                'password_confirmation' => 'UpdatedPassword999!',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_student_can_logout_revoking_current_token(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('device_1')->plainTextToken;
        $token2 = $user->createToken('device_2')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token1}")
            ->postJson('/api/v1/logout');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $user->refresh();
        // token1 deleted, token2 still active
        $this->assertCount(1, $user->tokens);
    }

    public function test_student_can_logout_all_revoking_all_tokens(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('device_1')->plainTextToken;
        $token2 = $user->createToken('device_2')->plainTextToken;
        $token3 = $user->createToken('device_3')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token1}")
            ->postJson('/api/v1/logout-all');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $user->refresh();
        $this->assertCount(0, $user->tokens);

        // Reset auth guard cache between requests in test
        app('auth')->forgetGuards();

        // Subsequent requests with token1 should fail with 401
        $this->withHeader('Authorization', "Bearer {$token1}")
            ->getJson('/api/v1/me')
            ->assertStatus(401);
    }

    public function test_email_verification_via_signed_url(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'verify_test@hbnotes.test',
        ]);
        $this->assertFalse($user->hasVerifiedEmail());

        $verificationUrl = URL::temporarySignedRoute(
            'api.v1.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->getJson($verificationUrl);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Email verified successfully.');

        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_email_verification_rejects_invalid_signature(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'fake_verify@hbnotes.test',
        ]);

        $tamperedUrl = "/api/v1/email/verify/{$user->id}/" . sha1($user->email) . "?signature=tampered";

        $response = $this->getJson($tamperedUrl);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_resend_email_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $token = $user->createToken('token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/email/verification-notification');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Verification link sent to your email.');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_existing_admin_authentication_remains_functional(): void
    {
        // Ensure web admin login via magic link continues to function flawlessly
        $admin = User::factory()->create([
            'email' => 'superadmin@hbnotes.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'superadmin@hbnotes.test',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHas('status');
    }
}
