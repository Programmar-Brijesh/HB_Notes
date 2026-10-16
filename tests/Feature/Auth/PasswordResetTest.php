<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    public function test_legacy_forgot_password_routes_are_disabled(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(404);
    }

    public function test_legacy_reset_password_routes_are_disabled(): void
    {
        $response = $this->get('/reset-password/sample-token');

        $response->assertStatus(404);
    }
}
