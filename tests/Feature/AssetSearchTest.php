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

class AssetSearchTest extends TestCase
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
        string $assetTag,
        string $serialNumber,
        string $statusName = 'Operational',
        string $typeName = 'IT Equipment',
        string $subtypeName = 'Laptop',
        array $overrides = []
    ): Asset {
        $assetType = AssetType::firstOrCreate(
            [
                'asset_type' => $typeName,
            ],
            [
                'active' => true,
            ]
        );

        $assetSubtype = AssetSubtype::firstOrCreate(
            [
                'asset_type_id' => $assetType->id,
                'asset_subtype' => $subtypeName,
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

        return Asset::create(array_merge([
            'asset_subtype_id' => $assetSubtype->id,
            'asset_status_id' => $assetStatus->id,
            'asset_condition_id' => $assetCondition->id,
            'asset_tag' => $assetTag,
            'serial_num' => $serialNumber,
            'purchase_date' => '2026-09-01',
            'warranty_expiry' => '2029-09-01',
        ], $overrides));
    }

    private function assignAsset(
        Asset $asset,
        Staff $staff,
        User $assignedBy
    ): AssetAssignment {
        return AssetAssignment::create([
            'asset_id' => $asset->id,
            'assigned_to_id' => $staff->id,
            'assigned_by_id' => $assignedBy->id,
            'assigned_date' => '2026-10-01',
            'returned_date' => null,
            'returned_by_id' => null,
            'notes' => 'Test assignment.',
        ]);
    }

    public function test_authorised_user_can_view_asset_availability_and_current_holder(): void
    {
        $user = $this->createApplicationUser();
        $holder = $this->createStaff('Jane', 'Doe');

        $inStockAsset = $this->createAsset(
            'STOCK-001',
            'SERIAL-STOCK-001'
        );

        $assignedAsset = $this->createAsset(
            'ASSIGNED-001',
            'SERIAL-ASSIGNED-001'
        );

        $unavailableAsset = $this->createAsset(
            'UNAVAILABLE-001',
            'SERIAL-UNAVAILABLE-001',
            'Under Repair'
        );

        $this->assignAsset(
            $assignedAsset,
            $holder,
            $user
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.index')
        );

        $response->assertOk();

        $response->assertSee($inStockAsset->asset_tag);
        $response->assertSee($assignedAsset->asset_tag);
        $response->assertSee($unavailableAsset->asset_tag);

        $response->assertSee('In Stock');
        $response->assertSee('Assigned');
        $response->assertSee('Unavailable');

        $response->assertSeeInOrder([
            'Jane',
            'Doe',
        ]);
    }

    public function test_assets_can_be_searched_by_asset_tag(): void
    {
        $user = $this->createApplicationUser();

        $matchingAsset = $this->createAsset(
            'LAPTOP-ABC-123',
            'SERIAL-001'
        );

        $otherAsset = $this->createAsset(
            'TABLET-XYZ-999',
            'SERIAL-002'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.index', [
                'search' => 'ABC-123',
            ])
        );

        $response->assertOk();
        $response->assertSee($matchingAsset->asset_tag);
        $response->assertDontSee($otherAsset->asset_tag);
    }

    public function test_assets_can_be_searched_by_serial_number(): void
    {
        $user = $this->createApplicationUser();

        $matchingAsset = $this->createAsset(
            'ASSET-001',
            'SERIAL-SPECIAL-456'
        );

        $otherAsset = $this->createAsset(
            'ASSET-002',
            'SERIAL-OTHER-789'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.index', [
                'search' => 'SPECIAL-456',
            ])
        );

        $response->assertOk();
        $response->assertSee($matchingAsset->asset_tag);
        $response->assertDontSee($otherAsset->asset_tag);
    }

    public function test_assets_can_be_searched_by_type_and_subtype(): void
    {
        $user = $this->createApplicationUser();

        $surfaceAsset = $this->createAsset(
            'SURFACE-001',
            'SERIAL-SURFACE',
            'Operational',
            'IT Equipment',
            'Surface Pro 9'
        );

        $phoneAsset = $this->createAsset(
            'PHONE-001',
            'SERIAL-PHONE',
            'Operational',
            'Mobile Device',
            'Smartphone'
        );

        $this->actingAs($user);

        $subtypeResponse = $this->get(
            route('assets.index', [
                'search' => 'Surface Pro',
            ])
        );

        $subtypeResponse->assertOk();
        $subtypeResponse->assertSee($surfaceAsset->asset_tag);
        $subtypeResponse->assertDontSee($phoneAsset->asset_tag);

        $typeResponse = $this->get(
            route('assets.index', [
                'search' => 'Mobile Device',
            ])
        );

        $typeResponse->assertOk();
        $typeResponse->assertSee($phoneAsset->asset_tag);
        $typeResponse->assertDontSee($surfaceAsset->asset_tag);
    }

    public function test_assets_can_be_filtered_to_in_stock(): void
    {
        $user = $this->createApplicationUser();
        $holder = $this->createStaff();

        $inStockAsset = $this->createAsset(
            'STOCK-001',
            'SERIAL-STOCK'
        );

        $assignedAsset = $this->createAsset(
            'ASSIGNED-001',
            'SERIAL-ASSIGNED'
        );

        $unavailableAsset = $this->createAsset(
            'REPAIR-001',
            'SERIAL-REPAIR',
            'Under Repair'
        );

        $this->assignAsset(
            $assignedAsset,
            $holder,
            $user
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.index', [
                'availability' => 'in_stock',
            ])
        );

        $response->assertOk();
        $response->assertSee($inStockAsset->asset_tag);
        $response->assertDontSee($assignedAsset->asset_tag);
        $response->assertDontSee($unavailableAsset->asset_tag);
    }

    public function test_assets_can_be_filtered_to_assigned(): void
    {
        $user = $this->createApplicationUser();
        $holder = $this->createStaff();

        $assignedAsset = $this->createAsset(
            'ASSIGNED-001',
            'SERIAL-ASSIGNED'
        );

        $inStockAsset = $this->createAsset(
            'STOCK-001',
            'SERIAL-STOCK'
        );

        $this->assignAsset(
            $assignedAsset,
            $holder,
            $user
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.index', [
                'availability' => 'assigned',
            ])
        );

        $response->assertOk();
        $response->assertSee($assignedAsset->asset_tag);
        $response->assertDontSee($inStockAsset->asset_tag);
    }

    public function test_assets_can_be_filtered_to_unavailable(): void
    {
        $user = $this->createApplicationUser();

        $underRepairAsset = $this->createAsset(
            'REPAIR-001',
            'SERIAL-REPAIR',
            'Under Repair'
        );

        $archivedAsset = $this->createAsset(
            'ARCHIVED-001',
            'SERIAL-ARCHIVED',
            'Operational',
            'IT Equipment',
            'Laptop',
            [
                'archived_at' => now(),
            ]
        );

        $retiredAsset = $this->createAsset(
            'RETIRED-001',
            'SERIAL-RETIRED',
            'Operational',
            'IT Equipment',
            'Laptop',
            [
                'retired_date' => today()->toDateString(),
            ]
        );

        $inStockAsset = $this->createAsset(
            'STOCK-001',
            'SERIAL-STOCK'
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.index', [
                'availability' => 'unavailable',
            ])
        );

        $response->assertOk();

        $response->assertSee($underRepairAsset->asset_tag);
        $response->assertSee($archivedAsset->asset_tag);
        $response->assertSee($retiredAsset->asset_tag);

        $response->assertDontSee($inStockAsset->asset_tag);
    }

    public function test_future_retirement_or_disposal_dates_remain_in_stock(): void
    {
        $user = $this->createApplicationUser();

        $futureRetirementAsset = $this->createAsset(
            'FUTURE-RETIRE-001',
            'SERIAL-FUTURE-RETIRE',
            'Operational',
            'IT Equipment',
            'Laptop',
            [
                'retired_date' => today()
                    ->addDays(30)
                    ->toDateString(),
            ]
        );

        $futureDisposalAsset = $this->createAsset(
            'FUTURE-DISPOSE-001',
            'SERIAL-FUTURE-DISPOSE',
            'Operational',
            'IT Equipment',
            'Laptop',
            [
                'disposal_date' => today()
                    ->addDays(30)
                    ->toDateString(),
            ]
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.index', [
                'availability' => 'in_stock',
            ])
        );

        $response->assertOk();
        $response->assertSee($futureRetirementAsset->asset_tag);
        $response->assertSee($futureDisposalAsset->asset_tag);
    }

    public function test_search_and_availability_filter_can_be_combined(): void
    {
        $user = $this->createApplicationUser();
        $holder = $this->createStaff();

        $assignedLaptop = $this->createAsset(
            'SEARCH-LAPTOP-001',
            'SERIAL-001'
        );

        $inStockLaptop = $this->createAsset(
            'SEARCH-LAPTOP-002',
            'SERIAL-002'
        );

        $otherAssignedAsset = $this->createAsset(
            'OTHER-ASSET-001',
            'SERIAL-003'
        );

        $this->assignAsset(
            $assignedLaptop,
            $holder,
            $user
        );

        $this->assignAsset(
            $otherAssignedAsset,
            $holder,
            $user
        );

        $this->actingAs($user);

        $response = $this->get(
            route('assets.index', [
                'search' => 'SEARCH-LAPTOP',
                'availability' => 'assigned',
            ])
        );

        $response->assertOk();

        $response->assertSee($assignedLaptop->asset_tag);
        $response->assertDontSee($inStockLaptop->asset_tag);
        $response->assertDontSee($otherAssignedAsset->asset_tag);
    }
}