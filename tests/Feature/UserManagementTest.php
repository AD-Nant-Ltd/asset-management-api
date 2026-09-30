<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function createRole(string $name): Role
    {
        return Role::firstOrCreate([
            'role_name' => $name,
        ]);
    }

    private function createStaff(
        string $forename = 'Test',
        string $surname = 'User',
        ?int $regul8StaffId = null
    ): Staff {
        return Staff::create([
            'forename' => $forename,
            'surname' => $surname,
            'regul8_staff_id' => $regul8StaffId,
            'active' => true,
        ]);
    }

    private function createApplicationUser(
        string $roleName = 'Administrator',
        string $email = 'test@example.com',
        bool $active = true
    ): User {
        $role = $this->createRole($roleName);

        $staff = $this->createStaff();

        return User::create([
            'staff_id' => $staff->id,
            'role_id' => $role->id,
            'email' => $email,
            'password' => 'Password123!',
            'active' => $active,
        ]);
    }

    public function test_administrator_can_view_application_users(): void
    {
        $administrator = $this->createApplicationUser();

        $this->actingAs($administrator);

        $response = $this->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('Application Users');
        $response->assertSee($administrator->email);
        $response->assertSee('Administrator');
    }

    public function test_administrator_can_grant_application_access_to_existing_staff_member(): void
    {
        $administrator = $this->createApplicationUser();

        $authorisedRole = $this->createRole('Authorised User');

        $staff = $this->createStaff(
            'Jane',
            'Doe',
            2001
        );

        $initialStaffCount = Staff::count();

        $this->actingAs($administrator);

        $response = $this->post(route('users.store'), [
            'staff_id' => $staff->id,
            'role_id' => $authorisedRole->id,
            'email' => 'jane@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'staff_id' => $staff->id,
            'role_id' => $authorisedRole->id,
            'email' => 'jane@example.com',
            'active' => true,
        ]);

        $this->assertSame($initialStaffCount, Staff::count());
    }

    public function test_staff_member_with_existing_account_cannot_receive_second_account(): void
    {
        $administrator = $this->createApplicationUser();

        $authorisedRole = $this->createRole('Authorised User');

        $staff = $this->createStaff(
            'Jane',
            'Doe',
            2001
        );

        User::create([
            'staff_id' => $staff->id,
            'role_id' => $authorisedRole->id,
            'email' => 'jane@example.com',
            'password' => 'Password123!',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->post(route('users.store'), [
            'staff_id' => $staff->id,
            'role_id' => $authorisedRole->id,
            'email' => 'jane.second@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('staff_id');

        $this->assertDatabaseMissing('users', [
            'email' => 'jane.second@example.com',
        ]);
    }

    public function test_create_form_only_lists_staff_without_application_accounts(): void
    {
        $administrator = $this->createApplicationUser();

        $authorisedRole = $this->createRole('Authorised User');

        $availableStaff = $this->createStaff(
            'Available',
            'Person',
            2001
        );

        $existingUserStaff = $this->createStaff(
            'Existing',
            'Account',
            2002
        );

        User::create([
            'staff_id' => $existingUserStaff->id,
            'role_id' => $authorisedRole->id,
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->get(route('users.create'));

        $response->assertOk();
        $response->assertSee('Available');
        $response->assertSee('Person');
        $response->assertDontSee('Existing Account');
    }

    public function test_administrator_can_update_application_user_details(): void
    {
        $administrator = $this->createApplicationUser();

        $authorisedRole = $this->createRole('Authorised User');

        $staff = $this->createStaff(
            'Jane',
            'Doe',
            2001
        );

        $user = User::create([
            'staff_id' => $staff->id,
            'role_id' => $authorisedRole->id,
            'email' => 'old@example.com',
            'password' => 'Password123!',
            'active' => true,
        ]);

        $administratorRole = $this->createRole('Administrator');

        $this->actingAs($administrator);

        $response = $this->put(route('users.update', $user), [
            'role_id' => $administratorRole->id,
            'email' => 'new@example.com',
            'active' => '1',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com',
            'role_id' => $administratorRole->id,
            'active' => true,
        ]);
    }

    public function test_administrator_can_revoke_access_without_deleting_user_or_staff_record(): void
    {
        $administrator = $this->createApplicationUser();

        $authorisedRole = $this->createRole('Authorised User');

        $staff = $this->createStaff(
            'Jane',
            'Doe',
            2001
        );

        $user = User::create([
            'staff_id' => $staff->id,
            'role_id' => $authorisedRole->id,
            'email' => 'jane@example.com',
            'password' => 'Password123!',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->put(route('users.update', $user), [
            'role_id' => $authorisedRole->id,
            'email' => 'jane@example.com',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'staff_id' => $staff->id,
            'active' => false,
        ]);

        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'forename' => 'Jane',
            'surname' => 'Doe',
        ]);
    }

    public function test_deactivated_authenticated_user_loses_access_on_next_request(): void
    {
        $user = $this->createApplicationUser(
            'Authorised User',
            'user@example.com'
        );

        $this->actingAs($user);

        $user->update([
            'active' => false,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_administrator_can_reset_application_user_password(): void
    {
        $administrator = $this->createApplicationUser();

        $authorisedRole = $this->createRole('Authorised User');

        $staff = $this->createStaff(
            'Jane',
            'Doe',
            2001
        );

        $user = User::create([
            'staff_id' => $staff->id,
            'role_id' => $authorisedRole->id,
            'email' => 'jane@example.com',
            'password' => 'OldPassword123!',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->put(route('users.update', $user), [
            'role_id' => $authorisedRole->id,
            'email' => 'jane@example.com',
            'active' => '1',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('users.index'));

        $user->refresh();

        $this->assertTrue(
            Hash::check('NewPassword123!', $user->password)
        );

        $this->assertFalse(
            Hash::check('OldPassword123!', $user->password)
        );
    }

    public function test_blank_password_fields_preserve_existing_password(): void
    {
        $administrator = $this->createApplicationUser();

        $authorisedRole = $this->createRole('Authorised User');

        $staff = $this->createStaff(
            'Jane',
            'Doe',
            2001
        );

        $user = User::create([
            'staff_id' => $staff->id,
            'role_id' => $authorisedRole->id,
            'email' => 'jane@example.com',
            'password' => 'ExistingPassword123!',
            'active' => true,
        ]);

        $originalPasswordHash = $user->password;

        $this->actingAs($administrator);

        $response = $this->put(route('users.update', $user), [
            'role_id' => $authorisedRole->id,
            'email' => 'jane.updated@example.com',
            'active' => '1',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('users.index'));

        $user->refresh();

        $this->assertSame(
            $originalPasswordHash,
            $user->password
        );

        $this->assertTrue(
            Hash::check('ExistingPassword123!', $user->password)
        );
    }

    public function test_authorised_user_cannot_access_user_administration(): void
    {
        $user = $this->createApplicationUser(
            'Authorised User',
            'authorised@example.com'
        );

        $this->actingAs($user);

        $this->get(route('users.index'))
            ->assertForbidden();

        $this->get(route('users.create'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_user_administration(): void
    {
        $this->get(route('users.index'))
            ->assertRedirect(route('login'));

        $this->get(route('users.create'))
            ->assertRedirect(route('login'));
    }
}