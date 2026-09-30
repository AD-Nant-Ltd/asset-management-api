@extends('layouts.app')

@section('title', 'Edit Application User')

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
                    <h1 class="mb-1">Edit Application User</h1>

                    <p class="text-muted mb-0">
                        Update account access, role or credentials.
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

                    <div class="mb-4">
                        <h2 class="h5 mb-3">Staff Member</h2>

                        <dl class="row mb-0">
                            <dt class="col-sm-4">Name</dt>
                            <dd class="col-sm-8">
                                {{ $user->staff->forename }}
                                {{ $user->staff->surname }}
                            </dd>

                            <dt class="col-sm-4">Regul8 Staff ID</dt>
                            <dd class="col-sm-8">
                                {{ $user->staff->regul8_staff_id ?? 'Not recorded' }}
                            </dd>
                        </dl>
                    </div>

                    <hr>

                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
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
                                @foreach ($roles as $role)
                                    <option
                                        value="{{ $role->id }}"
                                        {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}
                                    >
                                        {{ $role->role_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-check mb-4">
                            <input
                                type="checkbox"
                                id="active"
                                name="active"
                                value="1"
                                class="form-check-input"
                                {{ old('active', $user->active) ? 'checked' : '' }}
                            >

                            <label for="active" class="form-check-label">
                                Application access active
                            </label>

                            <div class="form-text">
                                Uncheck this option to revoke application access without deleting the user or staff record.
                            </div>
                        </div>

                        <hr>

                        <h2 class="h5 mb-3">Reset Password</h2>

                        <p class="text-muted">
                            Leave both password fields blank to keep the existing password.
                        </p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    New Password
                                </label>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    autocomplete="new-password"
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">
                                    Confirm New Password
                                </label>

                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control"
                                    autocomplete="new-password"
                                >
                            </div>
                        </div>

                        <div class="form-text mb-4">
                            Enter and confirm a new password only when the account password needs to be reset.
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmUpdateUserModal"
                        >
                            Save Changes
                        </button>

                        <div
                            class="modal fade"
                            id="confirmUpdateUserModal"
                            tabindex="-1"
                            aria-labelledby="confirmUpdateUserModalLabel"
                            aria-hidden="true"
                        >
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h2
                                            class="modal-title fs-5"
                                            id="confirmUpdateUserModalLabel"
                                        >
                                            Confirm Account Changes
                                        </h2>

                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"
                                        ></button>
                                    </div>

                                    <div class="modal-body">
                                        Are you sure you want to save these changes to this application account?
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
                                            Confirm Changes
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