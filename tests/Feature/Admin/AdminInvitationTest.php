<?php

namespace Tests\Feature\Admin;

use App\Models\AdminInvitation;
use App\Models\User;
use App\Notifications\AdminInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_invite_administrators(): void
    {
        $response = $this->post('/admin/administrators/invite', [
            'name' => 'New Colleague',
            'email' => 'newadmin@hbnotes.com',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_admin_can_invite_new_administrator(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/admin/administrators/invite', [
            'name' => 'Priyanshu Sharma',
            'email' => 'priyanshu@hbnotes.com',
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('admin_invitations', [
            'email' => 'priyanshu@hbnotes.com',
            'name' => 'Priyanshu Sharma',
            'invited_by' => $admin->id,
            'accepted_at' => null,
        ]);

        Notification::assertSentOnDemand(AdminInvitationNotification::class);
    }

    public function test_invitation_screen_renders_without_authenticating(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $plainToken = Str::random(64);
        AdminInvitation::create([
            'name' => 'Invited Admin',
            'email' => 'invitee@hbnotes.com',
            'token_hash' => hash('sha256', $plainToken),
            'invited_by' => $admin->id,
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->get('/admin/invitations/' . $plainToken);

        $response->assertStatus(200);
        $response->assertSee('Join the Admin Team');
        $response->assertSee('invitee@hbnotes.com');
        $this->assertGuest();
    }

    public function test_valid_invitation_can_be_accepted(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $plainToken = Str::random(64);
        $invitation = AdminInvitation::create([
            'name' => 'Invited Admin',
            'email' => 'invitee@hbnotes.com',
            'token_hash' => hash('sha256', $plainToken),
            'invited_by' => $admin->id,
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->post('/admin/invitations/' . $plainToken);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $newAdmin = User::where('email', 'invitee@hbnotes.com')->first();
        $this->assertNotNull($newAdmin);
        $this->assertTrue($newAdmin->isAdmin());
        $this->assertTrue($newAdmin->isActive());

        $this->assertNotNull($invitation->fresh()->accepted_at);
    }

    public function test_expired_invitation_cannot_be_accepted(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $plainToken = Str::random(64);
        AdminInvitation::create([
            'name' => 'Invited Admin',
            'email' => 'invitee@hbnotes.com',
            'token_hash' => hash('sha256', $plainToken),
            'invited_by' => $admin->id,
            'expires_at' => now()->subHour(), // Expired
        ]);

        $response = $this->post('/admin/invitations/' . $plainToken);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_accepted_invitation_cannot_be_reused(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $plainToken = Str::random(64);
        AdminInvitation::create([
            'name' => 'Invited Admin',
            'email' => 'invitee@hbnotes.com',
            'token_hash' => hash('sha256', $plainToken),
            'invited_by' => $admin->id,
            'expires_at' => now()->addHours(48),
            'accepted_at' => now()->subMinutes(10), // Already accepted
        ]);

        $response = $this->post('/admin/invitations/' . $plainToken);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_admin_can_revoke_pending_invitation(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $plainToken = Str::random(64);
        $invitation = AdminInvitation::create([
            'name' => 'To Revoke',
            'email' => 'revoke@hbnotes.com',
            'token_hash' => hash('sha256', $plainToken),
            'invited_by' => $admin->id,
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->actingAs($admin)->delete('/admin/administrators/invitations/' . $invitation->id);

        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('admin_invitations', ['id' => $invitation->id]);
    }
}
