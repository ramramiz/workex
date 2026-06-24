@extends('layouts.app')

@section('title', 'Task Details')
@section('page-title', 'Task details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
    @if($task->project)
        <li class="breadcrumb-item"><a href="{{ route('projects.show', $task->project) }}">{{ $task->project->name }}</a></li>
    @endif
    <li class="breadcrumb-item active">{{ $task->title }}</li>
@endsection

@push('styles')
<style>
    .chat-container {
        background-color: #efeae2;
        background-image: radial-gradient(rgba(0,0,0,0.08) 1px, transparent 1px);
        background-size: 20px 20px;
        padding: 24px 16px;
        border-radius: 0; /* flat with card borders */
        max-height: 550px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 16px;
        border: none;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);
    }

    .chat-row {
        display: flex;
        width: 100%;
        align-items: flex-end;
        gap: 8px;
    }

    .chat-row.sent {
        justify-content: flex-end;
    }

    .chat-row.received {
        justify-content: flex-start;
    }

    .chat-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 2px;
    }

    .chat-bubble {
        max-width: 80%;
        min-width: 95px;
        position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        font-size: 13.5px;
        line-height: 1.45;
        padding: 8px 12px 22px 12px;
    }

    .chat-row.sent .chat-bubble {
        background-color: #d9fdd3;
        color: #111b21;
        border-radius: 14px 14px 0px 14px;
        border: 1px solid #c7ebc3;
    }

    .chat-row.received .chat-bubble {
        background-color: #ffffff;
        color: #111b21;
        border-radius: 14px 14px 14px 0px;
        border: 1px solid #e2e8f0;
    }

    .chat-sender {
        font-weight: 700;
        font-size: 11px;
        margin-bottom: 3px;
        display: block;
    }

    .chat-row.received .chat-sender {
        color: #0284c7;
    }

    .chat-row.sent .chat-sender {
        color: #16a34a;
    }

    .chat-meta {
        position: absolute;
        bottom: 3px;
        right: 8px;
        font-size: 9px;
        color: #667781;
        display: flex;
        align-items: center;
        gap: 3px;
        white-space: nowrap;
    }

    .chat-row.sent .chat-meta i {
        color: #53bdeb; /* WhatsApp blue ticks */
        font-size: 11px;
    }

    /* Time Log Bubble Details */
    .time-log-box {
        border-radius: 8px;
        padding: 10px 12px;
        margin-top: 4px;
        font-size: 12.5px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .chat-row.sent .time-log-box {
        background-color: rgba(255, 255, 255, 0.6);
        border-left: 4px solid #10b981;
    }

    .chat-row.received .time-log-box {
        background-color: #f8fafc;
        border-left: 4px solid #6366f1;
    }

    .time-log-header {
        font-weight: 700;
        font-size: 12px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .chat-row.sent .time-log-header {
        color: #10b981;
    }

    .chat-row.received .time-log-header {
        color: #6366f1;
    }

    .time-log-grid {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 4px 10px;
        line-height: 1.3;
    }

    .time-log-label {
        color: #64748b;
        font-weight: 500;
        font-size: 11.5px;
    }

    .time-log-value {
        font-weight: 600;
        color: #1e293b;
    }

    .time-log-note-section {
        margin-top: 8px;
        border-top: 1px dashed rgba(0, 0, 0, 0.08);
        padding-top: 8px;
    }

    .time-log-note-title {
        font-weight: 600;
        font-size: 11px;
        color: #64748b;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .time-log-note-content {
        font-style: italic;
        color: #334155;
        white-space: pre-wrap;
    }

    /* Pulse animation for active log */
    .pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #ef4444;
        border-radius: 50%;
        display: inline-block;
        animation: pulse-active 1.5s infinite;
    }

    /* WhatsApp Bottom Input Bar Styles */
    .whatsapp-input-bar {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        padding: 10px 16px;
        background-color: #f0f2f5;
        border-top: 1px solid #e2e8f0;
    }

    .whatsapp-input-container {
        flex: 1;
        background-color: #ffffff;
        border-radius: 24px;
        padding: 6px 16px;
        display: flex;
        align-items: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    }

    .whatsapp-input {
        flex: 1;
        border: none;
        outline: none;
        background: transparent;
        font-size: 14px;
        padding: 6px 0;
        resize: none;
        max-height: 100px;
        line-height: 1.4;
        color: #111b21;
    }

    .whatsapp-input::placeholder {
        color: #8696a0;
    }

    .whatsapp-send-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #00a884; /* WhatsApp green */
        color: #ffffff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.15);
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s;
        padding: 0;
        flex-shrink: 0;
    }

    .whatsapp-send-btn:hover {
        background-color: #008f72;
        transform: scale(1.05);
    }

    .whatsapp-send-btn:active {
        transform: scale(0.95);
    }

    @keyframes pulse-active {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(239, 68, 68, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    /* Mention Dropdown Styles */
    .mention-dropdown {
        position: absolute;
        bottom: 54px;
        left: 16px;
        z-index: 1000;
        width: 280px;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.15);
        max-height: 220px;
        overflow-y: auto;
        display: none;
    }

    .mention-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        cursor: pointer;
        transition: background-color 0.2s;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        text-align: left;
    }

    .mention-item:last-child {
        border-bottom: none;
    }

    .mention-item:hover, .mention-item.active {
        background-color: #f1f5f9;
    }

    .mention-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        object-fit: cover;
    }

    .mention-info {
        display: flex;
        flex-direction: column;
        line-height: 1.25;
    }

    .mention-name {
        font-weight: 600;
        color: #0f172a;
    }

    .mention-email {
        font-size: 11px;
        color: #64748b;
    }

    .chat-info-trigger {
        cursor: pointer;
        opacity: 0.6;
        transition: opacity 0.2s, color 0.2s;
    }
    .chat-info-trigger:hover {
        opacity: 1;
        color: #0284c7 !important;
    }

    .color-btn.active {
        outline: 2px solid #000000;
        outline-offset: 2px;
    }

    /* Status Dropdown Colors */
    .status-select-pending {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
        border-color: #dee2e6 !important;
    }
    .status-select-in_progress {
        background-color: #fff3cd !important;
        color: #664d03 !important;
        border-color: #ffecb5 !important;
    }
    .status-select-review {
        background-color: #cff4fc !important;
        color: #087990 !important;
        border-color: #b6effb !important;
    }
    .status-select-rework {
        background-color: #f8d7da !important;
        color: #842029 !important;
        border-color: #f5c2c7 !important;
    }
    .status-select-rejected {
        background-color: #f8d7da !important;
        color: #842029 !important;
        border-color: #f5c2c7 !important;
    }
    .status-select-completed {
        background-color: #d1e7dd !important;
        color: #0f5132 !important;
        border-color: #badbcc !important;
    }
    .status-select-cancelled {
        background-color: #e2e3e5 !important;
        color: #212529 !important;
        border-color: #d3d6d8 !important;
    }

    /* Style select options to have default look so they are legible */
    #taskStatusSelect option {
        background-color: #ffffff !important;
        color: #212529 !important;
    }

    @media (min-width: 992px) {
        .sticky-chat-card {
            position: sticky;
            top: 24px;
            height: calc(100vh - 120px);
            display: flex;
            flex-direction: column;
        }
        .sticky-chat-card .card-body {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            overflow: hidden;
            min-height: 0;
        }
        .sticky-chat-card .chat-container {
            max-height: none !important;
            flex-grow: 1;
            min-height: 0;
            overflow-y: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        <!-- Left Column: Task Details and Metadata -->
        <div class="col-lg-5 d-flex flex-column gap-4">
            
            <!-- Compact Title Card -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-4">
                    <div class="mb-2">
                        @if($task->project)
                            <span class="badge bg-primary-subtle text-primary fw-semibold" style="font-size: 11px;">
                                <i class="bi bi-folder2-open me-1"></i>{{ $task->project->name }}
                            </span>
                        @endif
                        @if($task->meeting)
                            <a href="{{ route('meetings.show', $task->meeting) }}" class="badge text-decoration-none fw-semibold ms-1" style="background: #f3e8ff; color: #7c3aed; border: 1px solid #e9d5ff; font-size: 11px;">
                                <i class="bi bi-chat-left-quote me-1"></i>meeting-{{ $task->meeting->meeting_date->format('Y-m-d') }}
                            </a>
                        @endif
                    </div>
                    <h3 class="fw-bold text-dark mb-0">{{ $task->title }}</h3>
                </div>
            </div>

            <!-- Task Metadata & Actions Card -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary mb-3" style="font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.05em;">Task Actions & Status</h6>
                    
                    <!-- Quick Actions -->
                    <div class="d-flex flex-column gap-2 mb-4">
                        @php
                            $activeLog = $task->timeLogs->where('user_id', auth()->id())->where('status', 'running')->first();
                            if (!$activeLog && (auth()->user()->isSuperAdmin() || auth()->user()->isTeamLeader() || auth()->user()->isAdmin())) {
                                $activeLog = $task->timeLogs->where('status', 'running')->first();
                            }
                            $isButtonsDisabled = $task->status === 'completed' || $task->status === 'review';
                        @endphp
                        
                        @if($task->status === 'review')
                            <div class="alert alert-warning py-2 px-3 fs-7 mb-2 text-dark" style="border-radius: 8px;">
                                <i class="bi bi-hourglass-split me-1"></i> This task is in review, kindly wait for admin review.
                            </div>
                        @endif

                        @if($activeLog)
                            <button type="button" class="btn btn-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#endTaskModal" {{ $isButtonsDisabled ? 'disabled' : '' }}>
                                <i class="bi bi-stop-fill me-1"></i> End Work
                            </button>
                        @else
                            <form method="POST" action="{{ route('work-timer.start-task', $task) }}" class="w-100">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm w-100" {{ $isButtonsDisabled ? 'disabled' : '' }}><i class="bi bi-play-fill me-1"></i> Start Work</button>
                            </form>
                            @if($task->status !== 'pending')
                                <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#taskCompletionModal" {{ $isButtonsDisabled ? 'disabled' : '' }}>
                                    <i class="bi bi-send-check me-1"></i> Submit for Review
                                </button>
                            @endif
                        @endif

                        <div class="d-flex gap-2 w-100">
                            <select name="status" class="form-select form-select-sm fw-semibold status-select-{{ $task->status }}" id="taskStatusSelect" onchange="updateTaskStatus()" {{ $isButtonsDisabled ? 'disabled' : '' }}>
                                <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="review" {{ $task->status === 'review' ? 'selected' : '' }}>Review</option>
                                <option value="rework" {{ $task->status === 'rework' ? 'selected' : '' }}>Rework</option>
                                <option value="rejected" {{ $task->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                @if($task->status === 'completed')
                                    <option value="completed" selected>Completed</option>
                                @endif
                                <option value="cancelled" {{ $task->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>

                            @if(auth()->user()->isAdminOrAbove() || auth()->user()->isTeamLeader())
                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-secondary btn-sm px-3" title="Edit Task"><i class="bi bi-pencil"></i></a>
                            @endif
                        </div>
                    </div>

                    <div id="statusAlert" class="mt-2 d-none alert alert-success py-1 px-2 fs-8 mb-3" style="font-size: 11px;">Status updated successfully!</div>

                    <hr class="my-3 text-muted opacity-25">

                    <h6 class="fw-bold text-secondary mb-3" style="font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.05em;">Information</h6>
                    
                    <div class="d-flex flex-column gap-3 fs-7">
                        <!-- Assignee -->
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted">Assigned To:</span>
                            @if($task->assignee)
                                <span class="fw-semibold text-dark d-flex align-items-center gap-2">
                                    <img src="{{ $task->assignee->avatar_url }}" alt="" class="avatar-circle" style="width: 22px; height: 22px; border-radius: 50%;">
                                    {{ $task->assignee->name }}
                                </span>
                            @else
                                <span class="fw-semibold text-dark">Unassigned</span>
                            @endif
                        </div>

                        <!-- Priority -->
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted">Priority:</span>
                            @php $badge = $task->priority_badge; @endphp
                            <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }} text-capitalize px-2 py-1 fs-8" style="font-size: 11px;">
                                {{ $task->priority }}
                            </span>
                        </div>

                        <!-- Deadline -->
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted">Deadline:</span>
                            <span class="fw-semibold text-dark {{ $task->is_delayed ? 'text-danger' : '' }}">
                                {{ $task->deadline ? $task->deadline->format('d M Y') : '—' }}
                            </span>
                        </div>

                        <!-- Time Logged -->
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted">Time Logged:</span>
                            <span class="fw-semibold text-success">
                                {{ number_format($totalMinutes / 60, 2) }} hrs
                                @if($task->estimated_hours)
                                    <span class="text-muted fs-8 fw-normal">/ {{ $task->estimated_hours }} hrs est.</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Description Card -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary mb-3" style="font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.05em;">Description</h6>
                    <div class="text-dark fs-7" style="white-space: pre-wrap; line-height: 1.6;">{{ $task->description ?? 'No description provided for this task.' }}</div>
                    <hr class="my-3 text-muted opacity-25">
                    <small class="text-muted">Created By: {{ $task->creator->name ?? 'System' }}</small>
                </div>
            </div>

            <!-- Task Attachments Card -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary mb-3" style="font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.05em;">Attachments ({{ $task->files->count() }})</h6>
                    
                    <!-- Upload Form -->
                    <form method="POST" action="{{ route('tasks.files.store', $task) }}" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="file" name="file" class="form-control" required>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
                        </div>
                    </form>

                    <!-- Files List -->
                    <div class="list-group list-group-flush border-top overflow-auto" style="max-height: 200px;">
                        @forelse($task->files as $file)
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center fs-7 border-0">
                                <div class="text-truncate" style="max-width: 180px;">
                                    <i class="bi bi-file-earmark-code me-1 text-primary"></i>
                                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="text-decoration-none text-dark fw-semibold">{{ $file->file_name }}</a>
                                </div>
                                <span class="text-muted fs-8">{{ number_format($file->file_size / 1024, 1) }} KB</span>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted fs-8">No files uploaded.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Task Submitted Completion Info Card -->
            @if($task->completed_description || $task->completed_link)
                <div class="card border-0 shadow-sm border-start border-4 border-success" style="border-radius: 12px; background: #ffffff;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-success"><i class="bi bi-patch-check-fill me-1"></i> Submitted Completion Info</span>
                            @if($task->completed_link)
                                <a href="{{ $task->completed_link }}" target="_blank" class="btn btn-outline-success btn-sm py-1 px-2 fs-8 text-decoration-none" style="font-size: 11px;">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Test Page Link
                                </a>
                            @endif
                        </div>
                        @if($task->completed_description)
                            <div class="text-secondary fs-7" style="white-space: pre-wrap;">{{ $task->completed_description }}</div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Column: Discussion & Chat -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm overflow-hidden sticky-chat-card" style="border-radius: 12px; background: #ffffff;">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-chat-dots me-2 text-primary"></i>Discussion / Comments</h5>
                </div>
                <div class="card-body p-0">
                    <!-- Unified Discussion & Time Logs Feed -->
                    <div class="chat-container">
                        @forelse($feed as $item)
                            @include('tasks.partials.feed_item', ['item' => $item])
                        @empty
                            <div class="text-center py-5 text-muted" id="no-chat-messages">
                                <i class="bi bi-chat-text" style="font-size: 32px;"></i>
                                <div class="mt-2 fs-7">No messages or time logs yet. Start the discussion!</div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Add Comment (WhatsApp Bar at Bottom) -->
                    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="whatsapp-input-bar" id="chat-form" style="position: relative;" enctype="multipart/form-data">
                        @csrf
                        <!-- Mention Dropdown -->
                        <div id="mention-list" class="mention-dropdown"></div>

                        <!-- Hidden input for base64 canvas image data -->
                        <input type="hidden" name="image_data" id="chat-image-data">

                        <!-- Attachment trigger -->
                        <label for="chat-image-input" class="btn btn-link text-muted p-0 m-0 d-flex align-items-center justify-content-center" style="font-size: 20px; width: 36px; height: 36px; cursor: pointer;" title="Attach Image">
                            <i class="bi bi-paperclip"></i>
                        </label>
                        <input type="file" id="chat-image-input" accept="image/*" style="display: none;">

                        <div class="whatsapp-input-container flex-column align-items-start py-2">
                            <!-- Attachment Preview -->
                            <div id="chat-attachment-preview" class="d-none mb-2 position-relative" style="width: 80px; height: 80px; border-radius: 8px; border: 1px solid #cbd5e1; overflow: visible; background-size: cover; background-position: center;">
                                <button type="button" id="chat-attachment-remove" class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center position-absolute" style="width: 20px; height: 20px; border-radius: 50%; top: -8px; right: -8px; font-size: 11px; z-index: 10;">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <textarea name="comment" id="whatsapp-comment-input" class="whatsapp-input w-100" placeholder="Type a message" rows="1" autocomplete="off"></textarea>
                        </div>
                        <button type="submit" class="whatsapp-send-btn">
                            <i class="bi bi-send-fill" style="margin-left: 2px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- End Task Modal -->
@if($activeLog)
    <div class="modal fade" id="endTaskModal" tabindex="-1" aria-labelledby="endTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog text-start">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header">
                    <h5 class="modal-title" id="endTaskModalLabel">End Work - Record Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('work-timer.end-task', $activeLog) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-semibold">Work Done Description <span class="text-danger">*</span></label>
                            <textarea name="note" class="form-control" rows="3" required placeholder="Describe what progress you made during this log..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm">Complete Time Log</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Task Completion Modal -->
<div class="modal fade" id="taskCompletionModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="taskCompletionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="taskCompletionModalLabel">Submit for Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('tasks.submit-completion', $task) }}">
                @csrf
                <div class="modal-body text-start pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Description of Work Done <span class="text-danger">*</span></label>
                        <textarea name="completed_description" class="form-control" rows="4" required placeholder="Describe what you completed, any details of the changes made, etc."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Page Link / Test URL</label>
                        <input type="url" name="completed_link" class="form-control" placeholder="https://example.com/test-page">
                        <div class="form-text text-muted fs-8">Provide the exact link where this change/feature can be verified.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Submit for Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection



<!-- Image Annotation Markup Modal -->
<div class="modal fade" id="imageMarkupModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="imageMarkupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="imageMarkupModalLabel">Annotate Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="markup-modal-close"></button>
            </div>
            <div class="modal-body text-center pt-2">
                <!-- Toolbar -->
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3 bg-light p-2 rounded-3 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-dark active" id="tool-pencil" title="Pencil Tool">
                        <i class="bi bi-pencil-fill me-1"></i> Pencil
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark" id="tool-circle" title="Circle Tool">
                        <i class="bi bi-circle me-1"></i> Circle
                    </button>
                    <div class="vr mx-2"></div>
                    <!-- Colors -->
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn active" data-color="#ef4444" style="width: 24px; height: 24px; background-color: #ef4444;" title="Red"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn" data-color="#3b82f6" style="width: 24px; height: 24px; background-color: #3b82f6;" title="Blue"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn" data-color="#22c55e" style="width: 24px; height: 24px; background-color: #22c55e;" title="Green"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn" data-color="#eab308" style="width: 24px; height: 24px; background-color: #eab308;" title="Yellow"></button>
                    <div class="vr mx-2"></div>
                    <button type="button" class="btn btn-sm btn-danger text-white" id="btn-clear-canvas">
                        <i class="bi bi-trash3-fill me-1"></i> Clear
                    </button>
                </div>
                
                <!-- Canvas Container -->
                <div class="d-flex justify-content-center align-items-center border rounded-3 bg-dark overflow-auto p-2" style="max-height: 400px; min-height: 250px;">
                    <canvas id="markup-canvas" style="cursor: crosshair; display: block; max-width: 100%; height: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></canvas>
                </div>

                <!-- Optional Comment input -->
                <div class="mt-3 text-start">
                    <label class="form-label fs-7 fw-semibold text-dark">Add message with image (optional)</label>
                    <input type="text" id="markup-comment-input" class="form-control form-control-sm" placeholder="Type a comment...">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm px-4" id="btn-send-markup">
                    <i class="bi bi-send-fill me-1"></i> Send Image
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Viewer Modal (for clicking images in chat) -->
<div class="modal fade" id="imageViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-header border-0 p-0 justify-content-end mb-2">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="image-viewer-img" src="" alt="" class="img-fluid rounded-3" style="max-height: 80vh; box-shadow: 0 4px 24px rgba(0,0,0,0.5);">
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script>
    function updateTaskStatus() {
        const select = document.getElementById('taskStatusSelect');
        const alertBox = document.getElementById('statusAlert');
        const status = select.value;
        
        if (status === 'completed') {
            select.value = "{{ $task->status }}";
            const modal = new bootstrap.Modal(document.getElementById('taskCompletionModal'));
            modal.show();
            return;
        }
        
        alertBox.classList.add('d-none');

        fetch(`/tasks/{{ $task->id }}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Auto-scroll chat container to bottom
    document.addEventListener('DOMContentLoaded', () => {
        const chatContainer = document.querySelector('.chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // Auto-resize textarea and submit on Enter
        const commentInput = document.getElementById('whatsapp-comment-input');
        if (commentInput) {
            commentInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            commentInput.addEventListener('keydown', function(e) {
                // If mention dropdown is open, let keydown handle it in its own listener
                const mentionList = document.getElementById('mention-list');
                if (mentionList && mentionList.style.display === 'block') {
                    return;
                }

                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const imageVal = document.getElementById('chat-image-data').value;
                    if (this.value.trim().length > 0 || imageVal) {
                        this.form.submit();
                    }
                }
            });

            commentInput.addEventListener('paste', function(e) {
                const items = (e.clipboardData || window.clipboardData || e.originalEvent.clipboardData).items;
                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') !== -1) {
                        const file = items[i].getAsFile();
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            const base64 = event.target.result;
                            document.getElementById('chat-image-data').value = base64;
                            
                            const preview = document.getElementById('chat-attachment-preview');
                            if (preview) {
                                preview.style.backgroundImage = `url(${base64})`;
                                preview.classList.remove('d-none');
                            }
                        };
                        reader.readAsDataURL(file);
                        e.preventDefault();
                        break;
                    }
                }
            });

            const removeBtn = document.getElementById('chat-attachment-remove');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    document.getElementById('chat-image-data').value = '';
                    const preview = document.getElementById('chat-attachment-preview');
                    if (preview) {
                        preview.classList.add('d-none');
                        preview.style.backgroundImage = '';
                    }
                });
            }
        }

        const chatForm = document.getElementById('chat-form');
        if (chatForm) {
            chatForm.addEventListener('submit', function(e) {
                const comment = document.getElementById('whatsapp-comment-input').value.trim();
                const imageData = document.getElementById('chat-image-data').value;
                if (!comment && !imageData) {
                    e.preventDefault();
                }
            });
        }

        // @mention functionality
        const staffList = {!! json_encode(\App\Models\User::where('status', 'active')->get()->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'avatar_url' => $u->avatar_url
            ];
        })) !!};

        const mentionList = document.getElementById('mention-list');
        let selectedIndex = 0;
        let filteredStaff = [];
        let mentionStartIndex = -1;

        if (commentInput && mentionList) {
            commentInput.addEventListener('input', function(e) {
                const text = this.value;
                const cursorPosition = this.selectionStart;
                
                // Find the last index of '@' before cursor
                const lastAtIndex = text.lastIndexOf('@', cursorPosition - 1);
                
                if (lastAtIndex !== -1) {
                    const textBetween = text.substring(lastAtIndex + 1, cursorPosition);
                    
                    if (!textBetween.includes(' ') && !textBetween.includes('\n')) {
                        mentionStartIndex = lastAtIndex;
                        showMentions(textBetween);
                        return;
                    }
                }
                
                hideMentions();
            });

            commentInput.addEventListener('keydown', function(e) {
                if (mentionList.style.display === 'block') {
                    const items = mentionList.querySelectorAll('.mention-item');
                    
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        selectedIndex = (selectedIndex + 1) % items.length;
                        updateActiveItem(items);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                        updateActiveItem(items);
                    } else if (e.key === 'Enter' || e.key === 'Tab') {
                        if (items.length > 0) {
                            e.preventDefault();
                            selectMention(items[selectedIndex].dataset.name);
                        }
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        hideMentions();
                    }
                }
            });
        }

        function showMentions(query) {
            filteredStaff = staffList.filter(u => 
                u.name.toLowerCase().includes(query.toLowerCase()) || 
                u.email.toLowerCase().includes(query.toLowerCase())
            );

            if (filteredStaff.length === 0) {
                hideMentions();
                return;
            }

            mentionList.innerHTML = '';
            selectedIndex = 0;

            filteredStaff.forEach((u, index) => {
                const div = document.createElement('div');
                div.className = `mention-item ${index === 0 ? 'active' : ''}`;
                div.dataset.name = u.name;
                div.dataset.index = index;
                div.innerHTML = `
                    <img src="${u.avatar_url}" class="mention-avatar" alt="">
                    <div class="mention-info">
                        <span class="mention-name">${u.name}</span>
                        <span class="mention-email">${u.email}</span>
                    </div>
                `;
                
                div.addEventListener('click', function() {
                    selectMention(this.dataset.name);
                });
                
                mentionList.appendChild(div);
            });

            mentionList.style.display = 'block';
        }

        function updateActiveItem(items) {
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.classList.add('active');
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('active');
                }
            });
        }

        function selectMention(name) {
            const text = commentInput.value;
            const cursorPosition = commentInput.selectionStart;
            
            const beforeMention = text.substring(0, mentionStartIndex);
            const afterMention = text.substring(cursorPosition);
            
            // Insert name with a trailing space
            commentInput.value = beforeMention + '@' + name + ' ' + afterMention;
            
            // Put cursor after the newly inserted mention
            const newCursorPos = mentionStartIndex + name.length + 2; // +2 for @ and trailing space
            commentInput.setSelectionRange(newCursorPos, newCursorPos);
            commentInput.focus();
            
            hideMentions();
            
            // Trigger textarea resize
            commentInput.dispatchEvent(new Event('input', { bubbles: true }));
        }

        function hideMentions() {
            mentionList.style.display = 'none';
            filteredStaff = [];
            mentionStartIndex = -1;
        }



        // Image Viewer Modal setup
        const imageViewerModal = document.getElementById('imageViewerModal');
        if (imageViewerModal) {
            imageViewerModal.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                const src = trigger.getAttribute('data-src');
                document.getElementById('image-viewer-img').setAttribute('src', src);
            });
        }

        // Image Markup & Canvas Drawing Logic
        const chatImageInput = document.getElementById('chat-image-input');
        const canvas = document.getElementById('markup-canvas');
        const ctx = canvas ? canvas.getContext('2d') : null;
        
        let drawing = false;
        let currentTool = 'pencil'; // pencil or circle
        let currentColor = '#ef4444'; // default red
        let currentLineWidth = 3;
        let startX = 0;
        let startY = 0;
        let savedImageData = null;
        let loadedImg = null;

        if (chatImageInput && canvas && ctx) {
            chatImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        loadedImg = new Image();
                        loadedImg.onload = function() {
                            const maxDimension = 600;
                            let width = loadedImg.width;
                            let height = loadedImg.height;
                            if (width > maxDimension || height > maxDimension) {
                                if (width > height) {
                                    height = Math.round((height * maxDimension) / width);
                                    width = maxDimension;
                                } else {
                                    width = Math.round((width * maxDimension) / height);
                                    height = maxDimension;
                                }
                            }
                            canvas.width = width;
                            canvas.height = height;

                            ctx.drawImage(loadedImg, 0, 0, width, height);

                            const markupModalEl = document.getElementById('imageMarkupModal');
                            const modal = new bootstrap.Modal(markupModalEl);
                            modal.show();
                        };
                        loadedImg.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            function getCoordinates(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: (clientX - rect.left) * (canvas.width / rect.width),
                    y: (clientY - rect.top) * (canvas.height / rect.height)
                };
            }

            function startDrawing(e) {
                drawing = true;
                savedImageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const coords = getCoordinates(e);
                startX = coords.x;
                startY = coords.y;

                if (currentTool === 'pencil') {
                    ctx.beginPath();
                    ctx.moveTo(startX, startY);
                    ctx.strokeStyle = currentColor;
                    ctx.lineWidth = currentLineWidth;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                }
            }

            function draw(e) {
                if (!drawing) return;
                
                if (e.touches) {
                    e.preventDefault();
                }

                const coords = getCoordinates(e);
                const x = coords.x;
                const y = coords.y;

                if (currentTool === 'pencil') {
                    ctx.lineTo(x, y);
                    ctx.stroke();
                } else if (currentTool === 'circle') {
                    ctx.putImageData(savedImageData, 0, 0);
                    const radius = Math.sqrt(Math.pow(x - startX, 2) + Math.pow(y - startY, 2));
                    ctx.beginPath();
                    ctx.arc(startX, startY, radius, 0, 2 * Math.PI);
                    ctx.strokeStyle = currentColor;
                    ctx.lineWidth = currentLineWidth;
                    ctx.stroke();
                }
            }

            function stopDrawing() {
                if (drawing) {
                    drawing = false;
                    if (currentTool === 'pencil') {
                        ctx.closePath();
                    }
                }
            }

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseleave', stopDrawing);

            canvas.addEventListener('touchstart', startDrawing, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            canvas.addEventListener('touchend', stopDrawing);

            const pencilBtn = document.getElementById('tool-pencil');
            const circleBtn = document.getElementById('tool-circle');

            if (pencilBtn && circleBtn) {
                pencilBtn.addEventListener('click', function() {
                    currentTool = 'pencil';
                    pencilBtn.classList.add('active');
                    circleBtn.classList.remove('active');
                });

                circleBtn.addEventListener('click', function() {
                    currentTool = 'circle';
                    circleBtn.classList.add('active');
                    pencilBtn.classList.remove('active');
                });
            }

            const colorBtns = document.querySelectorAll('.color-btn');
            colorBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    colorBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentColor = this.getAttribute('data-color');
                });
            });

            const clearBtn = document.getElementById('btn-clear-canvas');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    if (loadedImg) {
                        ctx.drawImage(loadedImg, 0, 0, canvas.width, canvas.height);
                    }
                });
            }

            const sendMarkupBtn = document.getElementById('btn-send-markup');
            if (sendMarkupBtn) {
                sendMarkupBtn.addEventListener('click', function() {
                    const base64 = canvas.toDataURL('image/jpeg', 0.85);
                    document.getElementById('chat-image-data').value = base64;

                    const commentInput = document.getElementById('whatsapp-comment-input');
                    const markupComment = document.getElementById('markup-comment-input').value;
                    commentInput.value = markupComment;

                    const modalEl = document.getElementById('imageMarkupModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }

                    chatImageInput.value = '';

                    document.getElementById('chat-form').submit();
                });
            }

            const modalEl = document.getElementById('imageMarkupModal');
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function () {
                    chatImageInput.value = '';
                    document.getElementById('markup-comment-input').value = '';
                });
            }            // Polling chat updates
            let latestFeedTime = "{{ $feed->count() > 0 ? $feed->last()->created_at->toISOString() : now()->toISOString() }}";
            let lastCommentId = {{ $feed->where('feed_type', 'comment')->count() > 0 ? $feed->where('feed_type', 'comment')->last()->id : 'null' }};
            let lastTimelogId = {{ $feed->where('feed_type', 'time_log')->count() > 0 ? $feed->where('feed_type', 'time_log')->last()->id : 'null' }};
            
            const pollChatUpdates = () => {
                let url = `/tasks/{{ $task->id }}/feed-updates?since=` + encodeURIComponent(latestFeedTime);
                if (lastCommentId) {
                    url += "&last_comment_id=" + lastCommentId;
                }
                if (lastTimelogId) {
                    url += "&last_timelog_id=" + lastTimelogId;
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.has_updates) {
                            latestFeedTime = data.latest_time;
                            lastCommentId = data.last_comment_id;
                            lastTimelogId = data.last_timelog_id;
                            
                            const chatContainer = document.querySelector('.chat-container');
                            if (chatContainer) {
                                const noMessagesEl = document.getElementById('no-chat-messages');
                                if (noMessagesEl) {
                                    noMessagesEl.remove();
                                }
                                
                                const isNearBottom = chatContainer.scrollHeight - chatContainer.clientHeight - chatContainer.scrollTop < 100;
                                
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = data.html;
                                const newRows = tempDiv.querySelectorAll('.chat-row');
                                let appendedAny = false;
                                let playSound = false;
                                let hasSentMessage = false;
                                
                                newRows.forEach(row => {
                                     if (!document.getElementById(row.id)) {
                                         chatContainer.appendChild(row);
                                         appendedAny = true;
                                         if (row.classList.contains('received')) {
                                             playSound = true;
                                         }
                                         if (row.classList.contains('sent')) {
                                             hasSentMessage = true;
                                         }
                                     }
                                 });
                                 
                                 if (appendedAny) {
                                     if (isNearBottom || hasSentMessage) {
                                         chatContainer.scrollTop = chatContainer.scrollHeight;
                                     }
                                     
                                     if (playSound && typeof window.playNotificationSound === 'function') {
                                         window.playNotificationSound();
                                     }
                                 }
                            }
                        }
                    })
                    .catch(error => console.error('Error polling chat updates:', error));
            };
 
            // Poll chat updates every 5 seconds
            setInterval(pollChatUpdates, 5000);
        }
    });
</script>
@endpush
