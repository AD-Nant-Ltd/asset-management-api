<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetAssignmentService
{
    public function assign(
        Asset $asset,
        Staff $staff,
        User $assignedBy,
        string $assignedDate,
        ?string $notes = null
    ): AssetAssignment {
        return DB::transaction(function () use (
            $asset,
            $staff,
            $assignedBy,
            $assignedDate,
            $notes
        ) {
            $lockedAsset = Asset::with('assetStatus')
                ->lockForUpdate()
                ->findOrFail($asset->id);

            $this->ensureAssetCanBeAssigned($lockedAsset);
            $this->ensureStaffCanReceiveAsset($staff);

            return AssetAssignment::create([
                'asset_id' => $lockedAsset->id,
                'assigned_to_id' => $staff->id,
                'assigned_by_id' => $assignedBy->id,
                'assigned_date' => $assignedDate,
                'returned_date' => null,
                'returned_by_id' => null,
                'notes' => $notes,
            ]);
        });
    }

    public function ensureAssetCanBeAssigned(Asset $asset): void
    {
        $asset->loadMissing('assetStatus');

        if ($asset->archived_at !== null) {
            throw ValidationException::withMessages([
                'asset' => 'Archived assets cannot be assigned.',
            ]);
        }

        if (
            $asset->retired_date !== null &&
            $asset->retired_date->lte(today())
        ) {
            throw ValidationException::withMessages([
                'asset' => 'Retired assets cannot be assigned.',
            ]);
        }

        if (
            $asset->disposal_date !== null &&
            $asset->disposal_date->lte(today())
        ) {
            throw ValidationException::withMessages([
                'asset' => 'Disposed assets cannot be assigned.',
            ]);
        }

        if (
            $asset->assetStatus === null ||
            $asset->assetStatus->asset_status !== 'Operational'
        ) {
            throw ValidationException::withMessages([
                'asset' => 'Only operational assets can be assigned.',
            ]);
        }

        $hasActiveAssignment = AssetAssignment::where(
            'asset_id',
            $asset->id
        )
            ->whereNull('returned_date')
            ->exists();

        if ($hasActiveAssignment) {
            throw ValidationException::withMessages([
                'asset' => 'This asset is already assigned to a staff member.',
            ]);
        }
    }

    private function ensureStaffCanReceiveAsset(Staff $staff): void
    {
        if (! $staff->active) {
            throw ValidationException::withMessages([
                'assigned_to_id' => 'Assets can only be assigned to active staff members.',
            ]);
        }
    }
}