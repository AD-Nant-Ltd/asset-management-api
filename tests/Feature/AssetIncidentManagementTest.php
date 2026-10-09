<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCondition;
use App\Models\AssetIncident;
use App\Models\AssetStatus;
use App\Models\AssetSubtype;
use App\Models\AssetType;
use App\Models\IncidentStatus;
use App\Models\IncidentType;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetIncidentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function createApplicationUser(
        string $forename = 'Test',
        string $surname = 'User',
        string $email = 'user@example.com',
        bool $active = true
    ): User {
        $role = Role::firstOrCreate([
            'role_name' => 'Authorised User',
        ]);

        $staff = Staff::create([
            'forename' => $forename,
            'surname' => $surname,
            'regul8_staff_id' => null,
            'active' => true,
        ]);

        return User::create([
            'staff_id' => $staff->id,
            'role_id' => $role->id,
            'email' => $email,
            'password' => 'Password123!',
            'active' => $active,
        ]);
    }

    private function createStaff(
        string $forename = 'Jane',
        string $surname = 'Doe'
    ): Staff {
        return Staff::create([
            'forename' => $forename,
            'surname' => $surname,
            'regul8_staff_id' => null,
            'active' => true,
        ]);
    }

    private function createAsset(
        string $assetTag = 'ASSET-001'
    ): Asset {
        $assetType = AssetType::firstOrCreate(
            [
                'asset_type' => 'IT Equipment',
            ],
            [
                'active' => true,
            ]
        );

        $assetSubtype = AssetSubtype::firstOrCreate(
            [
                'asset_type_id' => $assetType->id,
                'asset_subtype' => 'Laptop',
            ],
            [
                'active' => true,
            ]
        );

        $assetStatus = AssetStatus::firstOrCreate(
            [
                'asset_status' => 'Operational',
            ],
            [
                'active' => true,
            ]
        );

        $assetCondition = AssetCondition::firstOrCreate(
            [
                'asset_condition' => 'Good',
            ],
            [
                'active' => true,
            ]
        );

        return Asset::create([
            'asset_subtype_id' => $assetSubtype->id,
            'asset_status_id' => $assetStatus->id,
            'asset_condition_id' => $assetCondition->id,
            'asset_tag' => $assetTag,
            'serial_num' => 'SERIAL-' . $assetTag,
            'purchase_date' => '2026-09-01',
            'warranty_expiry' => '2029-09-01',
        ]);
    }

    private function createIncidentType(
        string $name = 'Damage'
    ): IncidentType {
        return IncidentType::create([
            'incident_type' => $name,
            'active' => true,
        ]);
    }

    private function createIncidentStatus(
        string $name
    ): IncidentStatus {
        return IncidentStatus::create([
            'incident_status' => $name,
            'active' => true,
        ]);
    }

    private function createIncident(
        Asset $asset,
        Staff $staff,
        IncidentType $incidentType,
        IncidentStatus $incidentStatus,
        ?User $assignedUser = null
    ): AssetIncident {
        return AssetIncident::create([
            'asset_id' => $asset->id,
            'affected_staff_id' => $staff->id,
            'assigned_to_user_id' => $assignedUser?->id,
            'incident_type_id' => $incidentType->id,
            'incident_status_id' => $incidentStatus->id,
            'incident_date' => '2026-10-09',
            'description' => 'Laptop casing damaged.',
            'action_taken' => null,
            'warranty_claim_ref' => null,
            'warranty_claim_date' => null,
            'warranty_excess' => null,
            'associated_cost' => null,
            'resolved_date' => null,
        ]);
    }

    public function test_authorised_user_can_view_manage_incident_form(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $staff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus('Open');

        $incident = $this->createIncident(
            $asset,
            $staff,
            $incidentType,
            $openStatus
        );

        $this->actingAs($user);

        $response = $this->get(
            route(
                'assets.incidents.edit',
                [$asset, $incident]
            )
        );

        $response->assertOk();

        $response->assertSee(
            'Manage Incident #' . $incident->id
        );

        $response->assertSee($asset->asset_tag);

        $response->assertSee(
            $incidentType->incident_type
        );

        $response->assertSee($staff->forename);

        $response->assertSee($staff->surname);

        $response->assertSee('Open');
    }

    public function test_incident_can_be_assigned_to_active_application_user_when_recorded(): void
    {
        $recordingUser = $this->createApplicationUser(
            'Recording',
            'User',
            'recording@example.com'
        );

        $assignedUser = $this->createApplicationUser(
            'Incident',
            'Manager',
            'manager@example.com'
        );

        $asset = $this->createAsset();

        $staff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $this->createIncidentStatus('Open');

        $this->actingAs($recordingUser);

        $response = $this->post(
            route('assets.incidents.store', $asset),
            [
                'incident_type_id' => $incidentType->id,
                'affected_staff_id' => $staff->id,
                'assigned_to_user_id' => $assignedUser->id,
                'incident_date' => '2026-10-09',
                'description' => 'Screen damaged.',
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $this->assertDatabaseHas(
            'asset_incidents',
            [
                'asset_id' => $asset->id,
                'assigned_to_user_id' => $assignedUser->id,
                'incident_type_id' => $incidentType->id,
            ]
        );
    }

    public function test_authorised_user_can_update_incident_status_assignment_and_action(): void
    {
        $user = $this->createApplicationUser();

        $assignedUser = $this->createApplicationUser(
            'Incident',
            'Owner',
            'owner@example.com'
        );

        $asset = $this->createAsset();

        $staff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus('Open');

        $inProgressStatus =
            $this->createIncidentStatus('In Progress');

        $incident = $this->createIncident(
            $asset,
            $staff,
            $incidentType,
            $openStatus
        );

        $this->actingAs($user);

        $response = $this->put(
            route(
                'assets.incidents.update',
                [$asset, $incident]
            ),
            [
                'incident_status_id' =>
                    $inProgressStatus->id,

                'assigned_to_user_id' =>
                    $assignedUser->id,

                'action_taken' =>
                    'Device inspected and supplier contacted.',

                'warranty_claim_ref' => null,
                'warranty_claim_date' => null,
                'warranty_excess' => null,
                'associated_cost' => null,
                'resolved_date' => null,
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $this->assertDatabaseHas(
            'asset_incidents',
            [
                'id' => $incident->id,
                'incident_status_id' =>
                    $inProgressStatus->id,

                'assigned_to_user_id' =>
                    $assignedUser->id,

                'action_taken' =>
                    'Device inspected and supplier contacted.',
            ]
        );
    }

    public function test_authorised_user_can_record_warranty_and_cost_details(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $staff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus('Open');

        $incident = $this->createIncident(
            $asset,
            $staff,
            $incidentType,
            $openStatus
        );

        $this->actingAs($user);

        $response = $this->put(
            route(
                'assets.incidents.update',
                [$asset, $incident]
            ),
            [
                'incident_status_id' => $openStatus->id,
                'assigned_to_user_id' => $user->id,
                'action_taken' =>
                    'Warranty claim submitted.',

                'warranty_claim_ref' => 'WR-12345',
                'warranty_claim_date' => '2026-10-10',
                'warranty_excess' => '75.00',
                'associated_cost' => '125.50',
                'resolved_date' => null,
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $incident->refresh();

        $this->assertSame(
            'WR-12345',
            $incident->warranty_claim_ref
        );

        $this->assertSame(
            '2026-10-10',
            $incident->warranty_claim_date->format('Y-m-d')
        );

        $this->assertSame(
            '75.00',
            $incident->warranty_excess
        );

        $this->assertSame(
            '125.50',
            $incident->associated_cost
        );
    }

    public function test_incident_can_be_marked_resolved_with_valid_resolution_date(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $staff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus('Open');

        $resolvedStatus =
            $this->createIncidentStatus('Resolved');

        $incident = $this->createIncident(
            $asset,
            $staff,
            $incidentType,
            $openStatus,
            $user
        );

        $this->actingAs($user);

        $response = $this->put(
            route(
                'assets.incidents.update',
                [$asset, $incident]
            ),
            [
                'incident_status_id' =>
                    $resolvedStatus->id,

                'assigned_to_user_id' => $user->id,

                'action_taken' =>
                    'Replacement part fitted and tested.',

                'warranty_claim_ref' => null,
                'warranty_claim_date' => null,
                'warranty_excess' => null,
                'associated_cost' => null,
                'resolved_date' => '2026-10-10',
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $incident->refresh();

        $this->assertSame(
            $resolvedStatus->id,
            $incident->incident_status_id
        );

        $this->assertSame(
            '2026-10-10',
            $incident->resolved_date->format('Y-m-d')
        );
    }

    public function test_resolved_incident_requires_resolution_date(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $staff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus('Open');

        $resolvedStatus =
            $this->createIncidentStatus('Resolved');

        $incident = $this->createIncident(
            $asset,
            $staff,
            $incidentType,
            $openStatus
        );

        $this->actingAs($user);

        $response = $this
            ->from(
                route(
                    'assets.incidents.edit',
                    [$asset, $incident]
                )
            )
            ->put(
                route(
                    'assets.incidents.update',
                    [$asset, $incident]
                ),
                [
                    'incident_status_id' =>
                        $resolvedStatus->id,

                    'assigned_to_user_id' => $user->id,
                    'action_taken' => 'Issue resolved.',
                    'warranty_claim_ref' => null,
                    'warranty_claim_date' => null,
                    'warranty_excess' => null,
                    'associated_cost' => null,
                    'resolved_date' => null,
                ]
            );

        $response->assertRedirect(
            route(
                'assets.incidents.edit',
                [$asset, $incident]
            )
        );

        $response->assertSessionHasErrors(
            'resolved_date'
        );

        $incident->refresh();

        $this->assertSame(
            $openStatus->id,
            $incident->incident_status_id
        );

        $this->assertNull(
            $incident->resolved_date
        );
    }

    public function test_resolution_date_before_incident_date_is_rejected(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $staff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus('Open');

        $resolvedStatus =
            $this->createIncidentStatus('Resolved');

        $incident = $this->createIncident(
            $asset,
            $staff,
            $incidentType,
            $openStatus
        );

        $this->actingAs($user);

        $response = $this->put(
            route(
                'assets.incidents.update',
                [$asset, $incident]
            ),
            [
                'incident_status_id' =>
                    $resolvedStatus->id,

                'assigned_to_user_id' => $user->id,
                'action_taken' => 'Issue resolved.',
                'warranty_claim_ref' => null,
                'warranty_claim_date' => null,
                'warranty_excess' => null,
                'associated_cost' => null,
                'resolved_date' => '2026-10-08',
            ]
        );

        $response->assertSessionHasErrors(
            'resolved_date'
        );

        $incident->refresh();

        $this->assertSame(
            $openStatus->id,
            $incident->incident_status_id
        );

        $this->assertNull(
            $incident->resolved_date
        );
    }

    public function test_reopening_incident_clears_resolution_date(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $staff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus('Open');

        $resolvedStatus =
            $this->createIncidentStatus('Resolved');

        $incident = $this->createIncident(
            $asset,
            $staff,
            $incidentType,
            $resolvedStatus,
            $user
        );

        $incident->update([
            'resolved_date' => '2026-10-10',
        ]);

        $this->actingAs($user);

        $response = $this->put(
            route(
                'assets.incidents.update',
                [$asset, $incident]
            ),
            [
                'incident_status_id' => $openStatus->id,
                'assigned_to_user_id' => $user->id,
                'action_taken' =>
                    'Issue reopened for further investigation.',

                'warranty_claim_ref' => null,
                'warranty_claim_date' => null,
                'warranty_excess' => null,
                'associated_cost' => null,

                'resolved_date' => '2026-10-10',
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $incident->refresh();

        $this->assertSame(
            $openStatus->id,
            $incident->incident_status_id
        );

        $this->assertNull(
            $incident->resolved_date
        );
    }

    public function test_inactive_application_user_cannot_be_assigned_to_incident(): void
    {
        $user = $this->createApplicationUser();

        $inactiveUser = $this->createApplicationUser(
            'Inactive',
            'User',
            'inactive@example.com',
            false
        );

        $asset = $this->createAsset();

        $staff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus('Open');

        $incident = $this->createIncident(
            $asset,
            $staff,
            $incidentType,
            $openStatus
        );

        $this->actingAs($user);

        $response = $this->put(
            route(
                'assets.incidents.update',
                [$asset, $incident]
            ),
            [
                'incident_status_id' => $openStatus->id,
                'assigned_to_user_id' =>
                    $inactiveUser->id,

                'action_taken' => null,
                'warranty_claim_ref' => null,
                'warranty_claim_date' => null,
                'warranty_excess' => null,
                'associated_cost' => null,
                'resolved_date' => null,
            ]
        );

        $response->assertSessionHasErrors(
            'assigned_to_user_id'
        );

        $incident->refresh();

        $this->assertNull(
            $incident->assigned_to_user_id
        );
    }

    public function test_incident_cannot_be_managed_through_different_asset(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset(
            'ASSET-001'
        );

        $differentAsset = $this->createAsset(
            'ASSET-002'
        );

        $staff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus('Open');

        $incident = $this->createIncident(
            $asset,
            $staff,
            $incidentType,
            $openStatus
        );

        $this->actingAs($user);

        $this->get(
            route(
                'assets.incidents.edit',
                [$differentAsset, $incident]
            )
        )->assertNotFound();

        $this->put(
            route(
                'assets.incidents.update',
                [$differentAsset, $incident]
            ),
            [
                'incident_status_id' => $openStatus->id,
                'assigned_to_user_id' => $user->id,
                'action_taken' => null,
                'warranty_claim_ref' => null,
                'warranty_claim_date' => null,
                'warranty_excess' => null,
                'associated_cost' => null,
                'resolved_date' => null,
            ]
        )->assertNotFound();
    }

    public function test_guest_cannot_access_or_update_incident_management(): void
    {
        $asset = $this->createAsset();

        $staff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus('Open');

        $incident = $this->createIncident(
            $asset,
            $staff,
            $incidentType,
            $openStatus
        );

        $this->get(
            route(
                'assets.incidents.edit',
                [$asset, $incident]
            )
        )->assertRedirect(route('login'));

        $this->put(
            route(
                'assets.incidents.update',
                [$asset, $incident]
            ),
            [
                'incident_status_id' => $openStatus->id,
                'assigned_to_user_id' => null,
                'action_taken' => 'Unauthorised change.',
                'warranty_claim_ref' => null,
                'warranty_claim_date' => null,
                'warranty_excess' => null,
                'associated_cost' => null,
                'resolved_date' => null,
            ]
        )->assertRedirect(route('login'));

        $incident->refresh();

        $this->assertNull(
            $incident->action_taken
        );
    }
}