<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Staff;
use App\Services\AssetAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetAssignmentController extends Controller
{
    public function create(
        Asset $asset,
        AssetAssignmentService $assignmentService
    ) {
        $asset->load([
            'assetSubtype.assetType',
            'assetStatus',
            'assetCondition',
            'activeAssignment.assignedTo',
        ]);

        $assignmentService->ensureAssetCanBeAssigned($asset);

        $staffMembers = Staff::where('active', true)
            ->orderBy('forename')
            ->orderBy('surname')
            ->get();

        return view('asset-assignments.create', compact(
            'asset',
            'staffMembers'
        ));
    }

    public function store(
        Request $request,
        Asset $asset,
        AssetAssignmentService $assignmentService
    ) {
        $validated = $request->validate([
            'assigned_to_id' => [
                'required',
                'integer',
                Rule::exists('staff', 'id')
                    ->where('active', true),
            ],
            'assigned_date' => [
                'required',
                'date',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $staff = Staff::findOrFail(
            $validated['assigned_to_id']
        );

        $assignmentService->assign(
            asset: $asset,
            staff: $staff,
            assignedBy: $request->user(),
            assignedDate: $validated['assigned_date'],
            notes: $validated['notes'] ?? null
        );

        return redirect()
            ->route('assets.show', $asset)
            ->with(
                'success',
                'Asset assigned successfully.'
            );
    }

    public function createReturn(
        Asset $asset,
        AssetAssignmentService $assignmentService
    ) {
        $asset->load([
            'assetSubtype.assetType',
            'assetStatus',
            'assetCondition',
            'activeAssignment.assignedTo',
            'activeAssignment.assignedBy.staff',
        ]);

        $assignment = $assignmentService
            ->ensureAssetCanBeReturned($asset);

        $assignment->load([
            'assignedTo',
            'assignedBy.staff',
        ]);

        return view(
            'asset-assignments.return',
            compact(
                'asset',
                'assignment'
            )
        );
    }

    public function storeReturn(
        Request $request,
        Asset $asset,
        AssetAssignmentService $assignmentService
    ) {
        $validated = $request->validate([
            'returned_date' => [
                'required',
                'date',
            ],
        ]);

        $assignmentService->returnAsset(
            asset: $asset,
            returnedBy: $request->user(),
            returnedDate: $validated['returned_date']
        );

        return redirect()
            ->route('assets.show', $asset)
            ->with(
                'success',
                'Asset returned successfully.'
            );
    }
}