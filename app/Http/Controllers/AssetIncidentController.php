<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetIncident;
use App\Models\IncidentStatus;
use App\Models\IncidentType;
use App\Models\Staff;
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

        return view('asset-incidents.create', compact(
            'asset',
            'incidentTypes',
            'staffMembers'
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
            'assigned_to_user_id' => null,
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
}