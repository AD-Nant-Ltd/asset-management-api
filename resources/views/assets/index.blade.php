@extends('layouts.app')

@section('title', 'Assets')

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

<main class="container py-5">

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Assets</h1>

            <p class="text-muted mb-0">
                Search, filter and manage organisational asset records.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a
                href="{{ route('dashboard') }}"
                class="btn btn-outline-secondary"
            >
                Back to Dashboard
            </a>

            <a
                href="{{ route('assets.create') }}"
                class="btn btn-primary"
            >
                Add Asset
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form
                method="GET"
                action="{{ route('assets.index') }}"
                class="row g-3 align-items-end"
            >
                <div class="col-12 col-lg-6">
                    <label
                        for="search"
                        class="form-label"
                    >
                        Search Assets
                    </label>

                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Asset tag, serial number, type or subtype"
                    >
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <label
                        for="availability"
                        class="form-label"
                    >
                        Availability
                    </label>

                    <select
                        id="availability"
                        name="availability"
                        class="form-select"
                    >
                        <option
                            value="all"
                            {{ $availability === 'all' ? 'selected' : '' }}
                        >
                            All
                        </option>

                        <option
                            value="in_stock"
                            {{ $availability === 'in_stock' ? 'selected' : '' }}
                        >
                            In Stock
                        </option>

                        <option
                            value="assigned"
                            {{ $availability === 'assigned' ? 'selected' : '' }}
                        >
                            Assigned
                        </option>

                        <option
                            value="unavailable"
                            {{ $availability === 'unavailable' ? 'selected' : '' }}
                        >
                            Unavailable
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="d-flex gap-2">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Apply
                        </button>

                        <a
                            href="{{ route('assets.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <p class="text-muted mb-0">
            {{ $assets->count() }}
            {{ $assets->count() === 1 ? 'asset' : 'assets' }} found
        </p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Tag</th>
                            <th>Type</th>
                            <th>Subtype</th>
                            <th>Status</th>
                            <th>Condition</th>
                            <th>Serial Number</th>
                            <th>Availability</th>
                            <th>Current Holder</th>
                            <th>Record Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($assets as $asset)

                            @php
                                if ($asset->activeAssignment) {
                                    $currentAvailability = 'Assigned';
                                } elseif (
                                    ! $asset->archived_at &&
                                    (
                                        $asset->retired_date === null ||
                                        $asset->retired_date->gt(today())
                                    ) &&
                                    (
                                        $asset->disposal_date === null ||
                                        $asset->disposal_date->gt(today())
                                    ) &&
                                    $asset->assetStatus->asset_status === 'Operational'
                                ) {
                                    $currentAvailability = 'In Stock';
                                } else {
                                    $currentAvailability = 'Unavailable';
                                }
                            @endphp

                            <tr>
                                <td>
                                    <strong>
                                        {{ $asset->asset_tag }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $asset->assetSubtype->assetType->asset_type }}
                                </td>

                                <td>
                                    {{ $asset->assetSubtype->asset_subtype }}
                                </td>

                                <td>
                                    {{ $asset->assetStatus->asset_status }}
                                </td>

                                <td>
                                    {{ $asset->assetCondition->asset_condition }}
                                </td>

                                <td>
                                    {{ $asset->serial_num ?? '—' }}
                                </td>

                                <td>
                                    @if ($currentAvailability === 'Assigned')
                                        <span class="badge text-bg-primary">
                                            Assigned
                                        </span>

                                    @elseif ($currentAvailability === 'In Stock')
                                        <span class="badge text-bg-success">
                                            In Stock
                                        </span>

                                    @else
                                        <span class="badge text-bg-secondary">
                                            Unavailable
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($asset->activeAssignment)
                                        {{ $asset->activeAssignment->assignedTo->forename }}
                                        {{ $asset->activeAssignment->assignedTo->surname }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    @if ($asset->archived_at)
                                        <span class="badge text-bg-secondary">
                                            Archived
                                        </span>
                                    @else
                                        <span class="badge text-bg-success">
                                            Active
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('assets.show', $asset) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        View
                                    </a>

                                    @if (! $asset->archived_at)
                                        <a
                                            href="{{ route('assets.edit', $asset) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                        >
                                            Edit
                                        </a>
                                    @endif
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td
                                    colspan="10"
                                    class="text-center text-muted py-4"
                                >
                                    No asset records match the current search or filter.
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