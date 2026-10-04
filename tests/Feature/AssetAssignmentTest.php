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

class AssetAssignmentTest extends TestCase
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

    public function test_authorised_user_can_view_assignment_form_for_eligible_asset(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $this->actingAs($user);

        $response = $this->get(
            route('assets.assign.create', $asset)
        );

        $response->assertOk();
        $response->assertSee('Assign Asset');
        $response->assertSee($asset->asset_tag);
        $response->assertSee($staff->forename);
        $response->assertSee($staff->surname);
    }

    public function test_assignment_form_only_lists_active_staff_members(): void
    {
        $user = $this->createApplicationUser();

        $activeStaff = $this->createStaff(
            'Active',
            'Employee',
            true
        );

        $inactiveStaff = $this->createStaff(
            'Inactive',
            'Employee',
            false
        );

        $asset = $this->createAsset();

        $this->actingAs($user);

        $response = $this->get(
            route('assets.assign.create', $asset)
        );

        $response->assertOk();

        $response->assertSee(
            'value="' . $activeStaff->id . '"',
            false
        );

        $response->assertDontSee(
            'value="' . $inactiveStaff->id . '"',
            false
        );
    }

    public function test_authorised_user_can_assign_asset_to_active_staff_member(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $this->actingAs($user);

        $response = $this->post(
            route('assets.assign.store', $asset),
            [
                'assigned_to_id' => $staff->id,
                'assigned_date' => '2026-10-04',
                'notes' => 'Issued for normal business use.',
            ]
        );

        $response->assertRedirect(
            route('assets.show', $asset)
        );

        $this->assertDatabaseHas('asset_assignments', [
            'asset_id' => $asset->id,
            'assigned_to_id' => $staff->id,
            'assigned_by_id' => $user->id,
            'returned_date' => null,
            'returned_by_id' => null,
            'notes' => 'Issued for normal business use.',
        ]);

        $assignment = AssetAssignment::where(
            'asset_id',
            $asset->id
        )->firstOrFail();

        $this->assertSame(
            '2026-10-04',
            $assignment->assigned_date->format('Y-m-d')
        );
    }

    public function test_assignment_records_authenticated_user_who_processed_it(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $this->actingAs($user);

        $this->post(
            route('assets.assign.store', $asset),
            [
                'assigned_to_id' => $staff->id,
                'assigned_date' => '2026-10-04',
                'notes' => null,
            ]
        );

        $assignment = AssetAssignment::where(
            'asset_id',
            $asset->id
        )->firstOrFail();

        $this->assertSame(
            $user->id,
            $assignment->assigned_by_id
        );
    }

    public function test_current_holder_is_displayed_on_asset_page_after_assignment(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff('Jane', 'Doe');
        $asset = $this->createAsset();

        AssetAssignment::create([
            'asset_id' => $asset->id,
            'assigned_to_id' => $staff->id,
            'assigned_by_id' => $user->id,
            'assigned_date' => '2026-10-04',
            'returned_date' => null,
            'returned_by_id' => null,
            'notes' => 'Issued for work.',
        ]);

        $this->actingAs($user);

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();
        $response->assertSee('Current Holder');
        $response->assertSeeInOrder([
            'Jane',
            'Doe',
        ]);
        $response->assertSee('Issued for work.');
    }

    public function test_asset_with_active_assignment_cannot_be_assigned_again(): void
    {
        $user = $this->createApplicationUser();
        $firstStaff = $this->createStaff('Jane', 'Doe');
        $secondStaff = $this->createStaff('John', 'Smith');
        $asset = $this->createAsset();

        AssetAssignment::create([
            'asset_id' => $asset->id,
            'assigned_to_id' => $firstStaff->id,
            'assigned_by_id' => $user->id,
            'assigned_date' => '2026-10-03',
            'returned_date' => null,
            'returned_by_id' => null,
            'notes' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post(
            route('assets.assign.store', $asset),
            [
                'assigned_to_id' => $secondStaff->id,
                'assigned_date' => '2026-10-04',
                'notes' => null,
            ]
        );

        $response->assertSessionHasErrors('asset');

        $this->assertSame(
            1,
            AssetAssignment::where('asset_id', $asset->id)
                ->whereNull('returned_date')
                ->count()
        );
    }

    public function test_inactive_staff_member_cannot_receive_asset(): void
    {
        $user = $this->createApplicationUser();

        $inactiveStaff = $this->createStaff(
            'Inactive',
            'Employee',
            false
        );

        $asset = $this->createAsset();

        $this->actingAs($user);

        $response = $this->post(
            route('assets.assign.store', $asset),
            [
                'assigned_to_id' => $inactiveStaff->id,
                'assigned_date' => '2026-10-04',
                'notes' => null,
            ]
        );

        $response->assertSessionHasErrors('assigned_to_id');

        $this->assertDatabaseMissing('asset_assignments', [
            'asset_id' => $asset->id,
            'assigned_to_id' => $inactiveStaff->id,
        ]);
    }

    public function test_archived_asset_cannot_be_assigned(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $asset->update([
            'archived_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->post(
            route('assets.assign.store', $asset),
            [
                'assigned_to_id' => $staff->id,
                'assigned_date' => '2026-10-04',
                'notes' => null,
            ]
        );

        $response->assertSessionHasErrors('asset');

        $this->assertDatabaseMissing('asset_assignments', [
            'asset_id' => $asset->id,
        ]);
    }

    public function test_retired_or_disposed_asset_cannot_be_assigned(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();

        $retiredAsset = $this->createAsset(
            'ASSET-RETIRED'
        );

        $retiredAsset->update([
            'retired_date' => '2026-10-01',
        ]);

        $disposedAsset = $this->createAsset(
            'ASSET-DISPOSED'
        );

        $disposedAsset->update([
            'disposal_date' => '2026-10-01',
        ]);

        $this->actingAs($user);

        $retiredResponse = $this->post(
            route('assets.assign.store', $retiredAsset),
            [
                'assigned_to_id' => $staff->id,
                'assigned_date' => '2026-10-04',
                'notes' => null,
            ]
        );

        $retiredResponse->assertSessionHasErrors('asset');

        $disposedResponse = $this->post(
            route('assets.assign.store', $disposedAsset),
            [
                'assigned_to_id' => $staff->id,
                'assigned_date' => '2026-10-04',
                'notes' => null,
            ]
        );

        $disposedResponse->assertSessionHasErrors('asset');

        $this->assertDatabaseMissing('asset_assignments', [
            'asset_id' => $retiredAsset->id,
        ]);

        $this->assertDatabaseMissing('asset_assignments', [
            'asset_id' => $disposedAsset->id,
        ]);
    }

    public function test_future_retirement_date_does_not_prevent_asset_assignment(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();

        $asset = $this->createAsset(
            'ASSET-FUTURE-RETIREMENT'
        );

        $asset->update([
            'retired_date' => today()
                ->addDays(30)
                ->toDateString(),
        ]);

        $this->actingAs($user);

        $assetPageResponse = $this->get(
            route('assets.show', $asset)
        );

        $assetPageResponse->assertOk();
        $assetPageResponse->assertSee('In Stock');
        $assetPageResponse->assertSee('Assign Asset');

        $assignmentFormResponse = $this->get(
            route('assets.assign.create', $asset)
        );

        $assignmentFormResponse->assertOk();

        $assignmentResponse = $this->post(
            route('assets.assign.store', $asset),
            [
                'assigned_to_id' => $staff->id,
                'assigned_date' => today()->toDateString(),
                'notes' => 'Assigned before scheduled retirement.',
            ]
        );

        $assignmentResponse->assertRedirect(
            route('assets.show', $asset)
        );

        $this->assertDatabaseHas('asset_assignments', [
            'asset_id' => $asset->id,
            'assigned_to_id' => $staff->id,
            'assigned_by_id' => $user->id,
            'returned_date' => null,
            'notes' => 'Assigned before scheduled retirement.',
        ]);
    }

    public function test_future_disposal_date_does_not_prevent_asset_assignment(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();

        $asset = $this->createAsset(
            'ASSET-FUTURE-DISPOSAL'
        );

        $asset->update([
            'disposal_date' => today()
                ->addDays(30)
                ->toDateString(),
        ]);

        $this->actingAs($user);

        $assetPageResponse = $this->get(
            route('assets.show', $asset)
        );

        $assetPageResponse->assertOk();
        $assetPageResponse->assertSee('In Stock');
        $assetPageResponse->assertSee('Assign Asset');

        $assignmentFormResponse = $this->get(
            route('assets.assign.create', $asset)
        );

        $assignmentFormResponse->assertOk();

        $assignmentResponse = $this->post(
            route('assets.assign.store', $asset),
            [
                'assigned_to_id' => $staff->id,
                'assigned_date' => today()->toDateString(),
                'notes' => 'Assigned before scheduled disposal.',
            ]
        );

        $assignmentResponse->assertRedirect(
            route('assets.show', $asset)
        );

        $this->assertDatabaseHas('asset_assignments', [
            'asset_id' => $asset->id,
            'assigned_to_id' => $staff->id,
            'assigned_by_id' => $user->id,
            'returned_date' => null,
            'notes' => 'Assigned before scheduled disposal.',
        ]);
    }

    public function test_non_operational_asset_cannot_be_assigned(): void
    {
        $user = $this->createApplicationUser();
        $staff = $this->createStaff();

        $asset = $this->createAsset(
            'ASSET-REPAIR',
            'Under Repair'
        );

        $this->actingAs($user);

        $response = $this->post(
            route('assets.assign.store', $asset),
            [
                'assigned_to_id' => $staff->id,
                'assigned_date' => '2026-10-04',
                'notes' => null,
            ]
        );

        $response->assertSessionHasErrors('asset');

        $this->assertDatabaseMissing('asset_assignments', [
            'asset_id' => $asset->id,
        ]);
    }

    public function test_ineligible_asset_cannot_open_assignment_form_directly(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();

        $asset->update([
            'archived_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get(
            route('assets.assign.create', $asset)
        );

        $response->assertSessionHasErrors('asset');
    }

    public function test_guest_cannot_access_asset_assignment_functionality(): void
    {
        $staff = $this->createStaff();
        $asset = $this->createAsset();

        $this->get(
            route('assets.assign.create', $asset)
        )->assertRedirect(route('login'));

        $this->post(
            route('assets.assign.store', $asset),
            [
                'assigned_to_id' => $staff->id,
                'assigned_date' => '2026-10-04',
                'notes' => null,
            ]
        )->assertRedirect(route('login'));

        $this->assertDatabaseMissing('asset_assignments', [
            'asset_id' => $asset->id,
        ]);
    }
}