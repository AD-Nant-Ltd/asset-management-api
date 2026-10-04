<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
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

    public function returnAsset(
        Asset $asset,
        User $returnedBy,
        string $returnedDate
    ): AssetAssignment {
        return DB::transaction(function () use (
            $asset,
            $returnedBy,
            $returnedDate
        ) {
            $lockedAsset = Asset::lockForUpdate()
                ->findOrFail($asset->id);

            $assignment = $this->ensureAssetCanBeReturned(
                $lockedAsset
            );

            $returnDate = Carbon::parse($returnedDate);

            if ($returnDate->lt($assignment->assigned_date)) {
                throw ValidationException::withMessages([
                    'returned_date' => 'The return date cannot be before the assignment date.',
                ]);
            }

            $assignment->update([
                'returned_date' => $returnedDate,
                'returned_by_id' => $returnedBy->id,
            ]);

            return $assignment->fresh();
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

    public function ensureAssetCanBeReturned(
        Asset $asset
    ): AssetAssignment {
        $assignment = AssetAssignment::where(
            'asset_id',
            $asset->id
        )
            ->whereNull('returned_date')
            ->first();

        if ($assignment === null) {
            throw ValidationException::withMessages([
                'asset' => 'This asset does not have an active assignment to return.',
            ]);
        }

        return $assignment;
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