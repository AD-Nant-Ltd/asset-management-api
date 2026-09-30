@extends('layouts.app')

@section('title', 'Grant Application Access')

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
                    <h1 class="mb-1">Grant Application Access</h1>

                    <p class="text-muted mb-0">
                        Create an application account for an existing staff member.
                    </p>
                </div>

                <a
                    href="{{ route('users.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Back to Application Users
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

                    @if ($staff->isEmpty())
                        <div class="alert alert-info mb-0">
                            All available staff members already have application accounts.
                        </div>
                    @else
                        <form method="POST" action="{{ route('users.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="staff_id" class="form-label">
                                    Staff Member
                                </label>

                                <select
                                    id="staff_id"
                                    name="staff_id"
                                    class="form-select"
                                    required
                                >
                                    <option value="">
                                        Select a staff member
                                    </option>

                                    @foreach ($staff as $staffMember)
                                        <option
                                            value="{{ $staffMember->id }}"
                                            {{ old('staff_id') == $staffMember->id ? 'selected' : '' }}
                                        >
                                            {{ $staffMember->forename }}
                                            {{ $staffMember->surname }}
                                            @if ($staffMember->regul8_staff_id)
                                                — Regul8 ID {{ $staffMember->regul8_staff_id }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>

                                <div class="form-text">
                                    Only staff members without an existing application account are shown.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    class="form-control"
                                    maxlength="255"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="role_id" class="form-label">
                                    Role
                                </label>

                                <select
                                    id="role_id"
                                    name="role_id"
                                    class="form-select"
                                    required
                                >
                                    <option value="">
                                        Select a role
                                    </option>

                                    @foreach ($roles as $role)
                                        <option
                                            value="{{ $role->id }}"
                                            {{ old('role_id') == $role->id ? 'selected' : '' }}
                                        >
                                            {{ $role->role_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        Password
                                    </label>

                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        autocomplete="new-password"
                                        required
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">
                                        Confirm Password
                                    </label>

                                    <input
                                        type="password"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        class="form-control"
                                        autocomplete="new-password"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-text mb-4">
                                The password must meet the application's password requirements.
                            </div>

                            <button
                                type="button"
                                class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#confirmCreateUserModal"
                            >
                                Grant Application Access
                            </button>

                            <div
                                class="modal fade"
                                id="confirmCreateUserModal"
                                tabindex="-1"
                                aria-labelledby="confirmCreateUserModalLabel"
                                aria-hidden="true"
                            >
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h2
                                                class="modal-title fs-5"
                                                id="confirmCreateUserModalLabel"
                                            >
                                                Confirm Application Access
                                            </h2>

                                            <button
                                                type="button"
                                                class="btn-close"
                                                data-bs-dismiss="modal"
                                                aria-label="Close"
                                            ></button>
                                        </div>

                                        <div class="modal-body">
                                            Are you sure you want to grant this staff member access to the Asset Management System?
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
                                                Confirm Access
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif

                </div>
            </div>

        </div>
    </div>
</main>
@endsection