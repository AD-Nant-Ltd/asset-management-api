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

class AssetManagementTest extends TestCase
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

    private function createClassification(): array
    {
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

        return [
            'type' => $assetType,
            'subtype' => $assetSubtype,
            'status' => $assetStatus,
            'condition' => $assetCondition,
        ];
    }

    private function createAsset(string $assetTag = 'ASSET-001'): Asset
    {
        $classification = $this->createClassification();

        return Asset::create([
            'asset_subtype_id' => $classification['subtype']->id,
            'asset_status_id' => $classification['status']->id,
            'asset_condition_id' => $classification['condition']->id,
            'asset_tag' => $assetTag,
            'serial_num' => 'SERIAL-' . $assetTag,
            'delivery_date' => '2026-09-01',
            'purchase_date' => '2026-08-15',
            'warranty_expiry' => '2029-08-15',
        ]);
    }

    public function test_authorised_user_can_view_asset_list(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $this->actingAs($user);

        $response = $this->get(route('assets.index'));

        $response->assertOk();
        $response->assertSee($asset->asset_tag);
        $response->assertSee('IT Equipment');
        $response->assertSee('Laptop');
        $response->assertSee('Operational');
        $response->assertSee('Good');
    }

    public function test_authorised_user_can_create_asset_with_valid_classification(): void
    {
        $user = $this->createApplicationUser();

        $classification = $this->createClassification();

        $this->actingAs($user);

        $response = $this->post(route('assets.store'), [
            'asset_subtype_id' => $classification['subtype']->id,
            'asset_status_id' => $classification['status']->id,
            'asset_condition_id' => $classification['condition']->id,
            'asset_tag' => 'ASSET-100',
            'serial_num' => 'SERIAL-100',
            'delivery_date' => '2026-09-20',
            'purchase_date' => '2026-09-15',
            'warranty_expiry' => '2029-09-15',
        ]);

        $response->assertRedirect(route('assets.index'));

        $this->assertDatabaseHas('assets', [
            'asset_subtype_id' => $classification['subtype']->id,
            'asset_status_id' => $classification['status']->id,
            'asset_condition_id' => $classification['condition']->id,
            'asset_tag' => 'ASSET-100',
            'serial_num' => 'SERIAL-100',
        ]);
    }

    public function test_duplicate_asset_tag_is_rejected(): void
    {
        $user = $this->createApplicationUser();

        $existingAsset = $this->createAsset('ASSET-100');

        $this->actingAs($user);

        $response = $this->post(route('assets.store'), [
            'asset_subtype_id' => $existingAsset->asset_subtype_id,
            'asset_status_id' => $existingAsset->asset_status_id,
            'asset_condition_id' => $existingAsset->asset_condition_id,
            'asset_tag' => 'ASSET-100',
            'serial_num' => 'DIFFERENT-SERIAL',
        ]);

        $response->assertSessionHasErrors('asset_tag');

        $this->assertSame(
            1,
            Asset::where('asset_tag', 'ASSET-100')->count()
        );
    }

    public function test_invalid_asset_classification_references_are_rejected(): void
    {
        $user = $this->createApplicationUser();

        $this->actingAs($user);

        $response = $this->post(route('assets.store'), [
            'asset_subtype_id' => 999999,
            'asset_status_id' => 999999,
            'asset_condition_id' => 999999,
            'asset_tag' => 'ASSET-INVALID',
            'serial_num' => 'INVALID-SERIAL',
        ]);

        $response->assertSessionHasErrors([
            'asset_subtype_id',
            'asset_status_id',
            'asset_condition_id',
        ]);

        $this->assertDatabaseMissing('assets', [
            'asset_tag' => 'ASSET-INVALID',
        ]);
    }

    public function test_inactive_classification_values_are_not_available_when_creating_asset(): void
    {
        $user = $this->createApplicationUser();

        $activeType = AssetType::create([
            'asset_type' => 'IT Equipment',
            'active' => true,
        ]);

        AssetSubtype::create([
            'asset_type_id' => $activeType->id,
            'asset_subtype' => 'Inactive Laptop',
            'active' => false,
        ]);

        AssetStatus::create([
            'asset_status' => 'Inactive Status',
            'active' => false,
        ]);

        AssetCondition::create([
            'asset_condition' => 'Inactive Condition',
            'active' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('assets.create'));

        $response->assertOk();
        $response->assertDontSee('Inactive Laptop');
        $response->assertDontSee('Inactive Status');
        $response->assertDontSee('Inactive Condition');
    }

    public function test_authorised_user_can_view_existing_asset_record(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $this->actingAs($user);

        $response = $this->get(route('assets.show', $asset));

        $response->assertOk();
        $response->assertSee($asset->asset_tag);
        $response->assertSee($asset->serial_num);
        $response->assertSee('IT Equipment');
        $response->assertSee('Laptop');
        $response->assertSee('Operational');
        $response->assertSee('Good');
    }

    public function test_authorised_user_can_update_asset_details_and_lifecycle_data(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $newStatus = AssetStatus::create([
            'asset_status' => 'Under Repair',
            'active' => true,
        ]);

        $newCondition = AssetCondition::create([
            'asset_condition' => 'Damaged',
            'active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->put(
            route('assets.update', $asset),
            [
                'asset_subtype_id' => $asset->asset_subtype_id,
                'asset_status_id' => $newStatus->id,
                'asset_condition_id' => $newCondition->id,
                'asset_tag' => 'ASSET-UPDATED',
                'serial_num' => 'SERIAL-UPDATED',
                'delivery_date' => '2026-09-01',
                'purchase_date' => '2026-08-15',
                'warranty_expiry' => '2029-08-15',
                'retired_date' => '2031-01-01 00:00:00',
                'disposal_date' => null,
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'asset_status_id' => $newStatus->id,
            'asset_condition_id' => $newCondition->id,
            'asset_tag' => 'ASSET-UPDATED',
            'serial_num' => 'SERIAL-UPDATED',
            'retired_date' => '2031-01-01 00:00:00',
        ]);
    }

    public function test_authorised_user_can_archive_asset_without_deleting_it(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $this->actingAs($user);

        $response = $this->patch(
            route('assets.archive', $asset)
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $asset->refresh();

        $this->assertNotNull($asset->archived_at);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'asset_tag' => 'ASSET-001',
        ]);
    }

    public function test_archiving_asset_preserves_assignment_history(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $assignedStaff = Staff::create([
            'forename' => 'Jane',
            'surname' => 'Doe',
            'regul8_staff_id' => 2001,
            'active' => true,
        ]);

        $assignment = AssetAssignment::create([
            'asset_id' => $asset->id,
            'assigned_to_id' => $assignedStaff->id,
            'assigned_by_id' => $user->id,
            'assigned_date' => '2026-09-20',
            'returned_date' => null,
            'returned_by_id' => null,
            'notes' => 'Test assignment',
        ]);

        $this->actingAs($user);

        $this->patch(route('assets.archive', $asset));

        $this->assertDatabaseHas('asset_assignments', [
            'id' => $assignment->id,
            'asset_id' => $asset->id,
            'assigned_to_id' => $assignedStaff->id,
        ]);

        $this->assertSame(
            1,
            $asset->fresh()->assignments()->count()
        );
    }

    public function test_archiving_asset_preserves_incident_history(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $affectedStaff = Staff::create([
            'forename' => 'Jane',
            'surname' => 'Doe',
            'regul8_staff_id' => 2001,
            'active' => true,
        ]);

        $incidentType = IncidentType::create([
            'incident_type' => 'Fault',
            'active' => true,
        ]);

        $incidentStatus = IncidentStatus::create([
            'incident_status' => 'Open',
            'active' => true,
        ]);

        $incident = AssetIncident::create([
            'asset_id' => $asset->id,
            'affected_staff_id' => $affectedStaff->id,
            'assigned_to_user_id' => $user->id,
            'incident_type_id' => $incidentType->id,
            'incident_status_id' => $incidentStatus->id,
            'incident_date' => '2026-09-25',
            'description' => 'Test asset fault',
        ]);

        $this->actingAs($user);

        $this->patch(route('assets.archive', $asset));

        $this->assertDatabaseHas('asset_incidents', [
            'id' => $incident->id,
            'asset_id' => $asset->id,
            'description' => 'Test asset fault',
        ]);

        $this->assertSame(
            1,
            $asset->fresh()->incidents()->count()
        );
    }

    public function test_archived_asset_edit_page_is_forbidden(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $asset->update([
            'archived_at' => now(),
        ]);

        $this->actingAs($user);

        $this->get(route('assets.edit', $asset))
            ->assertForbidden();
    }

    public function test_archived_asset_cannot_be_updated_by_direct_request(): void
    {
        $user = $this->createApplicationUser();

        $asset = $this->createAsset();

        $asset->update([
            'archived_at' => now(),
        ]);

        $originalAssetTag = $asset->asset_tag;

        $this->actingAs($user);

        $response = $this->put(
            route('assets.update', $asset),
            [
                'asset_subtype_id' => $asset->asset_subtype_id,
                'asset_status_id' => $asset->asset_status_id,
                'asset_condition_id' => $asset->asset_condition_id,
                'asset_tag' => 'ILLEGAL-UPDATE',
                'serial_num' => $asset->serial_num,
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'asset_tag' => $originalAssetTag,
        ]);

        $this->assertDatabaseMissing('assets', [
            'id' => $asset->id,
            'asset_tag' => 'ILLEGAL-UPDATE',
        ]);
    }

    public function test_guest_cannot_access_asset_management(): void
    {
        $asset = $this->createAsset();

        $this->get(route('assets.index'))
            ->assertRedirect(route('login'));

        $this->get(route('assets.create'))
            ->assertRedirect(route('login'));

        $this->get(route('assets.show', $asset))
            ->assertRedirect(route('login'));

        $this->get(route('assets.edit', $asset))
            ->assertRedirect(route('login'));
    }
}