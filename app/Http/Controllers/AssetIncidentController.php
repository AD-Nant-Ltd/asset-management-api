<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetIncident;
use App\Models\IncidentStatus;
use App\Models\IncidentType;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetIncidentController extends Controller
{
    public function create(Asset $asset)
    {
        $asset->load([
            'assetSubtype.assetType',
            'assetStatus',
            'assetCondition',
            'activeAssignment.assignedTo',
        ]);

        $incidentTypes = IncidentType::where('active', true)
            ->orderBy('incident_type')
            ->get();

        $staffMembers = Staff::orderBy('forename')
            ->orderBy('surname')
            ->get();

        $applicationUsers = User::with('staff')
            ->where('active', true)
            ->whereHas('staff', function ($query) {
                $query->where('active', true);
            })
            ->get()
            ->sortBy(function ($user) {
                return strtolower(
                    $user->staff->forename . ' ' .
                    $user->staff->surname
                );
            })
            ->values();

        return view('asset-incidents.create', compact(
            'asset',
            'incidentTypes',
            'staffMembers',
            'applicationUsers'
        ));
    }

    public function store(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'incident_type_id' => [
                'required',
                'integer',
                Rule::exists('incident_types', 'id')
                    ->where('active', true),
            ],
            'affected_staff_id' => [
                'required',
                'integer',
                'exists:staff,id',
            ],
            'assigned_to_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
                    ->where('active', true),
            ],
            'incident_date' => [
                'required',
                'date',
            ],
            'description' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        $openStatus = IncidentStatus::where(
            'incident_status',
            'Open'
        )
            ->where('active', true)
            ->firstOrFail();

        AssetIncident::create([
            'asset_id' => $asset->id,
            'affected_staff_id' => $validated['affected_staff_id'],
            'assigned_to_user_id' =>
                $validated['assigned_to_user_id'] ?? null,
            'incident_type_id' => $validated['incident_type_id'],
            'incident_status_id' => $openStatus->id,
            'incident_date' => $validated['incident_date'],
            'description' => $validated['description'],
            'action_taken' => null,
            'warranty_claim_ref' => null,
            'warranty_claim_date' => null,
            'warranty_excess' => null,
            'associated_cost' => null,
            'resolved_date' => null,
        ]);

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Incident recorded successfully.')
            ->with('review_asset_state', true);
    }

    public function edit(
        Asset $asset,
        AssetIncident $incident
    ) {
        abort_unless(
            $incident->asset_id === $asset->id,
            404
        );

        $incident->load([
            'incidentType',
            'incidentStatus',
            'affectedStaff',
            'assignedToUser.staff',
        ]);

        $asset->load([
            'assetSubtype.assetType',
            'assetStatus',
            'assetCondition',
        ]);

        $incidentStatuses = IncidentStatus::where('active', true)
            ->orWhere('id', $incident->incident_status_id)
            ->orderBy('incident_status')
            ->get();

        $applicationUsers = User::with('staff')
            ->where('active', true)
            ->whereHas('staff', function ($query) {
                $query->where('active', true);
            })
            ->get()
            ->sortBy(function ($user) {
                return strtolower(
                    $user->staff->forename . ' ' .
                    $user->staff->surname
                );
            })
            ->values();

        return view(
            'asset-incidents.edit',
            compact(
                'asset',
                'incident',
                'incidentStatuses',
                'applicationUsers'
            )
        );
    }

    public function update(
        Request $request,
        Asset $asset,
        AssetIncident $incident
    ) {
        abort_unless(
            $incident->asset_id === $asset->id,
            404
        );

        $resolvedStatusId = IncidentStatus::where(
            'incident_status',
            'Resolved'
        )
            ->where('active', true)
            ->value('id');

        $validated = $request->validate([
            'incident_status_id' => [
                'required',
                'integer',
                Rule::exists('incident_statuses', 'id')
                    ->where('active', true),
            ],
            'assigned_to_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
                    ->where('active', true),
            ],
            'action_taken' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'warranty_claim_ref' => [
                'nullable',
                'string',
                'max:100',
            ],
            'warranty_claim_date' => [
                'nullable',
                'date',
            ],
            'warranty_excess' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],
            'associated_cost' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],
            'resolved_date' => [
                Rule::requiredIf(
                    (int) $request->input('incident_status_id')
                    === (int) $resolvedStatusId
                ),
                'nullable',
                'date',
                'after_or_equal:'
                    . $incident->incident_date->format('Y-m-d'),
            ],
        ]);

        $isResolved =
            (int) $validated['incident_status_id']
            === (int) $resolvedStatusId;

        $incident->update([
            'incident_status_id' =>
                $validated['incident_status_id'],

            'assigned_to_user_id' =>
                $validated['assigned_to_user_id'] ?? null,

            'action_taken' =>
                $validated['action_taken'] ?? null,

            'warranty_claim_ref' =>
                $validated['warranty_claim_ref'] ?? null,

            'warranty_claim_date' =>
                $validated['warranty_claim_date'] ?? null,

            'warranty_excess' =>
                $validated['warranty_excess'] ?? null,

            'associated_cost' =>
                $validated['associated_cost'] ?? null,

            'resolved_date' => $isResolved
                ? $validated['resolved_date']
                : null,
        ]);

        return redirect()
            ->route('assets.show', $asset)
            ->with(
                'success',
                'Incident updated successfully.'
            );
    }
}