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
                View and manage organisational asset records.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                Back to Dashboard
            </a>

            <a href="{{ route('assets.create') }}" class="btn btn-primary">
                Add Asset
            </a>
        </div>
    </div>

    <div class="card">
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
                            <th>Record Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($assets as $asset)
                            <tr>
                                <td>
                                    <strong>{{ $asset->asset_tag }}</strong>
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
                                <td colspan="8" class="text-center text-muted py-4">
                                    No asset records found.
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