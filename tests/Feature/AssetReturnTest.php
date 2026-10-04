<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCondition;
use App\Models\AssetStatus;
use App\Models\AssetSubtype;
use App\Models\AssetType;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetReturnTest extends TestCase
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
        string $assetTag = 'ASSET-001',
        string $statusName = 'Operational'
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
                'asset_status' => $statusName,
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

    private function createActiveAssignment(
        Asset $asset,
        Staff $staff,
        User $assignedBy,
        string $assignedDate = '2026-10-01',
        ?string $notes = 'Issued for work.'
    ): AssetAssignment {
        return AssetAssignment::create([
            'asset_id' => $asset->id,
            'assigned_to_id' => $staff->id,
            'assigned_by_id' => $assignedBy->id,
            'assigned_date' => $assignedDate,
            'returned_date' => null,
            'returned_by_id' => null,
            'notes' => $notes,
        ]);
    }

    public function test_authorised_user_can_view_return_form_for_assigned_asset(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $this->createActiveAssignment(
            $asset,
            $staff,
            $user
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.return.create', $asset)
        );

        $response->assertOk();
        $response->assertSee('Return Asset');
        $response->assertSee($asset->asset_tag);
    }

    public function test_return_form_displays_current_assignment_details(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff('Jane', 'Doe');
        $asset = $this->createAsset();

        $this->createActiveAssignment(
            $asset,
            $staff,
            $user,
            '2026-10-01',
            'Issued for testing.'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.return.create', $asset)
        );

        $response->assertOk();

        $response->assertSeeInOrder([
            'Jane',
            'Doe',
        ]);

        $response->assertSee('01/10/2026');
        $response->assertSee('Issued for testing.');
    }

    public function test_authorised_user_can_return_assigned_asset(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $assignment = $this->createActiveAssignment(
            $asset,
            $staff,
            $user
        );

        $this->actingAs($user);

        $response = $this->post(
            route('assets.return.store', $asset),
            [
                'returned_date' => '2026-10-04',
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $this->assertDatabaseHas('asset_assignments', [
            'id' => $assignment->id,
            'asset_id' => $asset->id,
            'assigned_to_id' => $staff->id,
            'returned_by_id' => $user->id,
        ]);

        $assignment->refresh();

        $this->assertSame(
            '2026-10-04',
            $assignment->returned_date->format('Y-m-d')
        );
    }

    public function test_return_records_authenticated_user_who_processed_it(): void
    {
        $assignedBy = $this->createApplicationUser(
            email: 'assigner@example.com'
        );

        $returnedBy = $this->createApplicationUser(
            email: 'returner@example.com'
        );

        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $assignment = $this->createActiveAssignment(
            $asset,
            $staff,
            $assignedBy
        );

        $this->actingAs($returnedBy);

        $this->post(
            route('assets.return.store', $asset),
            [
                'returned_date' => '2026-10-04',
            ]
        );

        $assignment->refresh();

        $this->assertSame(
            $returnedBy->id,
            $assignment->returned_by_id
        );
    }

    public function test_return_date_cannot_precede_assignment_date(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $assignment = $this->createActiveAssignment(
            $asset,
            $staff,
            $user,
            '2026-10-04'
        );

        $this->actingAs($user);

        $response = $this->post(
            route('assets.return.store', $asset),
            [
                'returned_date' => '2026-10-03',
            ]
        );

        $response->assertSessionHasErrors('returned_date');

        $assignment->refresh();

        $this->assertNull($assignment->returned_date);
        $this->assertNull($assignment->returned_by_id);
    }

    public function test_unassigned_asset_cannot_be_returned(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();

        $this->actingAs($user);

        $getResponse = $this->get(
            route('assets.return.create', $asset)
        );

        $getResponse->assertSessionHasErrors('asset');

        $postResponse = $this->post(
            route('assets.return.store', $asset),
            [
                'returned_date' => '2026-10-04',
            ]
        );

        $postResponse->assertSessionHasErrors('asset');

        $this->assertDatabaseMissing('asset_assignments', [
            'asset_id' => $asset->id,
        ]);
    }

    public function test_return_preserves_existing_assignment_record(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $assignment = $this->createActiveAssignment(
            $asset,
            $staff,
            $user,
            '2026-10-01',
            'Original assignment notes.'
        );

        $originalAssignmentId = $assignment->id;

        $this->actingAs($user);

        $this->post(
            route('assets.return.store', $asset),
            [
                'returned_date' => '2026-10-04',
            ]
        );

        $assignment->refresh();

        $this->assertSame(
            $originalAssignmentId,
            $assignment->id
        );

        $this->assertSame(
            $staff->id,
            $assignment->assigned_to_id
        );

        $this->assertSame(
            $user->id,
            $assignment->assigned_by_id
        );

        $this->assertSame(
            '2026-10-01',
            $assignment->assigned_date->format('Y-m-d')
        );

        $this->assertSame(
            'Original assignment notes.',
            $assignment->notes
        );

        $this->assertSame(
            1,
            AssetAssignment::where(
                'asset_id',
                $asset->id
            )->count()
        );
    }

    public function test_returned_asset_has_no_active_assignment_and_displays_in_stock(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $this->createActiveAssignment(
            $asset,
            $staff,
            $user
        );

        $this->actingAs($user);

        $this->post(
            route('assets.return.store', $asset),
            [
                'returned_date' => '2026-10-04',
            ]
        );

        $this->assertFalse(
            AssetAssignment::where(
                'asset_id',
                $asset->id
            )
                ->whereNull('returned_date')
                ->exists()
        );

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();
        $response->assertSee('In Stock');
        $response->assertSee('Assign Asset');
    }

    public function test_assigned_non_operational_asset_can_still_be_returned(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();

        $asset = $this->createAsset(
            'ASSET-REPAIR',
            'Under Repair'
        );

        $assignment = $this->createActiveAssignment(
            $asset,
            $staff,
            $user
        );

        $this->actingAs($user);

        $response = $this->post(
            route('assets.return.store', $asset),
            [
                'returned_date' => '2026-10-04',
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $assignment->refresh();

        $this->assertSame(
            '2026-10-04',
            $assignment->returned_date->format('Y-m-d')
        );

        $this->assertSame(
            $user->id,
            $assignment->returned_by_id
        );
    }

    public function test_guest_cannot_access_asset_return_functionality(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $assignment = $this->createActiveAssignment(
            $asset,
            $staff,
            $user
        );

        $this->get(
            route('assets.return.create', $asset)
        )->assertRedirect(route('login'));

        $this->post(
            route('assets.return.store', $asset),
            [
                'returned_date' => '2026-10-04',
            ]
        )->assertRedirect(route('login'));

        $assignment->refresh();

        $this->assertNull($assignment->returned_date);
        $this->assertNull($assignment->returned_by_id);
    }
}