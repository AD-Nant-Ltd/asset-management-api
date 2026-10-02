@extends('layouts.app')

@section('title', 'Add Asset')

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
        <div class="col-12 col-xl-10">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1">Add Asset</h1>

                    <p class="text-muted mb-0">
                        Create a new organisational asset record.
                    </p>
                </div>

                <a
                    href="{{ route('assets.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Back to Assets
                </a>
            </div>

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

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <form method="POST" action="{{ route('assets.store') }}">
                        @csrf

                        <h2 class="h5 mb-3">Asset Identification</h2>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="asset_tag" class="form-label">
                                    Asset Tag
                                </label>

                                <input
                                    type="text"
                                    id="asset_tag"
                                    name="asset_tag"
                                    value="{{ old('asset_tag') }}"
                                    class="form-control"
                                    maxlength="100"
                                    required
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="serial_num" class="form-label">
                                    Serial Number
                                </label>

                                <input
                                    type="text"
                                    id="serial_num"
                                    name="serial_num"
                                    value="{{ old('serial_num') }}"
                                    class="form-control"
                                    maxlength="255"
                                >
                            </div>
                        </div>

                        <hr>

                        <h2 class="h5 mb-3">Classification</h2>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="asset_subtype_id" class="form-label">
                                    Asset Type / Subtype
                                </label>

                                <select
                                    id="asset_subtype_id"
                                    name="asset_subtype_id"
                                    class="form-select"
                                    required
                                >
                                    <option value="">
                                        Select an asset subtype
                                    </option>

                                    @foreach ($assetSubtypes as $assetSubtype)
                                        <option
                                            value="{{ $assetSubtype->id }}"
                                            {{ old('asset_subtype_id') == $assetSubtype->id ? 'selected' : '' }}
                                        >
                                            {{ $assetSubtype->assetType->asset_type }}
                                            —
                                            {{ $assetSubtype->asset_subtype }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="asset_status_id" class="form-label">
                                    Operational Status
                                </label>

                                <select
                                    id="asset_status_id"
                                    name="asset_status_id"
                                    class="form-select"
                                    required
                                >
                                    <option value="">
                                        Select a status
                                    </option>

                                    @foreach ($assetStatuses as $assetStatus)
                                        <option
                                            value="{{ $assetStatus->id }}"
                                            {{ old('asset_status_id') == $assetStatus->id ? 'selected' : '' }}
                                        >
                                            {{ $assetStatus->asset_status }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="asset_condition_id" class="form-label">
                                    Condition
                                </label>

                                <select
                                    id="asset_condition_id"
                                    name="asset_condition_id"
                                    class="form-select"
                                    required
                                >
                                    <option value="">
                                        Select a condition
                                    </option>

                                    @foreach ($assetConditions as $assetCondition)
                                        <option
                                            value="{{ $assetCondition->id }}"
                                            {{ old('asset_condition_id') == $assetCondition->id ? 'selected' : '' }}
                                        >
                                            {{ $assetCondition->asset_condition }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>

                        <h2 class="h5 mb-3">Lifecycle Dates</h2>

                        <div class="row">
                            <div class="col-md-6 col-lg-4 mb-3">
                                <label for="delivery_date" class="form-label">
                                    Delivery Date
                                </label>

                                <input
                                    type="date"
                                    id="delivery_date"
                                    name="delivery_date"
                                    value="{{ old('delivery_date') }}"
                                    class="form-control"
                                >
                            </div>

                            <div class="col-md-6 col-lg-4 mb-3">
                                <label for="purchase_date" class="form-label">
                                    Purchase Date
                                </label>

                                <input
                                    type="date"
                                    id="purchase_date"
                                    name="purchase_date"
                                    value="{{ old('purchase_date') }}"
                                    class="form-control"
                                >
                            </div>

                            <div class="col-md-6 col-lg-4 mb-3">
                                <label for="warranty_expiry" class="form-label">
                                    Warranty Expiry
                                </label>

                                <input
                                    type="date"
                                    id="warranty_expiry"
                                    name="warranty_expiry"
                                    value="{{ old('warranty_expiry') }}"
                                    class="form-control"
                                >
                            </div>

                            <div class="col-md-6 col-lg-4 mb-3">
                                <label for="retired_date" class="form-label">
                                    Retired Date
                                </label>

                                <input
                                    type="date"
                                    id="retired_date"
                                    name="retired_date"
                                    value="{{ old('retired_date') }}"
                                    class="form-control"
                                >
                            </div>

                            <div class="col-md-6 col-lg-4 mb-3">
                                <label for="disposal_date" class="form-label">
                                    Disposal Date
                                </label>

                                <input
                                    type="date"
                                    id="disposal_date"
                                    name="disposal_date"
                                    value="{{ old('disposal_date') }}"
                                    class="form-control"
                                >
                            </div>
                        </div>

                        <div class="alert alert-light border mt-2">
                            Availability is derived from the asset's assignment and lifecycle state rather than entered manually.
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmCreateAssetModal"
                        >
                            Create Asset
                        </button>

                        <div
                            class="modal fade"
                            id="confirmCreateAssetModal"
                            tabindex="-1"
                            aria-labelledby="confirmCreateAssetModalLabel"
                            aria-hidden="true"
                        >
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h2
                                            class="modal-title fs-5"
                                            id="confirmCreateAssetModalLabel"
                                        >
                                            Confirm Asset Creation
                                        </h2>

                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"
                                        ></button>
                                    </div>

                                    <div class="modal-body">
                                        Are you sure you want to create this asset record?
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
                                            Confirm Creation
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