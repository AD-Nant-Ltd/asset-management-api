<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function createUser(bool $active = true): User
    {
        $role = Role::create([
            'role_name' => 'Administrator',
        ]);

        $staff = Staff::create([
            'forename' => 'Test',
            'surname' => 'User',
            'regul8_staff_id' => null,
            'active' => true,
        ]);

        return User::create([
            'staff_id' => $staff->id,
            'role_id' => $role->id,
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'active' => $active,
        ]);
    }

    public function test_active_user_can_authenticate_and_access_dashboard(): void
    {
        $user = $this->createUser();

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->get('/dashboard')
            ->assertOk();
    }

    public function test_guest_cannot_access_protected_dashboard(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $this->createUser(false);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $this->createUser();

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout_and_loses_protected_access(): void
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));

        $this->assertGuest();

        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }
}