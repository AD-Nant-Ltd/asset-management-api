<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Blade views use Vite assets, but these feature tests are testing
        // server-side application behaviour rather than frontend compilation.
        $this->withoutVite();
    }

    private function createAuthenticatedUser(): User
    {
        $role = Role::create([
            'role_name' => 'Authorised User',
        ]);

        $staff = Staff::create([
            'forename' => 'Adam',
            'surname' => 'Dolphin',
            'regul8_staff_id' => 1001,
            'active' => true,
        ]);

        $user = User::create([
            'staff_id' => $staff->id,
            'role_id' => $role->id,
            'email' => 'adam@example.com',
            'password' => 'Password123!',
            'active' => true,
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_authenticated_user_can_view_staff_list(): void
    {
        $this->createAuthenticatedUser();

        Staff::create([
            'forename' => 'Jane',
            'surname' => 'Doe',
            'regul8_staff_id' => 1002,
            'active' => true,
        ]);

        $response = $this->get(route('staff.index'));

        $response = $this->get(route('staff.index'));

        $response->assertOk();
        $response->assertSee('Staff');
        $response->assertSee('Jane');
        $response->assertSee('Doe');
        $response->assertSee('1002');
    }

    public function test_authenticated_user_can_create_staff_without_creating_application_user(): void
    {
        $this->createAuthenticatedUser();

        $initialUserCount = User::count();

        $response = $this->post(route('staff.store'), [
            'forename' => 'Jane',
            'surname' => 'Doe',
            'regul8_staff_id' => 1002,
            'active' => '1',
        ]);

        $response->assertRedirect(route('staff.index'));

        $this->assertDatabaseHas('staff', [
            'forename' => 'Jane',
            'surname' => 'Doe',
            'regul8_staff_id' => 1002,
            'active' => true,
        ]);

        $this->assertSame($initialUserCount, User::count());
    }

    public function test_authenticated_user_can_view_existing_staff_record(): void
    {
        $this->createAuthenticatedUser();

        $staff = Staff::create([
            'forename' => 'Jane',
            'surname' => 'Doe',
            'regul8_staff_id' => 1002,
            'active' => true,
        ]);

        $response = $this->get(route('staff.show', $staff));

        $response->assertOk();
        $response->assertSee('Jane Doe');
        $response->assertSee('1002');
        $response->assertSee('Active');
    }

    public function test_authenticated_user_can_update_existing_staff_record(): void
    {
        $this->createAuthenticatedUser();

        $staff = Staff::create([
            'forename' => 'John',
            'surname' => 'Smith',
            'regul8_staff_id' => 1002,
            'active' => true,
        ]);

        $response = $this->put(route('staff.update', $staff), [
            'forename' => 'John',
            'surname' => 'Smith-Jones',
            'regul8_staff_id' => 1002,
            'active' => '1',
        ]);

        $response->assertRedirect(route('staff.show', $staff));

        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'forename' => 'John',
            'surname' => 'Smith-Jones',
            'regul8_staff_id' => 1002,
            'active' => true,
        ]);
    }

    public function test_duplicate_regul8_staff_id_is_rejected_when_creating_staff(): void
    {
        $this->createAuthenticatedUser();

        Staff::create([
            'forename' => 'Jane',
            'surname' => 'Doe',
            'regul8_staff_id' => 2001,
            'active' => true,
        ]);

        $response = $this->post(route('staff.store'), [
            'forename' => 'John',
            'surname' => 'Smith',
            'regul8_staff_id' => 2001,
            'active' => '1',
        ]);

        $response->assertSessionHasErrors('regul8_staff_id');

        $this->assertDatabaseMissing('staff', [
            'forename' => 'John',
            'surname' => 'Smith',
            'regul8_staff_id' => 2001,
        ]);
    }

    public function test_existing_staff_member_can_keep_their_regul8_staff_id_when_updated(): void
    {
        $this->createAuthenticatedUser();

        $staff = Staff::create([
            'forename' => 'Jane',
            'surname' => 'Doe',
            'regul8_staff_id' => 2001,
            'active' => true,
        ]);

        $response = $this->put(route('staff.update', $staff), [
            'forename' => 'Jane',
            'surname' => 'Doe-Smith',
            'regul8_staff_id' => 2001,
            'active' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('staff.show', $staff));

        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'surname' => 'Doe-Smith',
            'regul8_staff_id' => 2001,
        ]);
    }

    public function test_guest_cannot_access_staff_management(): void
    {
        $staff = Staff::create([
            'forename' => 'Jane',
            'surname' => 'Doe',
            'regul8_staff_id' => 1002,
            'active' => true,
        ]);

        $this->get(route('staff.index'))
            ->assertRedirect(route('login'));

        $this->get(route('staff.create'))
            ->assertRedirect(route('login'));

        $this->get(route('staff.show', $staff))
            ->assertRedirect(route('login'));

        $this->get(route('staff.edit', $staff))
            ->assertRedirect(route('login'));

        $this->post(route('staff.store'), [
            'forename' => 'Unauthorised',
            'surname' => 'Person',
            'regul8_staff_id' => 9999,
            'active' => '1',
        ])->assertRedirect(route('login'));

        $this->put(route('staff.update', $staff), [
            'forename' => 'Changed',
            'surname' => 'Name',
            'regul8_staff_id' => 1002,
            'active' => '1',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('staff', [
            'forename' => 'Unauthorised',
            'surname' => 'Person',
        ]);

        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'forename' => 'Jane',
            'surname' => 'Doe',
        ]);
    }
}