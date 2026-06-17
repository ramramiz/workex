@extends('layouts.app')

@section('title', 'Bug Details')
@section('page-title', 'Bug Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('bugs.index') }}">Bugs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projects.show', $bug->project) }}">{{ $bug->project->name }}</a></li>
    <li class="breadcrumb-item active">{{ $bug->title }}</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Bug Details & Comments -->
    <div class="col-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Bug Info</h5>
                <a href="{{ route('bugs.edit', $bug) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit Bug</a>
            </div>
            <div class="card-body">
                <h3 class="fw-bold mb-1 text-danger">{{ $bug->title }}</h3>
                <p class="text-muted fs-7 mb-4">Project: <a href="{{ route('projects.show', $bug->project) }}" class="fw-semibold text-decoration-none">{{ $bug->project->name }}</a></p>

                <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2">Steps to Reproduce</h6>
                <div class="bg-light p-3 rounded mb-4 text-dark fs-7" style="white-space: pre-wrap;">{{ $bug->steps_to_reproduce ?? 'No reproduction steps provided.' }}</div>

                <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2">Detailed Description</h6>
                <div class="bg-light p-3 rounded mb-4 text-muted fs-7" style="white-space: pre-wrap;">{{ $bug->description ?? 'No description provided.' }}</div>

                @if($bug->link)
                    <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2">Related Link / URL</h6>
                    <div class="bg-light p-3 rounded mb-4 fs-7">
                        <a href="{{ str_starts_with($bug->link, 'http') ? $bug->link : 'http://' . $bug->link }}" target="_blank" class="text-primary fw-semibold text-decoration-none">
                            <i class="bi bi-link-45deg me-1"></i>{{ $bug->link }}
                        </a>
                    </div>
                @endif

                @if($bug->screenshots && count($bug->screenshots) > 0)
                    <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2">Screenshots / Reference Images</h6>
                    <div class="row g-3 mb-4">
                        @foreach($bug->screenshots as $screenshot)
                            <div class="col-6 col-md-4">
                                <a href="{{ asset('storage/' . $screenshot) }}" target="_blank" class="d-block border rounded overflow-hidden shadow-xs hover-shadow transition-all duration-200">
                                    <img src="{{ asset('storage/' . $screenshot) }}" alt="Bug Screenshot" class="img-fluid" style="height: 120px; width: 100%; object-fit: cover;">
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

                <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2">Environment</h6>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted d-block fs-8">Browser</small>
                            <span class="fw-semibold text-dark fs-7"><i class="bi bi-browser-chrome me-1"></i> {{ $bug->browser_info ?? 'Unknown' }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted d-block fs-8">Operating System</small>
                            <span class="fw-semibold text-dark fs-7"><i class="bi bi-laptop me-1"></i> {{ $bug->os_info ?? 'Unknown' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Discussion Comments Feed -->
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Discussion / Status Updates</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('bugs.comments.store', $bug) }}" class="mb-4">
                    @csrf
                    <div class="mb-2">
                        <textarea name="comment" class="form-control" rows="3" required placeholder="Post update or fix comment..."></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="comment-feed">
                    @forelse($bug->comments as $com)
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom border-light">
                            <img src="{{ $com->user->avatar_url }}" alt="" class="avatar-circle" style="width:36px; height:36px;">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark fs-7">{{ $com->user->name }}</span>
                                    <small class="text-muted" style="font-size:11px;">{{ $com->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="text-muted fs-7 mb-0 mt-1" style="white-space: pre-wrap;">{{ $com->comment }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-chat-left-dots" style="font-size: 32px;"></i>
                            <div class="mt-2 fs-7">No comments yet.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Status & Assignment -->
    <div class="col-12 col-lg-4">
        <!-- Status Widget -->
        <div class="card mb-4 border border-light">
            <div class="card-header bg-white"><h6 class="mb-0">Bug Status & Priority</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fs-7">Bug Status</label>
                    <select name="status" class="form-select form-select-sm" id="bugStatusSelect" onchange="updateBugStatus()">
                        <option value="open" {{ $bug->status === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="assigned" {{ $bug->status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="in_progress" {{ $bug->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ $bug->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ $bug->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="rejected" {{ $bug->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <div id="statusAlert" class="mt-2 d-none alert alert-success py-1 px-2 fs-8" style="font-size: 11px;">Bug status updated!</div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Priority</small>
                    <span class="badge bg-{{ $bug->priority === 'critical' || $bug->priority === 'high' ? 'danger' : ($bug->priority === 'medium' ? 'warning' : 'secondary') }}-subtle text-{{ $bug->priority === 'critical' || $bug->priority === 'high' ? 'danger' : ($bug->priority === 'medium' ? 'warning' : 'secondary') }} border border-{{ $bug->priority === 'critical' || $bug->priority === 'high' ? 'danger' : ($bug->priority === 'medium' ? 'warning' : 'secondary') }}-subtle text-capitalize mt-1">
                        {{ $bug->priority }}
                    </span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Reported By</small>
                    <span class="fw-semibold text-dark fs-7">{{ $bug->reportedBy->name ?? 'System' }}</span>
                </div>

                <div>
                    <small class="text-muted d-block">Assigned Developer</small>
                    @if($bug->assignedTo)
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <img src="{{ $bug->assignedTo->avatar_url }}" alt="" class="avatar-circle" style="width: 28px; height: 28px;">
                            <span class="fw-medium text-dark fs-7">{{ $bug->assignedTo->name }}</span>
                        </div>
                    @else
                        <span class="text-muted fs-7">Unassigned</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateBugStatus() {
        const select = document.getElementById('bugStatusSelect');
        const alertBox = document.getElementById('statusAlert');
        const status = select.value;
        
        alertBox.classList.add('d-none');

        fetch("{{ route('bugs.update-status', $bug) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alertBox.classList.remove('d-none');
                setTimeout(() => alertBox.classList.add('d-none'), 3000);
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
@endpush
