@extends('layouts.app')

@section('title', 'Add Staff Member')

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
        <div class="col-12 col-lg-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1">Add Staff Member</h1>
                    <p class="text-muted mb-0">
                        Create a staff record for asset assignment and incident management.
                    </p>
                </div>

                <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">
                    Back to Staff
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <form method="POST" action="{{ route('staff.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="forename" class="form-label">
                                    Forename
                                </label>

                                <input
                                    type="text"
                                    id="forename"
                                    name="forename"
                                    value="{{ old('forename') }}"
                                    class="form-control"
                                    maxlength="100"
                                    required
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="surname" class="form-label">
                                    Surname
                                </label>

                                <input
                                    type="text"
                                    id="surname"
                                    name="surname"
                                    value="{{ old('surname') }}"
                                    class="form-control"
                                    maxlength="100"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="regul8_staff_id" class="form-label">
                                Regul8 Staff ID
                            </label>

                            <input
                                type="number"
                                id="regul8_staff_id"
                                name="regul8_staff_id"
                                value="{{ old('regul8_staff_id') }}"
                                class="form-control"
                            >

                            <div class="form-text">
                                Optional.
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input
                                type="checkbox"
                                id="active"
                                name="active"
                                value="1"
                                class="form-check-input"
                                {{ old('active', true) ? 'checked' : '' }}
                            >

                            <label for="active" class="form-check-label">
                                Active staff member
                            </label>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmCreateModal"
                        >
                            Create Staff Member
                        </button>

                        <div
                            class="modal fade"
                            id="confirmCreateModal"
                            tabindex="-1"
                            aria-labelledby="confirmCreateModalLabel"
                            aria-hidden="true"
                        >
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h2
                                            class="modal-title fs-5"
                                            id="confirmCreateModalLabel"
                                        >
                                            Confirm Staff Creation
                                        </h2>

                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"
                                        ></button>
                                    </div>

                                    <div class="modal-body">
                                        Are you sure you want to create this staff record?
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