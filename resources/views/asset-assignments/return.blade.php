@extends('layouts.app')

@section('title', 'Return Asset')

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
    <div class="row justify-content-center">
        <div class="col-12 col-xl-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1">Return Asset</h1>

                    <p class="text-muted mb-0">
                        Return asset {{ $asset->asset_tag }} to stock.
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
                    <strong>The asset could not be returned.</strong>

                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">Asset</h2>
                </div>

                <div class="card-body">
                    <dl class="row mb-0">

                        <dt class="col-sm-4">Asset Tag</dt>
                        <dd class="col-sm-8">
                            {{ $asset->asset_tag }}
                        </dd>

                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">
                            {{ $asset->assetSubtype->assetType->asset_type }}
                            —
                            {{ $asset->assetSubtype->asset_subtype }}
                        </dd>

                        <dt class="col-sm-4">Operational Status</dt>
                        <dd class="col-sm-8">
                            {{ $asset->assetStatus->asset_status }}
                        </dd>

                        <dt class="col-sm-4">Condition</dt>
                        <dd class="col-sm-8">
                            {{ $asset->assetCondition->asset_condition }}
                        </dd>

                    </dl>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">Current Assignment</h2>
                </div>

                <div class="card-body">
                    <dl class="row mb-0">

                        <dt class="col-sm-4">Current Holder</dt>
                        <dd class="col-sm-8">
                            <strong>
                                {{ $assignment->assignedTo->forename }}
                                {{ $assignment->assignedTo->surname }}
                            </strong>
                        </dd>

                        <dt class="col-sm-4">Assigned Date</dt>
                        <dd class="col-sm-8">
                            {{ $assignment->assigned_date->format('d/m/Y') }}
                        </dd>

                        <dt class="col-sm-4">Processed By</dt>
                        <dd class="col-sm-8">
                            {{ $assignment->assignedBy->staff->forename }}
                            {{ $assignment->assignedBy->staff->surname }}
                        </dd>

                        <dt class="col-sm-4">Assignment Notes</dt>
                        <dd class="col-sm-8">
                            {{ $assignment->notes ?? 'No notes recorded' }}
                        </dd>

                    </dl>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <form
                        method="POST"
                        action="{{ route('assets.return.store', $asset) }}"
                    >
                        @csrf

                        <div class="mb-4">
                            <label
                                for="returned_date"
                                class="form-label"
                            >
                                Return Date
                            </label>

                            <input
                                type="date"
                                id="returned_date"
                                name="returned_date"
                                value="{{ old('returned_date', now()->format('Y-m-d')) }}"
                                min="{{ $assignment->assigned_date->format('Y-m-d') }}"
                                class="form-control"
                                required
                            >

                            <div class="form-text">
                                The return date cannot be before the assignment date
                                of {{ $assignment->assigned_date->format('d/m/Y') }}.
                            </div>
                        </div>

                        <div class="alert alert-light border">
                            Returning this asset will close the current assignment
                            and record you as the application user who processed
                            the return. The existing assignment record will remain
                            in the asset's history.
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmReturnModal"
                        >
                            Return Asset
                        </button>

                        <div
                            class="modal fade"
                            id="confirmReturnModal"
                            tabindex="-1"
                            aria-labelledby="confirmReturnModalLabel"
                            aria-hidden="true"
                        >
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h2
                                            class="modal-title fs-5"
                                            id="confirmReturnModalLabel"
                                        >
                                            Confirm Asset Return
                                        </h2>

                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"
                                        ></button>
                                    </div>

                                    <div class="modal-body">
                                        Confirm that asset
                                        <strong>{{ $asset->asset_tag }}</strong>
                                        has been returned by

                                        <strong>
                                            {{ $assignment->assignedTo->forename }}
                                            {{ $assignment->assignedTo->surname }}
                                        </strong>.

                                        <p class="text-muted mt-2 mb-0">
                                            The current assignment will be closed
                                            but retained in the asset's assignment history.
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

                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >
                                            Confirm Return
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</main>

@endsection