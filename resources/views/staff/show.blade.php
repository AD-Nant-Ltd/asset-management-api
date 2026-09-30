@extends('layouts.app')

@section('title', 'Staff Details')

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

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1">
                        {{ $staff->forename }} {{ $staff->surname }}
                    </h1>

                    <p class="text-muted mb-0">
                        Staff record
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <a
                        href="{{ route('staff.edit', $staff) }}"
                        class="btn btn-primary"
                    >
                        Edit
                    </a>

                    <a
                        href="{{ route('staff.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Back to Staff
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Forename</dt>
                        <dd class="col-sm-8">
                            {{ $staff->forename }}
                        </dd>

                        <dt class="col-sm-4">Surname</dt>
                        <dd class="col-sm-8">
                            {{ $staff->surname }}
                        </dd>

                        <dt class="col-sm-4">Regul8 Staff ID</dt>
                        <dd class="col-sm-8">
                            {{ $staff->regul8_staff_id ?? 'Not recorded' }}
                        </dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @if ($staff->active)
                                <span class="badge text-bg-success">
                                    Active
                                </span>
                            @else
                                <span class="badge text-bg-secondary">
                                    Inactive
                                </span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

        </div>
    </div>
</main>
@endsection