<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAssignment;
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

class AssetIncidentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function createApplicationUser(
        string $roleName = 'Authorised User',
        string $email = 'user@example.com'
    ): User {
        $role = Role::firstOrCreate([
            'role_name' => $roleName,
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
            'email' => $email,
            'password' => 'Password123!',
            'active' => true,
        ]);
    }

    private function createStaff(
        string $forename = 'Jane',
        string $surname = 'Doe',
        bool $active = true
    ): Staff {
        return Staff::create([
            'forename' => $forename,
            'surname' => $surname,
            'regul8_staff_id' => null,
            'active' => $active,
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
        string $name = 'Damage',
        bool $active = true
    ): IncidentType {
        return IncidentType::create([
            'incident_type' => $name,
            'active' => $active,
        ]);
    }

    private function createOpenStatus(): IncidentStatus
    {
        return IncidentStatus::create([
            'incident_status' => 'Open',
            'active' => true,
        ]);
    }

    public function test_authorised_user_can_view_record_incident_form(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();
        $staff = $this->createStaff();
        $incidentType = $this->createIncidentType();

        $this->actingAs($user);

        $response = $this->get(
            route('assets.incidents.create', $asset)
        );

        $response->assertOk();
        $response->assertSee('Record Incident');
        $response->assertSee($asset->asset_tag);
        $response->assertSee($incidentType->incident_type);
        $response->assertSee($staff->forename);
        $response->assertSee($staff->surname);
    }

    public function test_only_active_incident_types_are_available_for_selection(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();

        $activeType = $this->createIncidentType(
            'Damage',
            true
        );

        $inactiveType = $this->createIncidentType(
            'Legacy Fault',
            false
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.incidents.create', $asset)
        );

        $response->assertOk();

        $response->assertSee(
            'value="' . $activeType->id . '"',
            false
        );

        $response->assertDontSee(
            'value="' . $inactiveType->id . '"',
            false
        );
    }

    public function test_authorised_user_can_record_incident_against_asset(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();
        $staff = $this->createStaff();
        $incidentType = $this->createIncidentType();
        $openStatus = $this->createOpenStatus();

        $this->actingAs($user);

        $response = $this->post(
            route('assets.incidents.store', $asset),
            [
                'incident_type_id' => $incidentType->id,
                'affected_staff_id' => $staff->id,
                'incident_date' => '2026-10-09',
                'description' => 'Laptop casing damaged during use.',
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $response->assertSessionHas(
            'success',
            'Incident recorded successfully.'
        );

        $this->assertDatabaseHas('asset_incidents', [
            'asset_id' => $asset->id,
            'affected_staff_id' => $staff->id,
            'assigned_to_user_id' => null,
            'incident_type_id' => $incidentType->id,
            'incident_status_id' => $openStatus->id,
            'description' => 'Laptop casing damaged during use.',
            'action_taken' => null,
            'resolved_date' => null,
        ]);

        $incident = AssetIncident::where(
            'asset_id',
            $asset->id
        )->firstOrFail();

        $this->assertSame(
            '2026-10-09',
            $incident->incident_date->format('Y-m-d')
        );
    }

    public function test_incident_can_be_recorded_against_unassigned_asset(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset('ASSET-IN-STOCK');
        $staff = $this->createStaff();
        $incidentType = $this->createIncidentType();

        $this->createOpenStatus();

        $this->actingAs($user);

        $response = $this->post(
            route('assets.incidents.store', $asset),
            [
                'incident_type_id' => $incidentType->id,
                'affected_staff_id' => $staff->id,
                'incident_date' => '2026-10-09',
                'description' => 'Damage identified while asset was in stock.',
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $this->assertDatabaseHas('asset_incidents', [
            'asset_id' => $asset->id,
            'affected_staff_id' => $staff->id,
            'incident_type_id' => $incidentType->id,
        ]);

        $this->assertDatabaseMissing('asset_assignments', [
            'asset_id' => $asset->id,
        ]);
    }

    public function test_current_asset_holder_is_preselected_on_incident_form(): void
    {
        $user = $this->createApplicationUser();
        $holder = $this->createStaff('Current', 'Holder');
        $asset = $this->createAsset();

        $this->createIncidentType();

        AssetAssignment::create([
            'asset_id' => $asset->id,
            'assigned_to_id' => $holder->id,
            'assigned_by_id' => $user->id,
            'assigned_date' => '2026-10-01',
            'returned_date' => null,
            'returned_by_id' => null,
            'notes' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(
            route('assets.incidents.create', $asset)
        );

        $response->assertOk();

        $response->assertSeeInOrder([
            'Current',
            'Holder',
        ]);

        $response->assertSee(
            'value="' . $holder->id . '"',
            false
        );
    }

    public function test_required_incident_fields_are_validated(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();

        $this->createOpenStatus();

        $this->actingAs($user);

        $response = $this->post(
            route('assets.incidents.store', $asset),
            []
        );

        $response->assertSessionHasErrors([
            'incident_type_id',
            'affected_staff_id',
            'incident_date',
            'description',
        ]);

        $this->assertDatabaseMissing('asset_incidents', [
            'asset_id' => $asset->id,
        ]);
    }

    public function test_inactive_incident_type_cannot_be_used_to_create_incident(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();
        $staff = $this->createStaff();

        $inactiveType = $this->createIncidentType(
            'Inactive Type',
            false
        );

        $this->createOpenStatus();

        $this->actingAs($user);

        $response = $this->post(
            route('assets.incidents.store', $asset),
            [
                'incident_type_id' => $inactiveType->id,
                'affected_staff_id' => $staff->id,
                'incident_date' => '2026-10-09',
                'description' => 'Test incident.',
            ]
        );

        $response->assertSessionHasErrors(
            'incident_type_id'
        );

        $this->assertDatabaseMissing('asset_incidents', [
            'asset_id' => $asset->id,
            'incident_type_id' => $inactiveType->id,
        ]);
    }

    public function test_successful_incident_prompts_user_to_review_asset_state(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();
        $staff = $this->createStaff();
        $incidentType = $this->createIncidentType();

        $this->createOpenStatus();

        $this->actingAs($user);

        $response = $this->post(
            route('assets.incidents.store', $asset),
            [
                'incident_type_id' => $incidentType->id,
                'affected_staff_id' => $staff->id,
                'incident_date' => '2026-10-09',
                'description' => 'Asset damaged during use.',
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $response->assertSessionHas(
            'review_asset_state',
            true
        );

        $followUpResponse = $this
            ->withSession([
                'review_asset_state' => true,
            ])
            ->get(
                route('assets.show', $asset)
            );

        $followUpResponse->assertOk();
        $followUpResponse->assertSee('Review asset state');
        $followUpResponse->assertSee('Condition');
        $followUpResponse->assertSee('Operational Status');
        $followUpResponse->assertSee('Review Asset');

        $followUpResponse->assertSee(
            route('assets.edit', $asset),
            false
        );
    }

    public function test_guest_cannot_access_or_record_asset_incident(): void
    {
        $asset = $this->createAsset();
        $staff = $this->createStaff();
        $incidentType = $this->createIncidentType();

        $this->createOpenStatus();

        $this->get(
            route('assets.incidents.create', $asset)
        )->assertRedirect(
            route('login')
        );

        $this->post(
            route('assets.incidents.store', $asset),
            [
                'incident_type_id' => $incidentType->id,
                'affected_staff_id' => $staff->id,
                'incident_date' => '2026-10-09',
                'description' => 'Unauthorised incident attempt.',
            ]
        )->assertRedirect(
            route('login')
        );

        $this->assertDatabaseMissing('asset_incidents', [
            'asset_id' => $asset->id,
        ]);
    }
}