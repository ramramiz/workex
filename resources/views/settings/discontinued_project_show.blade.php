@extends('layouts.app')

@section('title', 'Discontinued Project Details')
@section('page-title', 'Discontinued Project Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item"><a href="{{ route('settings.discontinued-projects') }}">Discontinued Projects</a></li>
    <li class="breadcrumb-item active">{{ $project->name }}</li>
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
        <div class="card border border-light-subtle shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-bar-graph text-secondary"></i> Project Metadata
                </h5>
                <form method="POST" action="{{ route('settings.discontinued-projects.reactivate', $project->id) }}" onsubmit="return confirm('Are you sure you want to reactivate this project?')">
                    @csrf
                    <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-1 btn-sm">
                        <i class="bi bi-arrow-counterclockwise"></i> Reactivate Project
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    {{-- Logo + Name Info --}}
                    <div class="col-12 d-flex align-items-center gap-3 pb-3 border-bottom border-light-subtle">
                        @if($project->logo_path)
                            <img src="{{ asset('storage/' . $project->logo_path) }}" alt="{{ $project->name }}" class="rounded p-2 bg-white border" style="width: 60px; height: 60px; object-fit: contain;">
                        @else
                            <div class="bg-secondary bg-opacity-10 text-secondary rounded d-flex align-items-center justify-content-center fw-bold" style="width: 60px; height: 60px; font-size: 24px;">
                                {{ strtoupper(substr($project->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $project->name }}</h4>
                            <span class="badge bg-secondary">Discontinued</span>
                            <span class="text-muted ms-2">Code: <strong>{{ $project->project_code }}</strong></span>
                        </div>
                    </div>

                    {{-- General Details Grid --}}
                    <div class="col-12 col-md-6">
                        <table class="table table-sm table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 140px;">Client:</td>
                                    <td class="fw-semibold text-dark">{{ $project->client?->company_name ?? 'Internal Project' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Team Leader:</td>
                                    <td class="fw-semibold text-dark">{{ $project->teamLeader?->name ?? 'None' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Project Type:</td>
                                    <td class="fw-semibold text-dark">{{ ucwords($project->project_type ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Budget:</td>
                                    <td class="fw-semibold text-success">₹{{ number_format($project->project_value, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-12 col-md-6">
                        <table class="table table-sm table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 140px;">Start Date:</td>
                                    <td class="fw-semibold text-dark">{{ $project->start_date ? $project->start_date->format('d M Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Deadline:</td>
                                    <td class="fw-semibold text-dark">{{ $project->deadline ? $project->deadline->format('d M Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Discontinued At:</td>
                                    <td class="fw-semibold text-dark">{{ $project->updated_at->format('d M Y H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($project->description)
                        <div class="col-12 pt-3 border-top">
                            <h6 class="fw-bold text-dark">Description:</h6>
                            <p class="text-secondary mb-0" style="white-space: pre-wrap;">{{ $project->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tasks summary --}}
        <div class="card border border-light-subtle shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-list-task text-secondary"></i> Project Tasks List
                </h5>
            </div>
            <div class="card-body">
                @if($project->tasks->isEmpty())
                    <p class="text-muted mb-0 text-center py-3">No tasks created for this project.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Task Title</th>
                                    <th>Assignee</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->tasks as $task)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $task->title }}</span>
                                        </td>
                                        <td>
                                            {{ $task->assignee?->name ?? 'Unassigned' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->status === 'completed' ? 'success' : 'warning' }}">
                                                {{ ucfirst($task->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $task->due_date ? $task->due_date->format('d M Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
