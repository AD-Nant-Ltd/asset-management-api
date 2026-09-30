@extends('layouts.app')

@section('title', 'Staff')

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
            <h1 class="mb-1">Staff</h1>
            <p class="text-muted mb-0">
                Manage staff records used for asset assignments and incidents.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                Back to Dashboard
            </a>

            <a href="{{ route('staff.create') }}" class="btn btn-primary">
                Add Staff Member
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Regul8 Staff ID</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($staff as $staffMember)
                            <tr>
                                <td>
                                    {{ $staffMember->forename }}
                                    {{ $staffMember->surname }}
                                </td>

                                <td>
                                    {{ $staffMember->regul8_staff_id ?? '—' }}
                                </td>

                                <td>
                                    @if ($staffMember->active)
                                        <span class="badge text-bg-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge text-bg-secondary">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('staff.show', $staffMember) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="{{ route('staff.edit', $staffMember) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                    >
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No staff records found.
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