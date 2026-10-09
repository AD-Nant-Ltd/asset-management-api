@extends('layouts.app')

@section('title', 'Record Incident')

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
        <div class="col-12 col-xl-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1">
                        Record Incident
                    </h1>

                    <p class="text-muted mb-0">
                        Record an incident against asset
                        {{ $asset->asset_tag }}.
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
                        The incident could not be recorded.
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
                        Asset
                    </h2>
                </div>

                <div class="card-body">
                    <dl class="row mb-0">

                        <dt class="col-sm-4">
                            Asset Tag
                        </dt>

                        <dd class="col-sm-8">
                            {{ $asset->asset_tag }}
                        </dd>

                        <dt class="col-sm-4">
                            Type
                        </dt>

                        <dd class="col-sm-8">
                            {{ $asset->assetSubtype->assetType->asset_type }}
                            —
                            {{ $asset->assetSubtype->asset_subtype }}
                        </dd>

                        <dt class="col-sm-4">
                            Operational Status
                        </dt>

                        <dd class="col-sm-8">
                            {{ $asset->assetStatus->asset_status }}
                        </dd>

                        <dt class="col-sm-4">
                            Condition
                        </dt>

                        <dd class="col-sm-8">
                            {{ $asset->assetCondition->asset_condition }}
                        </dd>

                        <dt class="col-sm-4">
                            Current Holder
                        </dt>

                        <dd class="col-sm-8">
                            @if ($asset->activeAssignment)
                                {{ $asset->activeAssignment->assignedTo->forename }}
                                {{ $asset->activeAssignment->assignedTo->surname }}
                            @else
                                <span class="text-muted">
                                    No current assignment
                                </span>
                            @endif
                        </dd>

                    </dl>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('assets.incidents.store', $asset) }}"
            >
                @csrf

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h2 class="h5 mb-0">
                            Incident Details
                        </h2>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label
                                    for="incident_type_id"
                                    class="form-label"
                                >
                                    Incident Type
                                </label>

                                <select
                                    id="incident_type_id"
                                    name="incident_type_id"
                                    class="form-select"
                                    required
                                >
                                    <option value="">
                                        Select incident type
                                    </option>

                                    @foreach ($incidentTypes as $incidentType)
                                        <option
                                            value="{{ $incidentType->id }}"
                                            {{
                                                old('incident_type_id')
                                                == $incidentType->id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            {{ $incidentType->incident_type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="incident_date"
                                    class="form-label"
                                >
                                    Incident Date
                                </label>

                                <input
                                    type="date"
                                    id="incident_date"
                                    name="incident_date"
                                    value="{{ old(
                                        'incident_date',
                                        today()->format('Y-m-d')
                                    ) }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label
                                    for="affected_staff_id"
                                    class="form-label"
                                >
                                    Associated Staff
                                </label>

                                <select
                                    id="affected_staff_id"
                                    name="affected_staff_id"
                                    class="form-select"
                                    required
                                >
                                    <option value="">
                                        Select staff member
                                    </option>

                                    @foreach ($staffMembers as $staffMember)
                                        <option
                                            value="{{ $staffMember->id }}"
                                            {{
                                                old(
                                                    'affected_staff_id',
                                                    $asset->activeAssignment
                                                        ?->assigned_to_id
                                                ) == $staffMember->id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            {{ $staffMember->forename }}
                                            {{ $staffMember->surname }}

                                            @if (! $staffMember->active)
                                                (Inactive)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>

                                <div class="form-text">
                                    The current asset holder is selected
                                    automatically where applicable.
                                </div>
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
                                                    auth()->id()
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
                                    The incident is assigned to you by default,
                                    but it can be reassigned or left unassigned.
                                </div>
                            </div>

                            <div class="col-12">
                                <label
                                    for="description"
                                    class="form-label"
                                >
                                    Description
                                </label>

                                <textarea
                                    id="description"
                                    name="description"
                                    rows="5"
                                    maxlength="5000"
                                    class="form-control"
                                    required
                                >{{ old('description') }}</textarea>
                            </div>

                        </div>

                        <div class="alert alert-info mt-4 mb-0">
                            The incident will initially be recorded with an
                            <strong>Open</strong> status.
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a
                            href="{{ route('assets.show', $asset) }}"
                            class="btn btn-outline-secondary"
                        >
                            Cancel
                        </a>

                        <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmIncidentModal"
                        >
                            Record Incident
                        </button>
                    </div>
                </div>

                <div
                    class="modal fade"
                    id="confirmIncidentModal"
                    tabindex="-1"
                    aria-labelledby="confirmIncidentModalLabel"
                    aria-hidden="true"
                >
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h2
                                    class="modal-title fs-5"
                                    id="confirmIncidentModalLabel"
                                >
                                    Confirm Incident
                                </h2>

                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"
                                ></button>
                            </div>

                            <div class="modal-body">
                                Record this incident against asset
                                <strong>{{ $asset->asset_tag }}</strong>?
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
                                    Confirm Incident
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