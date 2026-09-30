@extends('layouts.app')

@section('title', 'Dashboard')

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
    <div class="row">
        <div class="col-12">
            <h1 class="mb-3">Dashboard</h1>

            <p class="text-muted">
                Welcome to the Asset Management System.
            </p>

            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="h5">Authenticated User</h2>

                            <dl class="row mb-0">
                                <dt class="col-sm-3">Name</dt>
                                <dd class="col-sm-9">
                                    {{ auth()->user()->staff->forename }}
                                    {{ auth()->user()->staff->surname }}
                                </dd>

                                <dt class="col-sm-3">Email</dt>
                                <dd class="col-sm-9">
                                    {{ auth()->user()->email }}
                                </dd>

                                <dt class="col-sm-3">Role</dt>
                                <dd class="col-sm-9">
                                    {{ auth()->user()->role->role_name }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="h5">Staff Management</h2>

                            <p class="text-muted">
                                View, create and update staff records used for asset assignments and incidents.
                            </p>

                            <a href="{{ route('staff.index') }}" class="btn btn-primary">
                                Manage Staff
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection