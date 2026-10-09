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

class AssetIncidentHistoryTest extends TestCase
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
        string $email = 'user@example.com'
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
            'active' => true,
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
        $assetType = AssetType::create([
            'asset_type' => 'IT Equipment',
            'active' => true,
        ]);

        $assetSubtype = AssetSubtype::create([
            'asset_type_id' => $assetType->id,
            'asset_subtype' => 'Laptop',
            'active' => true,
        ]);

        $assetStatus = AssetStatus::create([
            'asset_status' => 'Operational',
            'active' => true,
        ]);

        $assetCondition = AssetCondition::create([
            'asset_condition' => 'Good',
            'active' => true,
        ]);

        return Asset::create([
            'asset_subtype_id' => $assetSubtype->id,
            'asset_status_id' => $assetStatus->id,
            'asset_condition_id' => $assetCondition->id,
            'asset_tag' => $assetTag,
            'serial_num' => 'SERIAL-' . $assetTag,
            'delivery_date' => '2026-01-10',
            'purchase_date' => '2026-01-05',
            'warranty_expiry' => '2029-01-05',
            'retired_date' => null,
            'disposal_date' => null,
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
        string $incidentDate,
        ?string $resolvedDate = null,
        ?User $assignedUser = null,
        ?string $actionTaken = null
    ): AssetIncident {
        return AssetIncident::create([
            'asset_id' => $asset->id,
            'affected_staff_id' => $staff->id,
            'assigned_to_user_id' => $assignedUser?->id,
            'incident_type_id' => $incidentType->id,
            'incident_status_id' => $incidentStatus->id,
            'incident_date' => $incidentDate,
            'description' => 'Test incident description.',
            'action_taken' => $actionTaken,
            'warranty_claim_ref' => null,
            'warranty_claim_date' => null,
            'warranty_excess' => null,
            'associated_cost' => null,
            'resolved_date' => $resolvedDate,
        ]);
    }

    public function test_authorised_user_can_view_resolved_incident_history(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $affectedStaff = $this->createStaff(
            'Affected',
            'Employee'
        );

        $incidentType = $this->createIncidentType(
            'Equipment Damage'
        );

        $resolvedStatus = $this->createIncidentStatus(
            'Resolved'
        );

        $incident = $this->createIncident(
            $asset,
            $affectedStaff,
            $incidentType,
            $resolvedStatus,
            '2026-10-01',
            '2026-10-05',
            $user,
            'Replacement part fitted and tested.'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSee('Incident History');

        $response->assertSee(
            '#' . $incident->id
        );

        $response->assertSee(
            'Equipment Damage'
        );

        $response->assertSee(
            '01/10/2026'
        );

        $response->assertSee(
            'Affected'
        );

        $response->assertSee(
            'Employee'
        );

        $response->assertSee(
            $user->staff->forename
        );

        $response->assertSee(
            $user->staff->surname
        );

        $response->assertSee(
            '05/10/2026'
        );

        $response->assertSee(
            'Replacement part fitted and tested.'
        );
    }

    public function test_historical_incident_remains_associated_after_asset_update(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $affectedStaff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $resolvedStatus = $this->createIncidentStatus(
            'Resolved'
        );

        $incident = $this->createIncident(
            $asset,
            $affectedStaff,
            $incidentType,
            $resolvedStatus,
            '2026-09-20',
            '2026-09-25',
            $user,
            'Incident resolved.'
        );

        $newCondition = AssetCondition::create([
            'asset_condition' => 'Fair',
            'active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->put(
            route('assets.update', $asset),
            [
                'asset_subtype_id' =>
                    $asset->asset_subtype_id,

                'asset_status_id' =>
                    $asset->asset_status_id,

                'asset_condition_id' =>
                    $newCondition->id,

                'asset_tag' =>
                    $asset->asset_tag,

                'serial_num' =>
                    $asset->serial_num,

                'delivery_date' =>
                    $asset->delivery_date?->format('Y-m-d'),

                'purchase_date' =>
                    $asset->purchase_date?->format('Y-m-d'),

                'retired_date' => null,

                'warranty_expiry' =>
                    $asset->warranty_expiry?->format('Y-m-d'),

                'disposal_date' => null,
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $this->assertDatabaseHas(
            'asset_incidents',
            [
                'id' => $incident->id,
                'asset_id' => $asset->id,
            ]
        );

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSee(
            '#' . $incident->id
        );

        $response->assertSee(
            'Incident resolved.'
        );
    }

    public function test_historical_incident_remains_visible_after_asset_is_archived(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $affectedStaff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $resolvedStatus = $this->createIncidentStatus(
            'Resolved'
        );

        $incident = $this->createIncident(
            $asset,
            $affectedStaff,
            $incidentType,
            $resolvedStatus,
            '2026-09-10',
            '2026-09-12',
            $user,
            'Historical incident retained.'
        );

        $this->actingAs($user);

        $response = $this->patch(
            route('assets.archive', $asset)
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $asset->refresh();

        $this->assertNotNull(
            $asset->archived_at
        );

        $this->assertDatabaseHas(
            'asset_incidents',
            [
                'id' => $incident->id,
                'asset_id' => $asset->id,
            ]
        );

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSee(
            '#' . $incident->id
        );

        $response->assertSee(
            'Historical incident retained.'
        );
    }

    public function test_resolved_incidents_are_displayed_newest_first(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $affectedStaff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $resolvedStatus = $this->createIncidentStatus(
            'Resolved'
        );

        $olderIncident = $this->createIncident(
            $asset,
            $affectedStaff,
            $incidentType,
            $resolvedStatus,
            '2026-09-01',
            '2026-09-02',
            $user,
            'Older resolved incident.'
        );

        $newerIncident = $this->createIncident(
            $asset,
            $affectedStaff,
            $incidentType,
            $resolvedStatus,
            '2026-10-01',
            '2026-10-02',
            $user,
            'Newer resolved incident.'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSeeInOrder([
            '#' . $newerIncident->id,
            '#' . $olderIncident->id,
        ]);
    }

    public function test_current_incidents_are_displayed_newest_first(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $affectedStaff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus(
            'Open'
        );

        $olderIncident = $this->createIncident(
            $asset,
            $affectedStaff,
            $incidentType,
            $openStatus,
            '2026-09-01',
            null,
            $user
        );

        $newerIncident = $this->createIncident(
            $asset,
            $affectedStaff,
            $incidentType,
            $openStatus,
            '2026-10-01',
            null,
            $user
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSeeInOrder([
            '#' . $newerIncident->id,
            '#' . $olderIncident->id,
        ]);
    }

    public function test_current_and_resolved_incidents_are_displayed_in_separate_sections(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $affectedStaff = $this->createStaff();

        $incidentType = $this->createIncidentType();

        $openStatus = $this->createIncidentStatus(
            'Open'
        );

        $resolvedStatus = $this->createIncidentStatus(
            'Resolved'
        );

        $openIncident = $this->createIncident(
            $asset,
            $affectedStaff,
            $incidentType,
            $openStatus,
            '2026-10-08',
            null,
            $user
        );

        $resolvedIncident = $this->createIncident(
            $asset,
            $affectedStaff,
            $incidentType,
            $resolvedStatus,
            '2026-10-07',
            '2026-10-08',
            $user,
            'Resolved action.'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSeeInOrder([
            'Current Incidents',
            '#' . $openIncident->id,
            'Incident History',
            '#' . $resolvedIncident->id,
        ]);
    }
}