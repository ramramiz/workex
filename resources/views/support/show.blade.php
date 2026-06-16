@extends('layouts.app')

@section('title', 'Ticket Details')
@section('page-title', 'Ticket Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('support.index') }}">Support</a></li>
    <li class="breadcrumb-item active">{{ $support->ticket_number }}</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Description & Chat Feed -->
    <div class="col-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Ticket Details</h5>
                @if(auth()->user()->isAdminOrAbove() || auth()->user()->isTeamLeader())
                    <a href="{{ route('support.edit', $support) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit Ticket</a>
                @endif
            </div>
            <div class="card-body">
                <h3 class="fw-bold mb-1">{{ $support->title }}</h3>
                <span class="fs-7 text-muted font-monospace d-block mb-3">Ticket: {{ $support->ticket_number }}</span>
                
                <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2">Description / Client Report</h6>
                <div class="bg-light p-3 rounded mb-4 text-dark fs-7" style="white-space: pre-wrap;">{{ $support->description }}</div>
            </div>
        </div>

        <!-- Conversation Timeline -->
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Reply Thread</h5></div>
            <div class="card-body">
                @if($support->status !== 'closed')
                    <form method="POST" action="{{ route('support.reply', $support) }}" class="mb-4">
                        @csrf
                        <div class="mb-2">
                            <textarea name="message" class="form-control" rows="3" required placeholder="Type your reply or response here..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm">Submit Reply</button>
                        </div>
                    </form>
                    <hr class="my-4">
                @endif

                <div class="comment-feed">
                    @forelse($support->replies as $reply)
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom border-light">
                            <img src="{{ $reply->user->avatar_url }}" alt="" class="avatar-circle" style="width:36px; height:36px;">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark fs-7">{{ $reply->user->name }}</span>
                                    <small class="text-muted" style="font-size:11px;">{{ $reply->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="text-muted fs-7 mb-0 mt-1" style="white-space: pre-wrap;">{{ $reply->message }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-chat-right-text" style="font-size: 32px;"></i>
                            <div class="mt-2 fs-7">No replies recorded yet.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Status & AMC Validity Info -->
    <div class="col-12 col-lg-4">
        <!-- Ticket Meta Widget -->
        <div class="card mb-4 border border-light">
            <div class="card-header bg-white"><h6 class="mb-0">Ticket Status</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Status</small>
                    @if($support->status === 'open')
                        <span class="badge bg-info-subtle text-info border border-info-subtle fs-7 mt-1">Open</span>
                    @elseif($support->status === 'in_progress')
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-7 mt-1">In Progress</span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle fs-7 mt-1">Closed</span>
                    @endif
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Priority</small>
                    <span class="badge bg-{{ $support->priority === 'critical' || $support->priority === 'high' ? 'danger' : ($support->priority === 'medium' ? 'warning' : 'secondary') }}-subtle text-{{ $support->priority === 'critical' || $support->priority === 'high' ? 'danger' : ($support->priority === 'medium' ? 'warning' : 'secondary') }} border border-{{ $support->priority === 'critical' || $support->priority === 'high' ? 'danger' : ($support->priority === 'medium' ? 'warning' : 'secondary') }}-subtle text-capitalize mt-1">
                        {{ $support->priority }}
                    </span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Client Account</small>
                    <span class="fw-semibold text-dark fs-7">{{ $support->client->company_name ?? 'Internal / General' }}</span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Assigned Representative</small>
                    @if($support->assignedTo)
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <img src="{{ $support->assignedTo->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                            <span class="fw-medium text-dark fs-7">{{ $support->assignedTo->name }}</span>
                        </div>
                    @else
                        <span class="text-muted fs-7">Unassigned</span>
                    @endif
                </div>

                @if($support->status !== 'closed' && (auth()->user()->isAdminOrAbove() || auth()->user()->isTeamLeader()))
                    <hr>
                    <form method="POST" action="{{ route('support.close', $support) }}" class="d-grid mt-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> Close Ticket</button>
                    </form>
                @endif
            </div>
        </div>

        <!-- AMC Validity Widget -->
        @if($support->amc_end_date)
            <div class="card border border-light">
                <div class="card-header bg-white"><h6 class="mb-0">AMC Contract Details</h6></div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Contract Start</small>
                        <span class="fw-semibold text-dark fs-7">{{ \Carbon\Carbon::parse($support->amc_start_date)->format('d M Y') }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Contract End</small>
                        @php
                            $expired = \Carbon\Carbon::parse($support->amc_end_date)->isPast();
                        @endphp
                        <span class="fw-semibold fs-7 {{ $expired ? 'text-danger' : 'text-success' }}">
                            {{ \Carbon\Carbon::parse($support->amc_end_date)->format('d M Y') }}
                            @if($expired)
                                <small class="d-block text-danger mt-1"><i class="bi bi-exclamation-octagon"></i> Contract Expired</small>
                            @else
                                <small class="d-block text-success mt-1"><i class="bi bi-shield-check"></i> Contract Active</small>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
