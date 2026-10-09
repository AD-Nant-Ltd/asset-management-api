<?php

namespace App\Http\Controllers;

use App\Models\IncidentStatus;
use App\Models\IncidentType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IncidentConfigurationController extends Controller
{
    public function index()
    {
        $incidentTypes = IncidentType::orderBy('incident_type')->get();

        $incidentStatuses = IncidentStatus::orderBy('incident_status')->get();

        return view('incident-configuration.index', compact(
            'incidentTypes',
            'incidentStatuses'
        ));
    }

    public function storeIncidentType(Request $request)
    {
        $validated = $request->validate([
            'incident_type' => [
                'required',
                'string',
                'max:100',
                'unique:incident_types,incident_type',
            ],
        ]);

        IncidentType::create([
            'incident_type' => $validated['incident_type'],
            'active' => true,
        ]);

        return redirect(
            route('incident-configuration.index') . '#incident-types'
        )->with('success', 'Incident type created successfully.');
    }

    public function updateIncidentType(
        Request $request,
        IncidentType $incidentType
    ) {
        $validated = $request->validate([
            'incident_type' => [
                'required',
                'string',
                'max:100',
                Rule::unique('incident_types', 'incident_type')
                    ->ignore($incidentType->id),
            ],
            'active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $incidentType->update([
            'incident_type' => $validated['incident_type'],
            'active' => $request->boolean('active'),
        ]);

        return redirect(
            route('incident-configuration.index') . '#incident-types'
        )->with('success', 'Incident type updated successfully.');
    }

    public function storeIncidentStatus(Request $request)
    {
        $validated = $request->validate([
            'incident_status' => [
                'required',
                'string',
                'max:100',
                'unique:incident_statuses,incident_status',
            ],
        ]);

        IncidentStatus::create([
            'incident_status' => $validated['incident_status'],
            'active' => true,
        ]);

        return redirect(
            route('incident-configuration.index') . '#incident-statuses'
        )->with('success', 'Incident status created successfully.');
    }

    public function updateIncidentStatus(
        Request $request,
        IncidentStatus $incidentStatus
    ) {
        $validated = $request->validate([
            'incident_status' => [
                'required',
                'string',
                'max:100',
                Rule::unique('incident_statuses', 'incident_status')
                    ->ignore($incidentStatus->id),
            ],
            'active' => [
                'nullable',
                'boolean',
            ],
        ]);

        if ($this->isProtectedIncidentStatus($incidentStatus)) {
            if (
                $validated['incident_status'] !== $incidentStatus->incident_status
                || ! $request->boolean('active')
            ) {
                throw ValidationException::withMessages([
                    'incident_status' =>
                        'Open and Resolved are system statuses and cannot be renamed or deactivated.',
                ]);
            }
        }

        $incidentStatus->update([
            'incident_status' => $validated['incident_status'],
            'active' => $request->boolean('active'),
        ]);

        return redirect(
            route('incident-configuration.index') . '#incident-statuses'
        )->with('success', 'Incident status updated successfully.');
    }

    private function isProtectedIncidentStatus(
        IncidentStatus $incidentStatus
    ): bool {
        return in_array(
            $incidentStatus->incident_status,
            ['Open', 'Resolved'],
            true
        );
    }
}