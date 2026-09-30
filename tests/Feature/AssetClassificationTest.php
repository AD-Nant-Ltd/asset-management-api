<?php

namespace Tests\Feature;

use App\Models\AssetCondition;
use App\Models\AssetStatus;
use App\Models\AssetSubtype;
use App\Models\AssetType;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetClassificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function createApplicationUser(
        string $roleName = 'Administrator',
        string $email = 'test@example.com'
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

    public function test_administrator_can_view_asset_classification(): void
    {
        $administrator = $this->createApplicationUser();

        $assetType = AssetType::create([
            'asset_type' => 'IT Equipment',
            'active' => true,
        ]);

        AssetSubtype::create([
            'asset_type_id' => $assetType->id,
            'asset_subtype' => 'Laptop',
            'active' => true,
        ]);

        AssetStatus::create([
            'asset_status' => 'Operational',
            'active' => true,
        ]);

        AssetCondition::create([
            'asset_condition' => 'Good',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->get(
            route('asset-classification.index')
        );

        $response->assertOk();
        $response->assertSee('Asset Classification');
        $response->assertSee('IT Equipment');
        $response->assertSee('Laptop');
        $response->assertSee('Operational');
        $response->assertSee('Good');
    }

    public function test_administrator_can_create_asset_type(): void
    {
        $administrator = $this->createApplicationUser();

        $this->actingAs($administrator);

        $response = $this->post(
            route('asset-classification.types.store'),
            [
                'asset_type' => 'Test Equipment',
            ]
        );

        $response->assertRedirect(
            route('asset-classification.index')
        );

        $this->assertDatabaseHas('asset_types', [
            'asset_type' => 'Test Equipment',
            'active' => true,
        ]);
    }

    public function test_administrator_can_update_and_deactivate_asset_type(): void
    {
        $administrator = $this->createApplicationUser();

        $assetType = AssetType::create([
            'asset_type' => 'Test Equipment',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->put(
            route(
                'asset-classification.types.update',
                $assetType
            ),
            [
                'asset_type' => 'Updated Equipment',
            ]
        );

        $response->assertRedirect(
            route('asset-classification.index')
        );

        $this->assertDatabaseHas('asset_types', [
            'id' => $assetType->id,
            'asset_type' => 'Updated Equipment',
            'active' => false,
        ]);
    }

    public function test_administrator_can_create_asset_subtype_for_existing_type(): void
    {
        $administrator = $this->createApplicationUser();

        $assetType = AssetType::create([
            'asset_type' => 'IT Equipment',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->post(
            route('asset-classification.subtypes.store'),
            [
                'asset_type_id' => $assetType->id,
                'asset_subtype' => 'Tablet',
            ]
        );

        $response->assertRedirect(
            route('asset-classification.index')
        );

        $this->assertDatabaseHas('asset_subtypes', [
            'asset_type_id' => $assetType->id,
            'asset_subtype' => 'Tablet',
            'active' => true,
        ]);
    }

    public function test_asset_subtype_cannot_reference_nonexistent_asset_type(): void
    {
        $administrator = $this->createApplicationUser();

        $this->actingAs($administrator);

        $response = $this->post(
            route('asset-classification.subtypes.store'),
            [
                'asset_type_id' => 999999,
                'asset_subtype' => 'Invalid Subtype',
            ]
        );

        $response->assertSessionHasErrors('asset_type_id');

        $this->assertDatabaseMissing('asset_subtypes', [
            'asset_subtype' => 'Invalid Subtype',
        ]);
    }

    public function test_administrator_can_update_subtype_parent_and_active_status(): void
    {
        $administrator = $this->createApplicationUser();

        $originalType = AssetType::create([
            'asset_type' => 'IT Equipment',
            'active' => true,
        ]);

        $newType = AssetType::create([
            'asset_type' => 'Mobile Equipment',
            'active' => true,
        ]);

        $subtype = AssetSubtype::create([
            'asset_type_id' => $originalType->id,
            'asset_subtype' => 'Tablet',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->put(
            route(
                'asset-classification.subtypes.update',
                $subtype
            ),
            [
                'asset_type_id' => $newType->id,
                'asset_subtype' => 'Mobile Tablet',
            ]
        );

        $response->assertRedirect(
            route('asset-classification.index')
        );

        $this->assertDatabaseHas('asset_subtypes', [
            'id' => $subtype->id,
            'asset_type_id' => $newType->id,
            'asset_subtype' => 'Mobile Tablet',
            'active' => false,
        ]);
    }

    public function test_administrator_can_create_asset_status(): void
    {
        $administrator = $this->createApplicationUser();

        $this->actingAs($administrator);

        $response = $this->post(
            route('asset-classification.statuses.store'),
            [
                'asset_status' => 'Awaiting Inspection',
            ]
        );

        $response->assertRedirect(
            route('asset-classification.index')
        );

        $this->assertDatabaseHas('asset_statuses', [
            'asset_status' => 'Awaiting Inspection',
            'active' => true,
        ]);
    }

    public function test_administrator_can_update_and_deactivate_asset_status(): void
    {
        $administrator = $this->createApplicationUser();

        $assetStatus = AssetStatus::create([
            'asset_status' => 'Test Status',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->put(
            route(
                'asset-classification.statuses.update',
                $assetStatus
            ),
            [
                'asset_status' => 'Updated Status',
            ]
        );

        $response->assertRedirect(
            route('asset-classification.index')
        );

        $this->assertDatabaseHas('asset_statuses', [
            'id' => $assetStatus->id,
            'asset_status' => 'Updated Status',
            'active' => false,
        ]);
    }

    public function test_administrator_can_create_asset_condition(): void
    {
        $administrator = $this->createApplicationUser();

        $this->actingAs($administrator);

        $response = $this->post(
            route('asset-classification.conditions.store'),
            [
                'asset_condition' => 'Refurbished',
            ]
        );

        $response->assertRedirect(
            route('asset-classification.index')
        );

        $this->assertDatabaseHas('asset_conditions', [
            'asset_condition' => 'Refurbished',
            'active' => true,
        ]);
    }

    public function test_administrator_can_update_and_deactivate_asset_condition(): void
    {
        $administrator = $this->createApplicationUser();

        $assetCondition = AssetCondition::create([
            'asset_condition' => 'Test Condition',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $response = $this->put(
            route(
                'asset-classification.conditions.update',
                $assetCondition
            ),
            [
                'asset_condition' => 'Updated Condition',
            ]
        );

        $response->assertRedirect(
            route('asset-classification.index')
        );

        $this->assertDatabaseHas('asset_conditions', [
            'id' => $assetCondition->id,
            'asset_condition' => 'Updated Condition',
            'active' => false,
        ]);
    }

    public function test_duplicate_classification_values_are_rejected(): void
    {
        $administrator = $this->createApplicationUser();

        AssetType::create([
            'asset_type' => 'IT Equipment',
            'active' => true,
        ]);

        AssetStatus::create([
            'asset_status' => 'Operational',
            'active' => true,
        ]);

        AssetCondition::create([
            'asset_condition' => 'Good',
            'active' => true,
        ]);

        $this->actingAs($administrator);

        $this->post(
            route('asset-classification.types.store'),
            [
                'asset_type' => 'IT Equipment',
            ]
        )->assertSessionHasErrors('asset_type');

        $this->post(
            route('asset-classification.statuses.store'),
            [
                'asset_status' => 'Operational',
            ]
        )->assertSessionHasErrors('asset_status');

        $this->post(
            route('asset-classification.conditions.store'),
            [
                'asset_condition' => 'Good',
            ]
        )->assertSessionHasErrors('asset_condition');

        $this->assertSame(
            1,
            AssetType::where(
                'asset_type',
                'IT Equipment'
            )->count()
        );

        $this->assertSame(
            1,
            AssetStatus::where(
                'asset_status',
                'Operational'
            )->count()
        );

        $this->assertSame(
            1,
            AssetCondition::where(
                'asset_condition',
                'Good'
            )->count()
        );
    }

    public function test_authorised_user_cannot_access_asset_classification(): void
    {
        $user = $this->createApplicationUser(
            'Authorised User',
            'authorised@example.com'
        );

        $this->actingAs($user);

        $this->get(
            route('asset-classification.index')
        )->assertForbidden();

        $this->post(
            route('asset-classification.types.store'),
            [
                'asset_type' => 'Unauthorised Type',
            ]
        )->assertForbidden();

        $this->assertDatabaseMissing('asset_types', [
            'asset_type' => 'Unauthorised Type',
        ]);
    }

    public function test_guest_cannot_access_asset_classification(): void
    {
        $this->get(
            route('asset-classification.index')
        )->assertRedirect(route('login'));

        $this->post(
            route('asset-classification.types.store'),
            [
                'asset_type' => 'Guest Type',
            ]
        )->assertRedirect(route('login'));

        $this->assertDatabaseMissing('asset_types', [
            'asset_type' => 'Guest Type',
        ]);
    }
}