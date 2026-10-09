<?php

namespace Tests\Feature;

use App\Models\IncidentStatus;
use App\Models\IncidentType;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function createUser(
        string $roleName,
        string $email
    ): User {
        $role = Role::firstOrCreate([
            'role_name' => $roleName,
        ]);

        $staff = Staff::create([
            'forename' => 'Test',
            'surname' => $roleName === 'Administrator'
                ? 'Administrator'
                : 'User',
            'regul8_staff_id' => null,
            'active' => true,
        ]);

        return User::create([
            'staff_id' => $staff->id,
            'role_id' => $role->id,
            'email' => $email,
            'password' => 'Password123!',
            'active' => true,
        ]);
    }

    public function test_administrator_can_view_incident_configuration(): void
    {
        $administrator = $this->createUser(
            'Administrator',
            'admin@example.com'
        );

        IncidentType::create([
            'incident_type' => 'Damage',
            'active' => true,
        ]);

        IncidentStatus::create([
            'incident_status' => 'Open',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->get(
            route('incident-configuration.index')
        );

        $response->assertOk();

        $response->assertSee('Incident Configuration');
        $response->assertSee('Incident Types');
        $response->assertSee('Incident Statuses');
        $response->assertSee('Damage');
        $response->assertSee('Open');
    }

    public function test_administrator_can_create_incident_type(): void
    {
        $administrator = $this->createUser(
            'Administrator',
            'admin@example.com'
        );

        $this->actingAs($administrator);

        $response = $this->post(
            route('incident-configuration.types.store'),
            [
                'incident_type' => 'Loss',
            ]
        );

        $response->assertRedirect(
            route('incident-configuration.index')
            . '#incident-types'
        );

        $this->assertDatabaseHas(
            'incident_types',
            [
                'incident_type' => 'Loss',
                'active' => true,
            ]
        );
    }

    public function test_administrator_can_update_and_deactivate_incident_type(): void
    {
        $administrator = $this->createUser(
            'Administrator',
            'admin@example.com'
        );

        $incidentType = IncidentType::create([
            'incident_type' => 'Damage',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->put(
            route(
                'incident-configuration.types.update',
                $incidentType
            ),
            [
                'incident_type' => 'Physical Damage',
            ]
        );

        $response->assertRedirect(
            route('incident-configuration.index')
            . '#incident-types'
        );

        $this->assertDatabaseHas(
            'incident_types',
            [
                'id' => $incidentType->id,
                'incident_type' => 'Physical Damage',
                'active' => false,
            ]
        );
    }

    public function test_administrator_can_create_incident_status(): void
    {
        $administrator = $this->createUser(
            'Administrator',
            'admin@example.com'
        );

        $this->actingAs($administrator);

        $response = $this->post(
            route('incident-configuration.statuses.store'),
            [
                'incident_status' => 'Under Review',
            ]
        );

        $response->assertRedirect(
            route('incident-configuration.index')
            . '#incident-statuses'
        );

        $this->assertDatabaseHas(
            'incident_statuses',
            [
                'incident_status' => 'Under Review',
                'active' => true,
            ]
        );
    }

    public function test_administrator_can_update_and_deactivate_custom_incident_status(): void
    {
        $administrator = $this->createUser(
            'Administrator',
            'admin@example.com'
        );

        $incidentStatus = IncidentStatus::create([
            'incident_status' => 'Under Review',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->put(
            route(
                'incident-configuration.statuses.update',
                $incidentStatus
            ),
            [
                'incident_status' => 'Awaiting Review',
            ]
        );

        $response->assertRedirect(
            route('incident-configuration.index')
            . '#incident-statuses'
        );

        $this->assertDatabaseHas(
            'incident_statuses',
            [
                'id' => $incidentStatus->id,
                'incident_status' => 'Awaiting Review',
                'active' => false,
            ]
        );
    }

    public function test_duplicate_incident_type_and_status_values_are_rejected(): void
    {
        $administrator = $this->createUser(
            'Administrator',
            'admin@example.com'
        );

        IncidentType::create([
            'incident_type' => 'Damage',
            'active' => true,
        ]);

        IncidentStatus::create([
            'incident_status' => 'Under Review',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $typeResponse = $this->post(
            route('incident-configuration.types.store'),
            [
                'incident_type' => 'Damage',
            ]
        );

        $typeResponse->assertSessionHasErrors(
            'incident_type'
        );

        $statusResponse = $this->post(
            route('incident-configuration.statuses.store'),
            [
                'incident_status' => 'Under Review',
            ]
        );

        $statusResponse->assertSessionHasErrors(
            'incident_status'
        );

        $this->assertDatabaseCount(
            'incident_types',
            1
        );

        $this->assertDatabaseCount(
            'incident_statuses',
            1
        );
    }

    public function test_open_and_resolved_system_statuses_cannot_be_changed(): void
    {
        $administrator = $this->createUser(
            'Administrator',
            'admin@example.com'
        );

        $openStatus = IncidentStatus::create([
            'incident_status' => 'Open',
            'active' => true,
        ]);

        $resolvedStatus = IncidentStatus::create([
            'incident_status' => 'Resolved',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $renameOpenResponse = $this->put(
            route(
                'incident-configuration.statuses.update',
                $openStatus
            ),
            [
                'incident_status' => 'New',
                'active' => 1,
            ]
        );

        $renameOpenResponse->assertSessionHasErrors(
            'incident_status'
        );

        $deactivateOpenResponse = $this->put(
            route(
                'incident-configuration.statuses.update',
                $openStatus
            ),
            [
                'incident_status' => 'Open',
            ]
        );

        $deactivateOpenResponse->assertSessionHasErrors(
            'incident_status'
        );

        $renameResolvedResponse = $this->put(
            route(
                'incident-configuration.statuses.update',
                $resolvedStatus
            ),
            [
                'incident_status' => 'Closed',
                'active' => 1,
            ]
        );

        $renameResolvedResponse->assertSessionHasErrors(
            'incident_status'
        );

        $deactivateResolvedResponse = $this->put(
            route(
                'incident-configuration.statuses.update',
                $resolvedStatus
            ),
            [
                'incident_status' => 'Resolved',
            ]
        );

        $deactivateResolvedResponse->assertSessionHasErrors(
            'incident_status'
        );

        $this->assertDatabaseHas(
            'incident_statuses',
            [
                'id' => $openStatus->id,
                'incident_status' => 'Open',
                'active' => true,
            ]
        );

        $this->assertDatabaseHas(
            'incident_statuses',
            [
                'id' => $resolvedStatus->id,
                'incident_status' => 'Resolved',
                'active' => true,
            ]
        );
    }

    public function test_authorised_user_cannot_access_incident_configuration(): void
    {
        $user = $this->createUser(
            'Authorised User',
            'user@example.com'
        );

        $incidentType = IncidentType::create([
            'incident_type' => 'Damage',
            'active' => true,
        ]);

        $this->actingAs($user);

        $viewResponse = $this->get(
            route('incident-configuration.index')
        );

        $viewResponse->assertForbidden();

        $createResponse = $this->post(
            route('incident-configuration.types.store'),
            [
                'incident_type' => 'Loss',
            ]
        );

        $createResponse->assertForbidden();

        $updateResponse = $this->put(
            route(
                'incident-configuration.types.update',
                $incidentType
            ),
            [
                'incident_type' => 'Changed',
                'active' => 1,
            ]
        );

        $updateResponse->assertForbidden();

        $this->assertDatabaseMissing(
            'incident_types',
            [
                'incident_type' => 'Loss',
            ]
        );

        $this->assertDatabaseHas(
            'incident_types',
            [
                'id' => $incidentType->id,
                'incident_type' => 'Damage',
            ]
        );
    }
}