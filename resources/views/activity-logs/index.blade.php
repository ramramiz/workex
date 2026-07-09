@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-title', 'System Activity Logs')

@section('breadcrumb')
    <li class="breadcrumb-item active">Activity Logs</li>
@endsection

@php
    $users = \App\Models\User::orderBy('name')->get();
    $actions = \App\Models\ActivityLog::select('action')->distinct()->pluck('action');
@endphp

@section('content')
<!-- Filters -->
<div class="card border border-light shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-funnel-fill text-primary"></i> Filter Audit Logs</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-3">
            <input type="hidden" name="log_type" value="{{ $logType }}">
            <!-- User -->
            <div class="col-12 col-md-3">
                <label class="form-label fs-7 fw-semibold text-secondary">User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->role?->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action -->
            <div class="col-12 col-md-3">
                <label class="form-label fs-7 fw-semibold text-secondary">Action</label>
                <select name="action" class="form-select form-select-sm text-capitalize">
                    <option value="">All Actions</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', $act) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div class="col-12 col-md-2">
                <label class="form-label fs-7 fw-semibold text-secondary">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>

            <!-- Date To -->
            <div class="col-12 col-md-2">
                <label class="form-label fs-7 fw-semibold text-secondary">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>

            <!-- Filter Buttons -->
            <div class="col-12 col-md-2 d-grid mt-md-4 pt-md-2">
                <div class="btn-group btn-group-sm">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-filter"></i> Filter</button>
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Log Type Tabs -->
<div class="d-flex justify-content-start mb-3">
    <ul class="nav nav-pills" style="background: #f1f5f9; padding: 4px; border-radius: 10px;">
        <li class="nav-item">
            <a class="nav-link {{ $logType === 'all' ? 'active' : '' }} px-3 py-1.5 fw-semibold" href="{{ route('activity-logs.index', array_merge(request()->query(), ['log_type' => 'all'])) }}" style="font-size: 13px; border-radius: 8px;">
                <i class="bi bi-list-task me-1"></i> All Logs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $logType === 'whatsapp' ? 'active' : '' }} px-3 py-1.5 fw-semibold ms-1" href="{{ route('activity-logs.index', array_merge(request()->query(), ['log_type' => 'whatsapp'])) }}" style="font-size: 13px; border-radius: 8px;">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp Logs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $logType === 'email' ? 'active' : '' }} px-3 py-1.5 fw-semibold ms-1" href="{{ route('activity-logs.index', array_merge(request()->query(), ['log_type' => 'email'])) }}" style="font-size: 13px; border-radius: 8px;">
                <i class="bi bi-envelope me-1"></i> Email Logs
            </a>
        </li>
    </ul>
</div>

<!-- Logs list -->
<div class="card border border-light shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover fs-7">
            <thead>
                <tr>
                    <th style="width: 50px;"></th>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Metadata</th>
                    <th class="text-end" style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $badgeClass = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                        $actLower = strtolower($log->action);
                        if (str_contains($actLower, 'create') || str_contains($actLower, 'store') || str_contains($actLower, 'add')) {
                            $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                        } elseif (str_contains($actLower, 'update') || str_contains($actLower, 'edit')) {
                            $badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                        } elseif (str_contains($actLower, 'delete') || str_contains($actLower, 'destroy') || str_contains($actLower, 'remove')) {
                            $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                        } elseif (str_contains($actLower, 'login') || str_contains($actLower, 'authenticate')) {
                            $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                        }
                    @endphp
                    <tr>
                        <!-- Collapse Toggle Button (only if changes exist) -->
                        <td class="text-center">
                            @if(!empty($log->old_values) || !empty($log->new_values))
                                <button class="btn btn-link p-0 text-decoration-none text-secondary" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#changes-{{ $log->id }}" 
                                        aria-expanded="false" 
                                        aria-controls="changes-{{ $log->id }}">
                                    <i class="bi bi-chevron-down fs-6"></i>
                                </button>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $log->created_at->format('d M Y') }}</div>
                            <div class="text-muted fs-8">{{ $log->created_at->format('h:i:s A') }}</div>
                        </td>
                        <td>
                            @if($log->user)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $log->user->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <div>
                                        <div class="fw-bold text-dark fs-8">{{ $log->user->name }}</div>
                                        <div class="text-muted fs-9 text-uppercase">{{ $log->user->role?->name }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted italic fs-8">System / Guest</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge text-capitalize {{ $badgeClass }} fs-8">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td class="fw-semibold text-dark text-wrap" style="max-width: 250px;">
                            {{ $log->description }}
                            @if($log->model_type)
                                <div class="text-muted fs-8">
                                    {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fs-8 text-secondary"><i class="bi bi-pc-display"></i> {{ $log->ip_address }}</div>
                            <div class="text-muted fs-9 text-truncate" style="max-width: 180px;" title="{{ $log->user_agent }}">
                                {{ $log->user_agent }}
                            </div>
                        </td>
                        <!-- Actions Column -->
                        <td class="text-end">
                            <button type="button" class="btn btn-outline-primary btn-sm btn-view-log" style="border-radius: 6px;"
                                    data-id="{{ $log->id }}"
                                    data-timestamp="{{ $log->created_at->format('d M Y h:i:s A') }}"
                                    data-user="{{ $log->user ? $log->user->name : 'System / Guest' }}"
                                    data-action="{{ $log->action }}"
                                    data-ip="{{ $log->ip_address }}"
                                    data-description="{{ $log->description }}"
                                    data-payload="{{ json_encode($log->new_values) }}"
                                    data-old="{{ json_encode($log->old_values) }}"
                                    onclick="openLogModal(this)">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    
                    <!-- Collapsible Row for Data Changes -->
                    @if(!empty($log->old_values) || !empty($log->new_values))
                        <tr class="collapse-row">
                            <td colspan="7" class="p-0 border-0">
                                <div class="collapse" id="changes-{{ $log->id }}">
                                    <div class="p-3 bg-light border-bottom">
                                        <div class="row g-3">
                                            @if(!empty($log->old_values))
                                                <div class="col-12 col-md-6">
                                                    <h6 class="fs-8 fw-bold text-danger mb-2"><i class="bi bi-dash-circle"></i> Previous State</h6>
                                                    <div class="bg-white border rounded p-2 text-dark font-monospace fs-8 pre-wrap" style="max-height: 200px; overflow-y: auto;">
                                                        @foreach($log->old_values as $key => $val)
                                                            <div><strong>{{ $key }}:</strong> {{ is_array($val) ? json_encode($val) : $val }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                            @if(!empty($log->new_values))
                                                <div class="col-12 col-md-{{ empty($log->old_values) ? '12' : '6' }}">
                                                    <h6 class="fs-8 fw-bold text-success mb-2"><i class="bi bi-plus-circle"></i> Updated State</h6>
                                                    <div class="bg-white border rounded p-2 text-dark font-monospace fs-8 pre-wrap" style="max-height: 200px; overflow-y: auto;">
                                                        @foreach($log->new_values as $key => $val)
                                                            <div><strong>{{ $key }}:</strong> {{ is_array($val) ? json_encode($val) : $val }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history fs-3"></i>
                            <div class="mt-2">No activity logs recorded matching criteria.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($logs->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $logs->links() }}
        </div>
    @endif
</div>
</div>

{{-- View Log Modal --}}
<div class="modal fade" id="viewLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold" id="log-modal-title"><i class="bi bi-eye me-2"></i>Activity Log Details</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Log Meta Details -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 p-3 bg-light rounded gap-3" style="border: 1px solid #e2e8f0;">
                    <div style="min-width: 140px; flex: 1 1 auto;">
                        <small class="text-muted d-block fw-bold" style="font-size: 10px; letter-spacing: 0.05em; margin-bottom: 2px;">TIMESTAMP</small>
                        <span id="log-meta-time" class="fw-semibold text-dark fs-7"></span>
                    </div>
                    <div style="min-width: 100px; flex: 1 1 auto;">
                        <small class="text-muted d-block fw-bold" style="font-size: 10px; letter-spacing: 0.05em; margin-bottom: 2px;">USER</small>
                        <span id="log-meta-user" class="fw-semibold text-dark fs-7"></span>
                    </div>
                    <div style="min-width: 150px; max-width: 280px; flex: 1 1 auto;">
                        <small class="text-muted d-block fw-bold" style="font-size: 10px; letter-spacing: 0.05em; margin-bottom: 2px;">ACTION TYPE</small>
                        <span id="log-meta-action" class="badge text-capitalize fs-8 text-wrap text-start" style="white-space: normal; line-height: 1.3; display: inline-block;"></span>
                    </div>
                    <div style="min-width: 100px; flex: 1 1 auto;">
                        <small class="text-muted d-block fw-bold" style="font-size: 10px; letter-spacing: 0.05em; margin-bottom: 2px;">IP ADDRESS</small>
                        <span id="log-meta-ip" class="fw-semibold text-secondary font-monospace fs-8"></span>
                    </div>
                </div>

                <!-- Section for WhatsApp Logs -->
                <div id="whatsapp-log-section" class="d-none mt-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase;">Receiver WhatsApp Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-success border-end-0" style="border-radius: 8px 0 0 8px;"><i class="bi bi-whatsapp"></i></span>
                            <input type="text" id="whatsapp-receiver" class="form-control bg-white font-monospace" readonly style="border-radius: 0 8px 8px 0;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase;">Message Content</label>
                        <div class="p-3 border rounded text-dark fs-7" id="whatsapp-message" style="background-color: #efeae2; max-height: 250px; overflow-y: auto; white-space: pre-wrap; font-family: inherit; line-height: 1.5; border-color: #e2e8f0; border-left: 4px solid #075e54;"></div>
                    </div>
                </div>

                <!-- Section for Email Logs -->
                <div id="email-log-section" class="d-none mt-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase;">Receiver Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-primary border-end-0" style="border-radius: 8px 0 0 8px;"><i class="bi bi-envelope"></i></span>
                            <input type="text" id="email-receiver" class="form-control bg-white" readonly style="border-radius: 0 8px 8px 0;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase;">Subject</label>
                        <input type="text" id="email-subject" class="form-control bg-white fw-semibold" readonly style="border-radius: 8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase;">Mail Content Body</label>
                        <div class="border rounded" style="background: #f8fafc; overflow: hidden;">
                            <iframe id="email-body-iframe" style="width: 100%; height: 350px; border: none; background: white;"></iframe>
                        </div>
                    </div>
                    <div class="mb-3" id="email-attachments-wrapper">
                        <label class="form-label fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase;">Attachments</label>
                        <div class="d-flex flex-wrap gap-2" id="email-attachments-list"></div>
                    </div>
                </div>

                <!-- Section for Generic/Other Logs -->
                <div id="generic-log-section" class="d-none mt-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size: 11px; text-transform: uppercase;">Log Description</label>
                        <div class="p-3 bg-light border rounded text-dark fs-7" id="generic-description" style="line-height: 1.5;"></div>
                    </div>
                    <div class="row g-3" id="generic-changes-wrapper">
                        <div class="col-md-6" id="generic-old-state-col">
                            <h6 class="fs-8 fw-bold text-danger mb-2"><i class="bi bi-dash-circle"></i> Previous State</h6>
                            <pre class="bg-light border rounded p-3 text-dark font-monospace fs-8" id="generic-old-state" style="max-height: 250px; overflow-y: auto; white-space: pre-wrap;"></pre>
                        </div>
                        <div class="col-md-6" id="generic-new-state-col">
                            <h6 class="fs-8 fw-bold text-success mb-2"><i class="bi bi-plus-circle"></i> Updated State</h6>
                            <pre class="bg-light border rounded p-3 text-dark font-monospace fs-8" id="generic-new-state" style="max-height: 250px; overflow-y: auto; white-space: pre-wrap;"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal" style="border-radius:6px;">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openLogModal(button) {
        const id = button.getAttribute('data-id');
        const timestamp = button.getAttribute('data-timestamp');
        const user = button.getAttribute('data-user');
        const action = button.getAttribute('data-action');
        const ip = button.getAttribute('data-ip');
        const description = button.getAttribute('data-description');
        
        let payload = {};
        try {
            payload = JSON.parse(button.getAttribute('data-payload')) || {};
        } catch (e) {
            payload = {};
        }

        let oldState = {};
        try {
            oldState = JSON.parse(button.getAttribute('data-old')) || {};
        } catch (e) {
            oldState = {};
        }

        // Set Meta Details
        document.getElementById('log-meta-time').innerText = timestamp;
        document.getElementById('log-meta-user').innerText = user;
        
        const actionBadge = document.getElementById('log-meta-action');
        actionBadge.innerText = action.replace(/_/g, ' ');
        actionBadge.className = 'badge text-capitalize fs-8 ';
        
        // Determine badge styling based on action name
        const actLower = action.toLowerCase();
        if (actLower.includes('create') || actLower.includes('store') || actLower.includes('add')) {
            actionBadge.classList.add('bg-success-subtle', 'text-success', 'border', 'border-success-subtle');
        } else if (actLower.includes('update') || actLower.includes('edit')) {
            actionBadge.classList.add('bg-warning-subtle', 'text-warning', 'border', 'border-warning-subtle');
        } else if (actLower.includes('delete') || actLower.includes('destroy') || actLower.includes('remove')) {
            actionBadge.classList.add('bg-danger-subtle', 'text-danger', 'border', 'border-danger-subtle');
        } else if (actLower.includes('login') || actLower.includes('authenticate')) {
            actionBadge.classList.add('bg-primary-subtle', 'text-primary', 'border', 'border-primary-subtle');
        } else if (actLower.includes('whatsapp')) {
            actionBadge.classList.add('bg-success', 'text-white');
        } else if (actLower.includes('email') || actLower.includes('mail')) {
            actionBadge.classList.add('bg-primary', 'text-white');
        } else {
            actionBadge.classList.add('bg-secondary-subtle', 'text-secondary', 'border', 'border-secondary-subtle');
        }

        document.getElementById('log-meta-ip').innerText = ip || '—';

        // Hide all sections first
        document.getElementById('whatsapp-log-section').classList.add('d-none');
        document.getElementById('email-log-section').classList.add('d-none');
        document.getElementById('generic-log-section').classList.add('d-none');

        // Check Log Type
        if (action.includes('whatsapp')) {
            // WhatsApp View
            document.getElementById('whatsapp-log-section').classList.remove('d-none');
            document.getElementById('whatsapp-receiver').value = payload.phone || 'N/A';
            document.getElementById('whatsapp-message').innerText = payload.message || description;
            
            document.getElementById('log-modal-title').innerHTML = '<i class="bi bi-whatsapp text-success me-2"></i>WhatsApp Message Sent Details';
        } else if (action === 'email_sent' || action.includes('mail')) {
            // Email View
            document.getElementById('email-log-section').classList.remove('d-none');
            document.getElementById('email-receiver').value = payload.to || 'N/A';
            document.getElementById('email-subject').value = payload.subject || 'N/A';
            
            // Set body content in iframe
            const iframe = document.getElementById('email-body-iframe');
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            doc.open();
            doc.write(payload.body || '<p class="text-muted italic">No body content</p>');
            doc.close();

            // Set attachments
            const attachmentsList = document.getElementById('email-attachments-list');
            const attachmentsWrapper = document.getElementById('email-attachments-wrapper');
            attachmentsList.innerHTML = '';
            
            if (payload.attachments && payload.attachments.length > 0) {
                attachmentsWrapper.classList.remove('d-none');
                payload.attachments.forEach(file => {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-light text-dark border px-2.5 py-1.5 fs-8 d-inline-flex align-items-center gap-1';
                    badge.style.borderRadius = '20px';
                    badge.innerHTML = `<i class="bi bi-paperclip text-muted"></i> ${file}`;
                    attachmentsList.appendChild(badge);
                });
            } else {
                attachmentsWrapper.classList.add('d-none');
            }
            
            document.getElementById('log-modal-title').innerHTML = '<i class="bi bi-envelope text-primary me-2"></i>Email Sent Details';
        } else {
            // Generic View
            document.getElementById('generic-log-section').classList.remove('d-none');
            document.getElementById('generic-description').innerText = description;
            
            const oldStateCol = document.getElementById('generic-old-state-col');
            const newStateCol = document.getElementById('generic-new-state-col');
            const oldStateEl = document.getElementById('generic-old-state');
            const newStateEl = document.getElementById('generic-new-state');
            
            oldStateEl.innerHTML = '';
            newStateEl.innerHTML = '';
            
            const hasOld = Object.keys(oldState).length > 0;
            const hasNew = Object.keys(payload).length > 0;
            
            if (hasOld) {
                oldStateCol.classList.remove('d-none');
                oldStateEl.textContent = JSON.stringify(oldState, null, 2);
            } else {
                oldStateCol.classList.add('d-none');
            }
            
            if (hasNew) {
                newStateCol.classList.remove('d-none');
                newStateEl.textContent = JSON.stringify(payload, null, 2);
            } else {
                newStateCol.classList.add('d-none');
            }

            if (!hasOld && !hasNew) {
                document.getElementById('generic-changes-wrapper').classList.add('d-none');
            } else {
                document.getElementById('generic-changes-wrapper').classList.remove('d-none');
            }
            
            document.getElementById('log-modal-title').innerHTML = '<i class="bi bi-info-circle text-info me-2"></i>Activity Log Details';
        }

        const modal = new bootstrap.Modal(document.getElementById('viewLogModal'));
        modal.show();
    }
</script>
@endpush
@endsection
