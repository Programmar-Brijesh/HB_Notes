<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_non_admin_users_receive_access_denied(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $response = $this->actingAs($student)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_deactivated_admin_cannot_access_portal(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Overview');
        $response->assertSee('Academic Curriculums (Classes 9–12)');
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/admin/administrators/' . $admin->id . '/toggle-status');

        $response->assertSessionHasErrors('error');
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_last_active_admin_cannot_be_deactivated(): void
    {
        $admin1 = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Attempting to deactivate when there is only 1 active admin
        $response = $this->actingAs($admin1)->post('/admin/administrators/' . $admin1->id . '/toggle-status');

        $response->assertSessionHasErrors('error');
        $this->assertTrue($admin1->fresh()->is_active);
    }

    public function test_admin_can_deactivate_and_reactivate_another_admin(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $subAdmin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Deactivate subAdmin
        $response = $this->actingAs($superAdmin)->post('/admin/administrators/' . $subAdmin->id . '/toggle-status');
        $response->assertSessionHas('status');
        $this->assertFalse($subAdmin->fresh()->is_active);

        // Reactivate subAdmin
        $response = $this->actingAs($superAdmin)->post('/admin/administrators/' . $subAdmin->id . '/toggle-status');
        $response->assertSessionHas('status');
        $this->assertTrue($subAdmin->fresh()->is_active);
    }
}
