@extends('layouts.app')

@section('title', 'Incident Configuration')

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

                <button type="submit" class="btn btn-outline-light btn-sm">
                    Log out
                </button>
            </form>
        </div>
    </div>
</nav>

@if (session('success'))
    <div
        class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow"
        style="z-index: 1080; max-width: 420px;"
        role="alert"
    >
        {{ session('success') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Close"
        ></button>
    </div>
@endif

<main class="container py-5">
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There was a problem with the submitted data.</strong>

            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Incident Configuration</h1>

            <p class="text-muted mb-0">
                Manage incident types and statuses.
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            Back to Dashboard
        </a>
    </div>

    {{-- Incident Types --}}
    <div id="incident-types" class="card shadow-sm mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Incident Types</h2>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="{{ route('incident-configuration.types.store') }}"
                class="row g-3 align-items-end mb-4"
            >
                @csrf

                <div class="col-md-9">
                    <label for="new_incident_type" class="form-label">
                        New Incident Type
                    </label>

                    <input
                        type="text"
                        id="new_incident_type"
                        name="incident_type"
                        class="form-control"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        Add Incident Type
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Incident Type</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($incidentTypes as $incidentType)
                            <tr>
                                <form
                                    method="POST"
                                    action="{{ route(
                                        'incident-configuration.types.update',
                                        $incidentType
                                    ) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <td>
                                        <input
                                            type="text"
                                            name="incident_type"
                                            value="{{ $incidentType->incident_type }}"
                                            class="form-control"
                                            maxlength="100"
                                            required
                                        >
                                    </td>

                                    <td>
                                        <div class="form-check">
                                            <input
                                                type="checkbox"
                                                name="active"
                                                value="1"
                                                class="form-check-input"
                                                id="incident-type-active-{{ $incidentType->id }}"
                                                {{ $incidentType->active ? 'checked' : '' }}
                                            >

                                            <label
                                                class="form-check-label"
                                                for="incident-type-active-{{ $incidentType->id }}"
                                            >
                                                Active
                                            </label>
                                        </div>
                                    </td>

                                    <td class="text-end">
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Save
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="3"
                                    class="text-center text-muted py-4"
                                >
                                    No incident types configured.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Incident Statuses --}}
    <div id="incident-statuses" class="card shadow-sm">
        <div class="card-header">
            <h2 class="h5 mb-0">Incident Statuses</h2>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="{{ route('incident-configuration.statuses.store') }}"
                class="row g-3 align-items-end mb-4"
            >
                @csrf

                <div class="col-md-9">
                    <label for="new_incident_status" class="form-label">
                        New Incident Status
                    </label>

                    <input
                        type="text"
                        id="new_incident_status"
                        name="incident_status"
                        class="form-control"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        Add Incident Status
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Incident Status</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($incidentStatuses as $incidentStatus)
                            @php
                                $protectedStatus = in_array(
                                    $incidentStatus->incident_status,
                                    ['Open', 'Resolved'],
                                    true
                                );
                            @endphp

                            <tr>
                                <form
                                    method="POST"
                                    action="{{ route(
                                        'incident-configuration.statuses.update',
                                        $incidentStatus
                                    ) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <td>
                                        @if ($protectedStatus)
                                            <input
                                                type="text"
                                                value="{{ $incidentStatus->incident_status }}"
                                                class="form-control"
                                                disabled
                                            >

                                            <input
                                                type="hidden"
                                                name="incident_status"
                                                value="{{ $incidentStatus->incident_status }}"
                                            >

                                            <div class="form-text">
                                                System status
                                            </div>
                                        @else
                                            <input
                                                type="text"
                                                name="incident_status"
                                                value="{{ $incidentStatus->incident_status }}"
                                                class="form-control"
                                                maxlength="100"
                                                required
                                            >
                                        @endif
                                    </td>

                                    <td>
                                        @if ($protectedStatus)
                                            <input
                                                type="hidden"
                                                name="active"
                                                value="1"
                                            >

                                            <div class="form-check">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input"
                                                    checked
                                                    disabled
                                                >

                                                <label class="form-check-label">
                                                    Active
                                                </label>
                                            </div>
                                        @else
                                            <div class="form-check">
                                                <input
                                                    type="checkbox"
                                                    name="active"
                                                    value="1"
                                                    class="form-check-input"
                                                    id="incident-status-active-{{ $incidentStatus->id }}"
                                                    {{ $incidentStatus->active ? 'checked' : '' }}
                                                >

                                                <label
                                                    class="form-check-label"
                                                    for="incident-status-active-{{ $incidentStatus->id }}"
                                                >
                                                    Active
                                                </label>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        @if ($protectedStatus)
                                            <span class="text-muted small">
                                                Protected
                                            </span>
                                        @else
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                Save
                                            </button>
                                        @endif
                                    </td>
                                </form>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="3"
                                    class="text-center text-muted py-4"
                                >
                                    No incident statuses configured.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection