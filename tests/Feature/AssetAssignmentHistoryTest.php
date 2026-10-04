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

class AssetAssignmentHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function createApplicationUser(
        string $email = 'user@example.com'
    ): User {
        $role = Role::firstOrCreate([
            'role_name' => 'Authorised User',
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
        string $forename,
        string $surname
    ): Staff {
        return Staff::create([
            'forename' => $forename,
            'surname' => $surname,
            'regul8_staff_id' => null,
            'active' => true,
        ]);
    }

    private function createAsset(
        string $assetTag = 'ASSET-HISTORY-001'
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

    private function createAssignment(
        Asset $asset,
        Staff $staff,
        User $processedBy,
        string $assignedDate,
        ?string $returnedDate,
        string $notes
    ): AssetAssignment {
        return AssetAssignment::create([
            'asset_id' => $asset->id,
            'assigned_to_id' => $staff->id,
            'assigned_by_id' => $processedBy->id,
            'assigned_date' => $assignedDate,
            'returned_date' => $returnedDate,
            'returned_by_id' => $returnedDate !== null
                ? $processedBy->id
                : null,
            'notes' => $notes,
        ]);
    }

    public function test_authorised_user_can_view_complete_assignment_history(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();

        $firstHolder = $this->createStaff(
            'Jane',
            'Doe'
        );

        $secondHolder = $this->createStaff(
            'John',
            'Smith'
        );

        $this->createAssignment(
            $asset,
            $firstHolder,
            $user,
            '2026-09-20',
            '2026-09-25',
            'First assignment'
        );

        $this->createAssignment(
            $asset,
            $secondHolder,
            $user,
            '2026-10-01',
            '2026-10-03',
            'Second assignment'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSee('Assignment History');

        $response->assertSee('Jane');
        $response->assertSee('Doe');

        $response->assertSee('John');
        $response->assertSee('Smith');

        $response->assertSee('20/09/2026');
        $response->assertSee('25/09/2026');

        $response->assertSee('01/10/2026');
        $response->assertSee('03/10/2026');

        $response->assertSee('First assignment');
        $response->assertSee('Second assignment');
    }

    public function test_assignment_history_is_displayed_newest_first(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();

        $firstHolder = $this->createStaff(
            'Jane',
            'Doe'
        );

        $secondHolder = $this->createStaff(
            'John',
            'Smith'
        );

        $this->createAssignment(
            $asset,
            $firstHolder,
            $user,
            '2026-09-20',
            '2026-09-25',
            'Older assignment'
        );

        $this->createAssignment(
            $asset,
            $secondHolder,
            $user,
            '2026-10-01',
            '2026-10-03',
            'Newer assignment'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSeeInOrder([
            'Newer assignment',
            'Older assignment',
        ]);
    }

    public function test_same_day_assignments_use_newest_record_as_tie_breaker(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();

        $firstHolder = $this->createStaff(
            'Jane',
            'Doe'
        );

        $secondHolder = $this->createStaff(
            'John',
            'Smith'
        );

        $this->createAssignment(
            $asset,
            $firstHolder,
            $user,
            '2026-10-04',
            '2026-10-04',
            'First same-day assignment'
        );

        $this->createAssignment(
            $asset,
            $secondHolder,
            $user,
            '2026-10-04',
            null,
            'Second same-day assignment'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSeeInOrder([
            'Second same-day assignment',
            'First same-day assignment',
        ]);
    }

    public function test_returned_assignment_remains_visible_in_history(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();

        $holder = $this->createStaff(
            'Jane',
            'Doe'
        );

        $assignment = $this->createAssignment(
            $asset,
            $holder,
            $user,
            '2026-10-01',
            '2026-10-03',
            'Returned assignment'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSee('Jane');
        $response->assertSee('Doe');
        $response->assertSee('01/10/2026');
        $response->assertSee('03/10/2026');
        $response->assertSee('Returned assignment');

        $this->assertDatabaseHas('asset_assignments', [
            'id' => $assignment->id,
            'asset_id' => $asset->id,
            'assigned_to_id' => $holder->id,
        ]);
    }

    public function test_current_assignment_is_identified_in_assignment_history(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();

        $holder = $this->createStaff(
            'John',
            'Smith'
        );

        $this->createAssignment(
            $asset,
            $holder,
            $user,
            '2026-10-04',
            null,
            'Current assignment'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSee('John');
        $response->assertSee('Smith');
        $response->assertSee('04/10/2026');
        $response->assertSee('Currently assigned');
        $response->assertSee('Current assignment');
    }

    public function test_reassignment_preserves_previous_assignment_history(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();

        $firstHolder = $this->createStaff(
            'Jane',
            'Doe'
        );

        $secondHolder = $this->createStaff(
            'John',
            'Smith'
        );

        $firstAssignment = $this->createAssignment(
            $asset,
            $firstHolder,
            $user,
            '2026-10-01',
            '2026-10-03',
            'Previous holder'
        );

        $secondAssignment = $this->createAssignment(
            $asset,
            $secondHolder,
            $user,
            '2026-10-04',
            null,
            'Current holder'
        );

        $this->assertSame(
            2,
            AssetAssignment::where(
                'asset_id',
                $asset->id
            )->count()
        );

        $this->assertDatabaseHas('asset_assignments', [
            'id' => $firstAssignment->id,
            'asset_id' => $asset->id,
            'assigned_to_id' => $firstHolder->id,
        ]);

        $this->assertDatabaseHas('asset_assignments', [
            'id' => $secondAssignment->id,
            'asset_id' => $asset->id,
            'assigned_to_id' => $secondHolder->id,
            'returned_date' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertOk();

        $response->assertSee('Previous holder');
        $response->assertSee('Current holder');

        $response->assertSeeInOrder([
            'Current holder',
            'Previous holder',
        ]);
    }

    public function test_guest_cannot_view_asset_assignment_history(): void
    {
        $user = $this->createApplicationUser();
        $asset = $this->createAsset();

        $holder = $this->createStaff(
            'Jane',
            'Doe'
        );

        $this->createAssignment(
            $asset,
            $holder,
            $user,
            '2026-10-01',
            '2026-10-03',
            'Protected assignment history'
        );

        $response = $this->get(
            route('assets.show', $asset)
        );

        $response->assertRedirect(
            route('login')
        );
    }
}