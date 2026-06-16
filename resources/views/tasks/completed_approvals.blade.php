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
                                
                                <form method="POST" action="{{ route('tasks.approve-completion', $task) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this completion and close this task?');">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Approve and Close Task">
                                        <i class="bi bi-check-lg me-1"></i> Approve
                                    </button>
                                </form>

                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#reworkModal-{{ $task->id }}" title="Reject and Send Back for Rework">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reject
                                </button>
                            </div>

                            <!-- Rejection/Rework Modal -->
                            <div class="modal fade" id="reworkModal-{{ $task->id }}" data-bs-backdrop="static" tabindex="-1" aria-labelledby="reworkModalLabel-{{ $task->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered text-start">
                                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold text-dark" id="reworkModalLabel-{{ $task->id }}">Send Task Back for Rework</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="{{ route('tasks.reject-completion', $task) }}">
                                            @csrf
                                            <div class="modal-body pt-3">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-dark">Rework Feedback / Reason <span class="text-danger">*</span></label>
                                                    <textarea name="comment" class="form-control" rows="4" required placeholder="Specify exactly what failed testing or needs changes..."></textarea>
                                                    <div class="form-text text-muted fs-8">This feedback will be posted directly as a comment in the task discussion history.</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger btn-sm px-4">Send Back to Rework</button>
                                            </div>
                                        </form>
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
