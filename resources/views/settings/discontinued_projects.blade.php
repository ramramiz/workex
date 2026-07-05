@extends('layouts.app')

@section('title', 'Discontinued Projects')
@section('page-title', 'Discontinued Projects')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Discontinued Projects</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Navigation Sidebar for Settings -->
    <div class="col-12 col-md-3">
        <div class="card">
            @include('settings.sidebar')
        </div>
    </div>

    <!-- Right Content Card -->
    <div class="col-12 col-md-9">
        <div class="card border border-light-subtle shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-folder-x text-secondary"></i> Discontinued Projects
                </h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($projects->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-folder-check display-4 text-light d-block mb-3"></i>
                        <p class="mb-0">No discontinued projects found.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Project</th>
                                    <th>Client</th>
                                    <th>Budget</th>
                                    <th>Discontinued Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $p)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($p->logo_path)
                                                    <img src="{{ asset('storage/' . $p->logo_path) }}" alt="{{ $p->name }}" class="rounded" style="width: 32px; height: 32px; object-fit: contain; padding: 2px; border: 1px solid #dee2e6;">
                                                @else
                                                    <div class="bg-secondary bg-opacity-10 text-secondary rounded d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                                        {{ strtoupper(substr($p->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="fw-semibold d-block text-dark">{{ $p->name }}</span>
                                                    <small class="text-muted">{{ $p->project_code }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $p->client?->company_name ?? 'Internal Project' }}
                                        </td>
                                        <td>
                                            ₹{{ number_format($p->project_value, 2) }}
                                        </td>
                                        <td>
                                            {{ $p->updated_at->format('d M Y') }}
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <a href="{{ route('settings.discontinued-projects.show', $p->id) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-eye"></i> View Details
                                                </a>
                                                <form method="POST" action="{{ route('settings.discontinued-projects.reactivate', $p->id) }}" onsubmit="return confirm('Are you sure you want to reactivate this project?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Reactivate
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $projects->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
