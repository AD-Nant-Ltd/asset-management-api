@extends('layouts.app')

@section('title', 'Assign Asset')

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
                    <h1 class="mb-1">Assign Asset</h1>

                    <p class="text-muted mb-0">
                        Assign asset {{ $asset->asset_tag }} to a staff member.
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
                    <strong>The asset could not be assigned.</strong>

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

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <form
                        method="POST"
                        action="{{ route('assets.assign.store', $asset) }}"
                    >
                        @csrf

                        <div class="mb-3">
                            <label
                                for="assigned_to_id"
                                class="form-label"
                            >
                                Staff Member
                            </label>

                            <select
                                id="assigned_to_id"
                                name="assigned_to_id"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Select a staff member
                                </option>

                                @foreach ($staffMembers as $staffMember)
                                    <option
                                        value="{{ $staffMember->id }}"
                                        {{ old('assigned_to_id') == $staffMember->id ? 'selected' : '' }}
                                    >
                                        {{ $staffMember->forename }}
                                        {{ $staffMember->surname }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="form-text">
                                Only active staff members are available.
                                The staff member does not require an application account.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label
                                for="assigned_date"
                                class="form-label"
                            >
                                Assignment Date
                            </label>

                            <input
                                type="date"
                                id="assigned_date"
                                name="assigned_date"
                                value="{{ old('assigned_date', now()->format('Y-m-d')) }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label
                                for="notes"
                                class="form-label"
                            >
                                Notes
                            </label>

                            <textarea
                                id="notes"
                                name="notes"
                                class="form-control"
                                rows="4"
                                maxlength="1000"
                            >{{ old('notes') }}</textarea>

                            <div class="form-text">
                                Optional assignment notes.
                            </div>
                        </div>

                        <div class="alert alert-light border">
                            The assignment will record you as the application user
                            who processed the transaction.
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmAssignmentModal"
                        >
                            Assign Asset
                        </button>

                        <div
                            class="modal fade"
                            id="confirmAssignmentModal"
                            tabindex="-1"
                            aria-labelledby="confirmAssignmentModalLabel"
                            aria-hidden="true"
                        >
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h2
                                            class="modal-title fs-5"
                                            id="confirmAssignmentModalLabel"
                                        >
                                            Confirm Asset Assignment
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
                                        should be assigned to the selected staff member.
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
                                            Confirm Assignment
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