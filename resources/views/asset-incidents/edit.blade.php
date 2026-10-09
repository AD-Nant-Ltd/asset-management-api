@extends('layouts.app')

@section('title', 'Manage Incident')

@section('content')
<nav class="navbar navbar-expand-lg bg-dark navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            Asset Management
        </a>

        <div class="d-flex align-items-center gap-3">
            <span class="text-light">
                {{ auth()->user()->staff->forename }}
                {{ auth()->user()->staff->surname }}
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="btn btn-outline-light btn-sm"
                >
                    Log out
                </button>
            </form>
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1">
                        Manage Incident #{{ $incident->id }}
                    </h1>

                    <p class="text-muted mb-0">
                        Asset {{ $asset->asset_tag }}
                    </p>
                </div>

                <a
                    href="{{ route('assets.show', $asset) }}"
                    class="btn btn-outline-secondary"
                >
                    Back to Asset
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>
                        The incident could not be updated.
                    </strong>

                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">
                        Incident Details
                    </h2>
                </div>

                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">
                            Asset
                        </dt>

                        <dd class="col-sm-8">
                            {{ $asset->asset_tag }}
                            —
                            {{ $asset->assetSubtype->assetType->asset_type }}
                            /
                            {{ $asset->assetSubtype->asset_subtype }}
                        </dd>

                        <dt class="col-sm-4">
                            Incident Type
                        </dt>

                        <dd class="col-sm-8">
                            {{ $incident->incidentType->incident_type }}
                        </dd>

                        <dt class="col-sm-4">
                            Incident Date
                        </dt>

                        <dd class="col-sm-8">
                            {{ $incident->incident_date->format('d/m/Y') }}
                        </dd>

                        <dt class="col-sm-4">
                            Associated Staff
                        </dt>

                        <dd class="col-sm-8">
                            {{ $incident->affectedStaff->forename }}
                            {{ $incident->affectedStaff->surname }}
                        </dd>

                        <dt class="col-sm-4">
                            Description
                        </dt>

                        <dd class="col-sm-8">
                            {{ $incident->description }}
                        </dd>
                    </dl>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'assets.incidents.update',
                    [$asset, $incident]
                ) }}"
            >
                @csrf
                @method('PUT')

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-0">
                            Incident Management
                        </h2>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label
                                    for="incident_status_id"
                                    class="form-label"
                                >
                                    Status
                                </label>

                                <select
                                    id="incident_status_id"
                                    name="incident_status_id"
                                    class="form-select"
                                    required
                                >
                                    @foreach ($incidentStatuses as $incidentStatus)
                                        <option
                                            value="{{ $incidentStatus->id }}"
                                            {{
                                                old(
                                                    'incident_status_id',
                                                    $incident->incident_status_id
                                                ) == $incidentStatus->id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            {{ $incidentStatus->incident_status }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="assigned_to_user_id"
                                    class="form-label"
                                >
                                    Assigned To
                                </label>

                                <select
                                    id="assigned_to_user_id"
                                    name="assigned_to_user_id"
                                    class="form-select"
                                >
                                    <option value="">
                                        Unassigned
                                    </option>

                                    @foreach ($applicationUsers as $applicationUser)
                                        <option
                                            value="{{ $applicationUser->id }}"
                                            {{
                                                old(
                                                    'assigned_to_user_id',
                                                    $incident->assigned_to_user_id
                                                ) == $applicationUser->id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            {{ $applicationUser->staff->forename }}
                                            {{ $applicationUser->staff->surname }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="form-text">
                                    Assign the incident to an active application user
                                    responsible for managing it.
                                </div>
                            </div>

                            <div class="col-12">
                                <label
                                    for="action_taken"
                                    class="form-label"
                                >
                                    Action Taken
                                </label>

                                <textarea
                                    id="action_taken"
                                    name="action_taken"
                                    rows="5"
                                    maxlength="5000"
                                    class="form-control"
                                >{{ old(
                                    'action_taken',
                                    $incident->action_taken
                                ) }}</textarea>

                                <div class="form-text">
                                    Record actions taken in response to the incident.
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-0">
                            Warranty and Cost
                        </h2>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label
                                    for="warranty_claim_ref"
                                    class="form-label"
                                >
                                    Warranty Claim Reference
                                </label>

                                <input
                                    type="text"
                                    id="warranty_claim_ref"
                                    name="warranty_claim_ref"
                                    maxlength="100"
                                    value="{{ old(
                                        'warranty_claim_ref',
                                        $incident->warranty_claim_ref
                                    ) }}"
                                    class="form-control"
                                >
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="warranty_claim_date"
                                    class="form-label"
                                >
                                    Warranty Claim Date
                                </label>

                                <input
                                    type="date"
                                    id="warranty_claim_date"
                                    name="warranty_claim_date"
                                    value="{{ old(
                                        'warranty_claim_date',
                                        $incident->warranty_claim_date?->format('Y-m-d')
                                    ) }}"
                                    class="form-control"
                                >
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="warranty_excess"
                                    class="form-label"
                                >
                                    Warranty Excess (£)
                                </label>

                                <input
                                    type="number"
                                    id="warranty_excess"
                                    name="warranty_excess"
                                    min="0"
                                    step="0.01"
                                    value="{{ old(
                                        'warranty_excess',
                                        $incident->warranty_excess
                                    ) }}"
                                    class="form-control"
                                >
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="associated_cost"
                                    class="form-label"
                                >
                                    Associated Cost (£)
                                </label>

                                <input
                                    type="number"
                                    id="associated_cost"
                                    name="associated_cost"
                                    min="0"
                                    step="0.01"
                                    value="{{ old(
                                        'associated_cost',
                                        $incident->associated_cost
                                    ) }}"
                                    class="form-control"
                                >
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h2 class="h5 mb-0">
                            Resolution
                        </h2>
                    </div>

                    <div class="card-body">
                        <div class="mb-4">
                            <label
                                for="resolved_date"
                                class="form-label"
                            >
                                Resolution Date
                            </label>

                            <input
                                type="date"
                                id="resolved_date"
                                name="resolved_date"
                                value="{{ old(
                                    'resolved_date',
                                    $incident->resolved_date?->format('Y-m-d')
                                ) }}"
                                class="form-control"
                            >

                            <div class="form-text">
                                A resolution date is required when the incident
                                status is Resolved and cannot be before the
                                incident date.
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmIncidentUpdateModal"
                        >
                            Save Changes
                        </button>
                    </div>
                </div>

                <div
                    class="modal fade"
                    id="confirmIncidentUpdateModal"
                    tabindex="-1"
                    aria-labelledby="confirmIncidentUpdateModalLabel"
                    aria-hidden="true"
                >
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h2
                                    class="modal-title fs-5"
                                    id="confirmIncidentUpdateModalLabel"
                                >
                                    Confirm Incident Update
                                </h2>

                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"
                                ></button>
                            </div>

                            <div class="modal-body">
                                Save these changes to incident
                                <strong>#{{ $incident->id }}</strong>?
                            </div>

                            <div class="modal-footer">
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Confirm Changes
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</main>
@endsection