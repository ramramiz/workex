@extends('layouts.app')

@section('title', 'Project AMCs')
@section('page-title', 'Project Annual Maintenance Contracts (AMC)')

@section('breadcrumb')
    <li class="breadcrumb-item active">Project AMCs</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Project AMC Management</h5>
        <div class="d-flex gap-2">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('project-amcs.import.template') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                    <i class="bi bi-download me-1"></i> Download Template
                </a>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importAmcModal" style="border-radius: 8px;">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Excel
                </button>
            @endif
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAccounts())
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAmcModal" style="border-radius: 8px;">
                    <i class="bi bi-plus-circle me-1"></i> Add Project AMC
                </button>
            @endif
        </div>
</div>

{{-- Filters Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('project-amcs.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fs-8 text-muted mb-1 text-uppercase fw-bold">Filter Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="pending_renewal" {{ request('status') === 'pending_renewal' ? 'selected' : '' }}>Pending Renewal</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fs-8 text-muted mb-1 text-uppercase fw-bold">Filter Project</label>
                <select name="project_id" class="form-select form-select-sm select-search select2" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->project_code }}) - {{ $p->client?->company_name ?? 'Internal Project' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-5 text-md-end">
                <a href="{{ route('project-amcs.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise"></i> Reset Filters
                </a>
            </div>
        </form>
    </div>
</div>

{{-- AMC List Table --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead class="table-light">
                <tr>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Project Details</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Client</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">AMC Value</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Contract Period</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Frequency</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Status</th>
                    <th class="py-3 text-secondary text-end" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($amcs as $amc)
                    <tr>
                        <td class="fw-semibold text-dark">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <div>
                                    @if($amc->project)
                                        <a href="{{ route('projects.show', $amc->project) }}" class="text-decoration-none text-dark fw-bold">{{ $amc->project->name }}</a>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <span class="badge bg-secondary-subtle text-secondary font-monospace fs-8" style="font-weight: 500;">{{ $amc->project->project_code }}</span>
                                            <span class="badge bg-primary-subtle text-primary text-uppercase fs-8" style="font-weight: 600;">{{ $amc->service_type ?? 'AMC' }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted fw-bold">Project Deleted / N/A</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($amc->project && $amc->project->client)
                                <span class="fw-medium">{{ $amc->project->client->contact_person }}</span>
                                <small class="text-muted d-block fs-8">{{ $amc->project->client->company_name }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-bold text-primary">
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAccounts())
                                ₹{{ number_format($amc->amount, 2) }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span class="fs-7 text-dark fw-medium">{{ $amc->start_date->format('d M Y') }}</span>
                            <span class="text-muted fs-8 mx-1">to</span>
                            <span class="fs-7 text-dark fw-medium">{{ $amc->end_date->format('d M Y') }}</span>
                            @if($amc->status === 'expired')
                                <small class="text-danger d-block mt-0.5" style="font-size: 10px;">Expired {{ $amc->end_date->diffForHumans() }}</small>
                            @else
                                <small class="text-muted d-block mt-0.5" style="font-size: 10px;">Ends {{ $amc->end_date->diffForHumans() }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border text-capitalize px-2.5 py-1 fw-medium fs-8" style="border-radius:20px;">
                                {{ str_replace('_', ' ', $amc->frequency) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $amc->status_badge }}-subtle text-{{ $amc->status_badge }} border border-{{ $amc->status_badge }}-subtle">
                                @if($amc->status === 'pending_renewal')
                                    Pending Renewal
                                @else
                                    {{ ucfirst($amc->status) }}
                                @endif
                            </span>
                            @php
                                $daysLeft = (int) today()->diffInDays($amc->end_date, false);
                            @endphp
                            @if($amc->status !== 'expired' && $daysLeft >= 0 && $daysLeft <= 30)
                                <div class="mt-1">
                                    <span class="badge bg-warning text-dark border border-warning-subtle" style="font-size: 10px; font-weight: 600;">
                                        <i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i>{{ $daysLeft }} {{ Str::plural('day', $daysLeft) }} left
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('project-amcs.show', $amc) }}" class="btn btn-outline-info btn-sm" 
                                   style="border-radius: 6px;" title="View Renewal History">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($amc->project && $amc->project->client && $amc->project->client->phone)
                                    <form method="POST" action="{{ route('project-amcs.send-whatsapp-reminder', $amc) }}" class="d-inline" onsubmit="return confirm('Send WhatsApp AMC renewal reminder to this client?')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success btn-sm" style="border-radius: 6px;" title="Send WhatsApp Reminder">
                                            <i class="bi bi-whatsapp"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-outline-success btn-sm disabled" style="border-radius: 6px;" title="Client has no phone number" disabled>
                                        <i class="bi bi-whatsapp"></i>
                                    </button>
                                @endif
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAccounts())
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-edit-amc" 
                                            style="border-radius: 6px;"
                                            data-id="{{ $amc->id }}"
                                            data-project_name="{{ $amc->project ? $amc->project->name : 'N/A' }}"
                                            data-amount="{{ $amc->amount }}"
                                            data-start_date="{{ $amc->start_date->format('Y-m-d') }}"
                                            data-end_date="{{ $amc->end_date->format('Y-m-d') }}"
                                            data-frequency="{{ $amc->frequency }}"
                                            data-status="{{ $amc->status }}"
                                            data-remarks="{{ $amc->remarks }}"
                                            data-alert_phone="{{ $amc->alert_phone }}"
                                            data-alert_email="{{ $amc->alert_email }}"
                                            data-default_phone="{{ $amc->project && $amc->project->client ? $amc->project->client->phone : '' }}"
                                            data-default_email="{{ $amc->project && $amc->project->client ? $amc->project->client->email : '' }}"
                                            data-service_type="{{ $amc->service_type }}"
                                            onclick="openEditModal(this)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('project-amcs.destroy', $amc) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this AMC contract?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius: 6px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history" style="font-size: 36px;"></i>
                            <div class="mt-2">No Project AMCs registered.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($amcs->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $amcs->links() }}
        </div>
    @endif
</div>

{{-- Import AMC Modal --}}
@if(auth()->user()->isSuperAdmin())
<div class="modal fade" id="importAmcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Project AMCs</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('project-amcs.import.preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Upload Excel File (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required style="border-radius: 8px;">
                        <small class="text-muted d-block mt-2">
                            Download the template to format your file correctly. Matching is done via <strong>Project Code</strong> or <strong>Project Name</strong>.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Add AMC Modal --}}
@if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
<div class="modal fade" id="addAmcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Add Project AMC</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('project-amcs.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Select Project <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select select-search" required style="border-radius:8px;">
                            <option value="">Select Project</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" 
                                        data-client-phone="{{ $p->client?->phone ?? '' }}" 
                                        data-client-email="{{ $p->client?->email ?? '' }}">
                                    {{ $p->name }} ({{ $p->project_code }}) - {{ $p->client?->company_name ?? 'Internal Project' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">AMC Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">₹</span>
                                <input type="number" step="0.01" min="0" name="amount" class="form-control" placeholder="0.00" required style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Service Type <span class="text-danger">*</span></label>
                            <select id="create-service-type-select" name="service_type" class="form-select" required style="border-radius:8px;" onchange="toggleCustomServiceType(this, 'create-custom-service-type')">
                                <option value="AMC">AMC</option>
                                <option value="Domain">Domain</option>
                                <option value="Server">Server</option>
                                <option value="Email">Email</option>
                                <option value="other">Other (Custom)</option>
                            </select>
                            <input type="text" id="create-custom-service-type" class="form-control mt-2 d-none" placeholder="Enter custom service type" style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required style="border-radius:8px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Billing Frequency <span class="text-danger">*</span></label>
                            <select name="frequency" class="form-select" required style="border-radius:8px;">
                                <option value="annually">Annually</option>
                                <option value="semi-annually">Semi-Annually</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Contract Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required style="border-radius:8px;">
                                <option value="active">Active</option>
                                <option value="pending_renewal">Pending Renewal</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Alert WhatsApp Number</label>
                            <input type="text" name="alert_phone" id="create-alert-phone" class="form-control" placeholder="Defaults to client phone" style="border-radius:8px;">
                            <small class="text-muted d-block mt-0.5" style="font-size: 10px;">Leave blank to use default details.</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Alert Email</label>
                            <input type="email" name="alert_email" id="create-alert-email" class="form-control" placeholder="Defaults to client email" style="border-radius:8px;">
                            <small class="text-muted d-block mt-0.5" style="font-size: 10px;">Leave blank to use default details.</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Remarks / Description</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Contract conditions, notes..." style="border-radius:8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Save AMC</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit AMC Modal --}}
<div class="modal fade" id="editAmcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Project AMC</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAmcForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Project</label>
                        <input type="text" id="edit-project-display" class="form-control bg-light" readonly style="border-radius:8px;">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">AMC Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">₹</span>
                                <input type="number" step="0.01" min="0" name="amount" id="edit-amount" class="form-control" required style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Service Type <span class="text-danger">*</span></label>
                            <select id="edit-service-type-select" name="service_type" class="form-select" required style="border-radius:8px;" onchange="toggleCustomServiceType(this, 'edit-custom-service-type')">
                                <option value="AMC">AMC</option>
                                <option value="Domain">Domain</option>
                                <option value="Server">Server</option>
                                <option value="Email">Email</option>
                                <option value="other">Other (Custom)</option>
                            </select>
                            <input type="text" id="edit-custom-service-type" class="form-control mt-2 d-none" placeholder="Enter custom service type" style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="edit-start_date" class="form-control" required style="border-radius:8px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="edit-end_date" class="form-control" required style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Billing Frequency <span class="text-danger">*</span></label>
                            <select name="frequency" id="edit-frequency" class="form-select" required style="border-radius:8px;">
                                <option value="annually">Annually</option>
                                <option value="semi-annually">Semi-Annually</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Contract Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit-status" class="form-select" required style="border-radius:8px;">
                                <option value="active">Active</option>
                                <option value="pending_renewal">Pending Renewal</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Alert WhatsApp Number</label>
                            <input type="text" name="alert_phone" id="edit-alert-phone" class="form-control" placeholder="Defaults to client phone" style="border-radius:8px;">
                            <small class="text-muted d-block mt-0.5" style="font-size: 10px;">Leave blank to use default details.</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Alert Email</label>
                            <input type="email" name="alert_email" id="edit-alert-email" class="form-control" placeholder="Defaults to client email" style="border-radius:8px;">
                            <small class="text-muted d-block mt-0.5" style="font-size: 10px;">Leave blank to use default details.</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Remarks / Description</label>
                        <textarea name="remarks" id="edit-remarks" class="form-control" rows="3" style="border-radius:8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Update AMC</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    function openEditModal(button) {
        const id = button.getAttribute('data-id');
        const projectName = button.getAttribute('data-project_name');
        const amount = button.getAttribute('data-amount');
        const startDate = button.getAttribute('data-start_date');
        const endDate = button.getAttribute('data-end_date');
        const frequency = button.getAttribute('data-frequency');
        const status = button.getAttribute('data-status');
        const remarks = button.getAttribute('data-remarks');
        const alertPhone = button.getAttribute('data-alert_phone');
        const alertEmail = button.getAttribute('data-alert_email');
        const defaultPhone = button.getAttribute('data-default_phone');
        const defaultEmail = button.getAttribute('data-default_email');
        const serviceType = button.getAttribute('data-service_type');

        document.getElementById('edit-project-display').value = projectName;
        document.getElementById('edit-amount').value = amount;
        document.getElementById('edit-start_date').value = startDate;
        document.getElementById('edit-end_date').value = endDate;
        document.getElementById('edit-frequency').value = frequency;
        document.getElementById('edit-status').value = status;
        document.getElementById('edit-remarks').value = remarks || '';
        
        const selectEl = document.getElementById('edit-service-type-select');
        const customInput = document.getElementById('edit-custom-service-type');
        
        selectEl.setAttribute('name', 'service_type');
        customInput.removeAttribute('name');
        customInput.removeAttribute('required');
        customInput.classList.add('d-none');
        customInput.value = '';

        if (['AMC', 'Domain', 'Server', 'Email'].includes(serviceType)) {
            selectEl.value = serviceType;
        } else {
            selectEl.value = 'other';
            customInput.classList.remove('d-none');
            customInput.setAttribute('required', 'required');
            customInput.value = serviceType || '';
            customInput.setAttribute('name', 'service_type');
            selectEl.removeAttribute('name');
        }
        
        const alertPhoneInput = document.getElementById('edit-alert-phone');
        if (alertPhoneInput) {
            alertPhoneInput.value = alertPhone || '';
            alertPhoneInput.placeholder = defaultPhone || 'Defaults to client phone';
        }
        
        const alertEmailInput = document.getElementById('edit-alert-email');
        if (alertEmailInput) {
            alertEmailInput.value = alertEmail || '';
            alertEmailInput.placeholder = defaultEmail || 'Defaults to client email';
        }

        const form = document.getElementById('editAmcForm');
        form.action = `/project-amcs/${id}`;

        const modal = new bootstrap.Modal(document.getElementById('editAmcModal'));
        modal.show();
    }

    function toggleCustomServiceType(selectEl, customInputId) {
        const customInput = document.getElementById(customInputId);
        if (selectEl.value === 'other') {
            customInput.classList.remove('d-none');
            customInput.setAttribute('required', 'required');
            customInput.setAttribute('name', selectEl.getAttribute('name'));
            selectEl.removeAttribute('name');
        } else {
            customInput.classList.add('d-none');
            customInput.removeAttribute('required');
            if (!selectEl.hasAttribute('name')) {
                selectEl.setAttribute('name', customInput.getAttribute('name'));
            }
            customInput.removeAttribute('name');
            customInput.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const projectSelect = document.querySelector('select[name="project_id"]');
        if (projectSelect) {
            projectSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const phone = selectedOption ? selectedOption.getAttribute('data-client-phone') : '';
                const email = selectedOption ? selectedOption.getAttribute('data-client-email') : '';
                
                const alertPhoneInput = document.getElementById('create-alert-phone');
                const alertEmailInput = document.getElementById('create-alert-email');
                
                if (alertPhoneInput) {
                    alertPhoneInput.placeholder = phone || 'Defaults to client phone';
                }
                if (alertEmailInput) {
                    alertEmailInput.placeholder = email || 'Defaults to client email';
                }
            });
        }
    });
</script>
@endpush
@endsection
