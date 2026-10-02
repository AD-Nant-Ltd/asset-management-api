@extends('layouts.app')

@section('title', 'Asset Details')

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
            <div class="d-flex align-items-center gap-2">
                <h1 class="mb-1">
                    {{ $asset->asset_tag }}
                </h1>

                @if ($asset->archived_at)
                    <span class="badge text-bg-secondary">
                        Archived
                    </span>
                @else
                    <span class="badge text-bg-success">
                        Active
                    </span>
                @endif
            </div>

            <p class="text-muted mb-0">
                Asset record
            </p>
        </div>

        <div class="d-flex gap-2">
            <a
                href="{{ route('assets.index') }}"
                class="btn btn-outline-secondary"
            >
                Back to Assets
            </a>

            @if (! $asset->archived_at)
                <a
                    href="{{ route('assets.edit', $asset) }}"
                    class="btn btn-primary"
                >
                    Edit Asset
                </a>
            @endif
        </div>
    </div>

    @if ($asset->archived_at)
        <div class="alert alert-secondary">
            This asset was archived on
            <strong>{{ $asset->archived_at->format('d/m/Y H:i') }}</strong>.
            Its historical records remain available.
        </div>
    @endif

    <div class="row g-4">

        {{-- Identification --}}
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">Identification</h2>
                </div>

                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Asset Tag</dt>
                        <dd class="col-sm-7">
                            {{ $asset->asset_tag }}
                        </dd>

                        <dt class="col-sm-5">Serial Number</dt>
                        <dd class="col-sm-7">
                            {{ $asset->serial_num ?? 'Not recorded' }}
                        </dd>

                        <dt class="col-sm-5">Asset Type</dt>
                        <dd class="col-sm-7">
                            {{ $asset->assetSubtype->assetType->asset_type }}
                        </dd>

                        <dt class="col-sm-5">Asset Subtype</dt>
                        <dd class="col-sm-7">
                            {{ $asset->assetSubtype->asset_subtype }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Current State --}}
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">Current State</h2>
                </div>

                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Operational Status</dt>
                        <dd class="col-sm-7">
                            {{ $asset->assetStatus->asset_status }}
                        </dd>

                        <dt class="col-sm-5">Condition</dt>
                        <dd class="col-sm-7">
                            {{ $asset->assetCondition->asset_condition }}
                        </dd>

                        <dt class="col-sm-5">Record Status</dt>
                        <dd class="col-sm-7">
                            @if ($asset->archived_at)
                                <span class="badge text-bg-secondary">
                                    Archived
                                </span>
                            @else
                                <span class="badge text-bg-success">
                                    Active
                                </span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Lifecycle --}}
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">Lifecycle</h2>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg">
                            <div class="text-muted small">
                                Delivery Date
                            </div>

                            <div>
                                {{ $asset->delivery_date?->format('d/m/Y') ?? 'Not recorded' }}
                            </div>
                        </div>

                        <div class="col-md-6 col-lg">
                            <div class="text-muted small">
                                Purchase Date
                            </div>

                            <div>
                                {{ $asset->purchase_date?->format('d/m/Y') ?? 'Not recorded' }}
                            </div>
                        </div>

                        <div class="col-md-6 col-lg">
                            <div class="text-muted small">
                                Warranty Expiry
                            </div>

                            <div>
                                {{ $asset->warranty_expiry?->format('d/m/Y') ?? 'Not recorded' }}
                            </div>
                        </div>

                        <div class="col-md-6 col-lg">
                            <div class="text-muted small">
                                Retired Date
                            </div>

                            <div>
                                {{ $asset->retired_date?->format('d/m/Y') ?? 'Not recorded' }}
                            </div>
                        </div>

                        <div class="col-md-6 col-lg">
                            <div class="text-muted small">
                                Disposal Date
                            </div>

                            <div>
                                {{ $asset->disposal_date?->format('d/m/Y') ?? 'Not recorded' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Assignment History --}}
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">Assignment History</h2>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Assigned Date</th>
                                    <th>Returned Date</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($asset->assignments as $assignment)
                                    <tr>
                                        <td>
                                            {{ $assignment->assignedTo->forename }}
                                            {{ $assignment->assignedTo->surname }}
                                        </td>

                                        <td>
                                            {{ $assignment->assigned_date?->format('d/m/Y') ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $assignment->returned_date?->format('d/m/Y') ?? 'Currently assigned' }}
                                        </td>

                                        <td>
                                            {{ $assignment->notes ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No assignment history recorded.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Incident History --}}
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h5 mb-0">Incident History</h2>
                </div>

                <div class="card-body">
                    @if ($asset->incidents->isEmpty())
                        <p class="text-muted mb-0">
                            No incidents recorded for this asset.
                        </p>
                    @else
                        <p class="mb-0">
                            {{ $asset->incidents->count() }}
                            incident record(s) are associated with this asset.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Archive --}}
        @if (! $asset->archived_at)
            <div class="col-12">
                <div class="card border-warning shadow-sm">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Archive Asset</h2>
                    </div>

                    <div class="card-body">
                        <p>
                            Archiving removes the asset from active use without
                            deleting the asset record or its historical data.
                        </p>

                        <button
                            type="button"
                            class="btn btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmArchiveAssetModal"
                        >
                            Archive Asset
                        </button>
                    </div>
                </div>
            </div>

            <div
                class="modal fade"
                id="confirmArchiveAssetModal"
                tabindex="-1"
                aria-labelledby="confirmArchiveAssetModalLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h2
                                class="modal-title fs-5"
                                id="confirmArchiveAssetModalLabel"
                            >
                                Confirm Asset Archive
                            </h2>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"
                            ></button>
                        </div>

                        <div class="modal-body">
                            Are you sure you want to archive asset
                            <strong>{{ $asset->asset_tag }}</strong>?

                            <p class="text-muted mt-2 mb-0">
                                The asset will not be deleted and its historical
                                assignment and incident records will be preserved.
                            </p>
                        </div>

                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                data-bs-dismiss="modal"
                            >
                                Cancel
                            </button>

                            <form
                                method="POST"
                                action="{{ route('assets.archive', $asset) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="btn btn-danger"
                                >
                                    Confirm Archive
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        @endif

    </div>
</main>
@endsection