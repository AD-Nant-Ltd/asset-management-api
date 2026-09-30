@extends('layouts.app')

@section('title', 'Asset Classification')

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
            <h1 class="mb-1">Asset Classification</h1>

            <p class="text-muted mb-0">
                Manage asset types, subtypes, statuses and conditions.
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            Back to Dashboard
        </a>
    </div>

    {{-- Asset Types --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Asset Types</h2>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="{{ route('asset-classification.types.store') }}"
                class="row g-3 align-items-end mb-4"
            >
                @csrf

                <div class="col-md-9">
                    <label for="new_asset_type" class="form-label">
                        New Asset Type
                    </label>

                    <input
                        type="text"
                        id="new_asset_type"
                        name="asset_type"
                        class="form-control"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        Add Asset Type
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Type</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($assetTypes as $assetType)
                            <tr>
                                <form
                                    method="POST"
                                    action="{{ route('asset-classification.types.update', $assetType) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <td>
                                        <input
                                            type="text"
                                            name="asset_type"
                                            value="{{ $assetType->asset_type }}"
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
                                                id="asset-type-active-{{ $assetType->id }}"
                                                {{ $assetType->active ? 'checked' : '' }}
                                            >

                                            <label
                                                class="form-check-label"
                                                for="asset-type-active-{{ $assetType->id }}"
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
                                <td colspan="3" class="text-center text-muted py-4">
                                    No asset types configured.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Asset Subtypes --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Asset Subtypes</h2>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="{{ route('asset-classification.subtypes.store') }}"
                class="row g-3 align-items-end mb-4"
            >
                @csrf

                <div class="col-md-4">
                    <label for="new_subtype_type" class="form-label">
                        Asset Type
                    </label>

                    <select
                        id="new_subtype_type"
                        name="asset_type_id"
                        class="form-select"
                        required
                    >
                        <option value="">Select an asset type</option>

                        @foreach ($assetTypes as $assetType)
                            <option value="{{ $assetType->id }}">
                                {{ $assetType->asset_type }}
                                @if (! $assetType->active)
                                    (Inactive)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5">
                    <label for="new_asset_subtype" class="form-label">
                        New Asset Subtype
                    </label>

                    <input
                        type="text"
                        id="new_asset_subtype"
                        name="asset_subtype"
                        class="form-control"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        Add Asset Subtype
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subtype</th>
                            <th>Asset Type</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($assetSubtypes as $assetSubtype)
                            <tr>
                                <form
                                    method="POST"
                                    action="{{ route('asset-classification.subtypes.update', $assetSubtype) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <td>
                                        <input
                                            type="text"
                                            name="asset_subtype"
                                            value="{{ $assetSubtype->asset_subtype }}"
                                            class="form-control"
                                            maxlength="100"
                                            required
                                        >
                                    </td>

                                    <td>
                                        <select
                                            name="asset_type_id"
                                            class="form-select"
                                            required
                                        >
                                            @foreach ($assetTypes as $assetType)
                                                <option
                                                    value="{{ $assetType->id }}"
                                                    {{ $assetSubtype->asset_type_id == $assetType->id ? 'selected' : '' }}
                                                >
                                                    {{ $assetType->asset_type }}

                                                    @if (! $assetType->active)
                                                        (Inactive)
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <div class="form-check">
                                            <input
                                                type="checkbox"
                                                name="active"
                                                value="1"
                                                class="form-check-input"
                                                id="asset-subtype-active-{{ $assetSubtype->id }}"
                                                {{ $assetSubtype->active ? 'checked' : '' }}
                                            >

                                            <label
                                                class="form-check-label"
                                                for="asset-subtype-active-{{ $assetSubtype->id }}"
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
                                <td colspan="4" class="text-center text-muted py-4">
                                    No asset subtypes configured.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Asset Statuses --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Asset Statuses</h2>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="{{ route('asset-classification.statuses.store') }}"
                class="row g-3 align-items-end mb-4"
            >
                @csrf

                <div class="col-md-9">
                    <label for="new_asset_status" class="form-label">
                        New Asset Status
                    </label>

                    <input
                        type="text"
                        id="new_asset_status"
                        name="asset_status"
                        class="form-control"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        Add Asset Status
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Status</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($assetStatuses as $assetStatus)
                            <tr>
                                <form
                                    method="POST"
                                    action="{{ route('asset-classification.statuses.update', $assetStatus) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <td>
                                        <input
                                            type="text"
                                            name="asset_status"
                                            value="{{ $assetStatus->asset_status }}"
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
                                                id="asset-status-active-{{ $assetStatus->id }}"
                                                {{ $assetStatus->active ? 'checked' : '' }}
                                            >

                                            <label
                                                class="form-check-label"
                                                for="asset-status-active-{{ $assetStatus->id }}"
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
                                <td colspan="3" class="text-center text-muted py-4">
                                    No asset statuses configured.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Asset Conditions --}}
    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="h5 mb-0">Asset Conditions</h2>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="{{ route('asset-classification.conditions.store') }}"
                class="row g-3 align-items-end mb-4"
            >
                @csrf

                <div class="col-md-9">
                    <label for="new_asset_condition" class="form-label">
                        New Asset Condition
                    </label>

                    <input
                        type="text"
                        id="new_asset_condition"
                        name="asset_condition"
                        class="form-control"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        Add Asset Condition
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Condition</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($assetConditions as $assetCondition)
                            <tr>
                                <form
                                    method="POST"
                                    action="{{ route('asset-classification.conditions.update', $assetCondition) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <td>
                                        <input
                                            type="text"
                                            name="asset_condition"
                                            value="{{ $assetCondition->asset_condition }}"
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
                                                id="asset-condition-active-{{ $assetCondition->id }}"
                                                {{ $assetCondition->active ? 'checked' : '' }}
                                            >

                                            <label
                                                class="form-check-label"
                                                for="asset-condition-active-{{ $assetCondition->id }}"
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
                                <td colspan="3" class="text-center text-muted py-4">
                                    No asset conditions configured.
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