@extends('layouts.app')

@section('title', 'Approve Completed Work')
@section('page-title', 'Approve Completed Work')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active">Completed Work Approvals</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Completed Work Approvals Queue</h5>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-3">Task Name</th>
                    <th>Project</th>
                    <th>Developer</th>
                    <th>Submission Details</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold">
                                <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none text-dark">{{ $task->title }}</a>
                            </div>
                            <span class="badge bg-info-subtle text-info border border-info-subtle mt-1">Pending Approval</span>
                        </td>
                        <td class="fs-7 text-secondary">
                            {{ $task->project->name ?? '—' }}
                        </td>
                        <td>
                            @if($task->assignee)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $task->assignee->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <span class="fs-7">{{ $task->assignee->name }}</span>
                                </div>
                            @else
                                <span class="text-muted fs-7">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            @if($task->completed_description)
                                <div class="text-secondary fs-7 text-wrap" style="max-width: 300px;">
                                    <strong>Description:</strong> {{ Str::limit($task->completed_description, 100) }}
                                </div>
                            @endif
                            @if($task->completed_link)
                                <div class="mt-1 fs-8">
                                    <i class="bi bi-link-45deg text-primary"></i>
                                    <a href="{{ $task->completed_link }}" target="_blank" class="text-decoration-none text-primary fw-semibold">Test Link</a>
                                </div>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-2 align-items-center">
                                @if($task->completed_link)
                                    <a href="{{ $task->completed_link }}" target="_blank" class="btn btn-outline-info btn-sm" title="Open test link">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Test Link
                                    </a>
                                @endif
                                
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approvalModal-{{ $task->id }}" onclick="toggleRejectSection({{ $task->id }}, false)" title="Approve Task">
                                    <i class="bi bi-check-lg me-1"></i> Approve
                                </button>

                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#approvalModal-{{ $task->id }}" onclick="toggleRejectSection({{ $task->id }}, true)" title="Reject Task">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reject
                                </button>
                            </div>

                            <!-- Unified Review & Approval Modal -->
                            <div class="modal fade" id="approvalModal-{{ $task->id }}" data-bs-backdrop="static" tabindex="-1" aria-labelledby="approvalModalLabel-{{ $task->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered text-start">
                                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold text-dark" id="approvalModalLabel-{{ $task->id }}">Review Completion: {{ $task->title }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        
                                        <div class="modal-body pt-3">
                                            <div class="mb-3">
                                                <span class="d-block text-muted fs-8 text-uppercase fw-semibold">Developer</span>
                                                <span class="text-dark fw-medium fs-7">{{ $task->assignee->name ?? 'Unassigned' }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <span class="d-block text-muted fs-8 text-uppercase fw-semibold">Project</span>
                                                <span class="text-dark fw-medium fs-7">{{ $task->project->name ?? '—' }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <span class="d-block text-muted fs-8 text-uppercase fw-semibold">Description of Work Done</span>
                                                <div class="p-3 bg-light rounded-3 text-dark fs-7" style="white-space: pre-wrap;">{{ $task->completed_description ?? 'No description provided.' }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <span class="d-block text-muted fs-8 text-uppercase fw-semibold">Completed Page Link</span>
                                                @if($task->completed_link)
                                                    <a href="{{ $task->completed_link }}" target="_blank" class="text-decoration-none text-primary fw-semibold fs-7 break-all">
                                                        <i class="bi bi-link-45deg"></i> {{ $task->completed_link }}
                                                    </a>
                                                @else
                                                    <span class="text-muted fs-7">—</span>
                                                @endif
                                            </div>

                                            <!-- Hidden Rejection Form Section -->
                                            <div id="rejectSection-{{ $task->id }}" class="d-none border-top pt-3 mt-3">
                                                <form method="POST" action="{{ route('tasks.reject-completion', $task) }}" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold text-danger">Rework Feedback / Reason <span class="text-danger">*</span></label>
                                                        <textarea name="comment" id="rejectComment-{{ $task->id }}" class="form-control" rows="3" placeholder="Specify exactly what needs changes..."></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold text-dark">Attach Image (optional)</label>
                                                        <input type="file" name="image" class="form-control" accept="image/*">
                                                    </div>
                                                    <div class="d-flex gap-2 justify-content-end">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleRejectSection({{ $task->id }}, false)">Back</button>
                                                        <button type="submit" class="btn btn-danger btn-sm px-4">Submit Rejection</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Default Footer with Approve / Reject Options -->
                                        <div class="modal-footer border-0" id="defaultFooter-{{ $task->id }}">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="toggleRejectSection({{ $task->id }}, true)">Reject</button>
                                            
                                            <form method="POST" action="{{ route('tasks.approve-completion', $task) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm px-4">Approve</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-patch-check" style="font-size: 32px;"></i>
                            <div class="mt-2">No tasks are currently pending approval.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tasks->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleRejectSection(taskId, show) {
    const section = document.getElementById('rejectSection-' + taskId);
    const footer = document.getElementById('defaultFooter-' + taskId);
    const textarea = document.getElementById('rejectComment-' + taskId);
    if (show) {
        section.classList.remove('d-none');
        footer.classList.add('d-none');
        textarea.setAttribute('required', 'required');
        textarea.focus();
    } else {
        section.classList.add('d-none');
        footer.classList.remove('d-none');
        textarea.removeAttribute('required');
    }
}
</script>
@endpush
