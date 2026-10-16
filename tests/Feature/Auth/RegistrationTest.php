<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_public_registration_route_does_not_exist(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_public_registration_submission_is_not_allowed(): void
    {
        $response = $this->post('/register', [
            'name' => 'Aarav Sharma',
            'email' => 'aarav@example.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ]);

        $response->assertStatus(404);
    }

    public function test_login_page_does_not_contain_registration_links(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('register');
        $response->assertDontSee('Create an account');
        $response->assertDontSee('Sign up');
    }

    public function test_login_page_does_not_contain_password_fields(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('type="password"', false);
    }
}
