@extends('layouts.app')

@section('title', 'Employee Profile')
@section('page-title', 'Employee Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">{{ $employee->name }}</li>
@endsection

@section('content')
@if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; background: #fff;">
    <div class="card-body py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h5 class="fw-bold text-dark mb-0">Management Tools</h5>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($employee->user)
                @if(auth()->user()->isSuperAdmin())
                <button type="button" class="btn btn-warning btn-sm"
                    title="Roles & Permissions"
                    onclick="openPermissionsModal({{ $employee->id }}, '{{ addslashes($employee->name) }}')">
                    <i class="bi bi-shield-check me-1"></i> Roles & Permissions
                </button>
                @endif
                {{-- Login to Account (Super Admin Impersonation) --}}
                <button type="button" class="btn btn-sm btn-dark"
                    title="Login as this employee"
                    onclick="confirmLoginAs({{ $employee->id }}, '{{ addslashes($employee->name) }}')">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login to Account
                </button>
                {{-- Session Tracking Icon --}}
                <button type="button" class="btn btn-teal btn-sm session-track-btn"
                    title="View Login Sessions"
                    style="color: white; background-color: #0d9488; border-color: #0d9488;"
                    data-user-id="{{ $employee->user->id }}"
                    data-sessions-url="{{ route('users.sessions.index', $employee->user) }}"
                    data-destroy-all-url="{{ route('users.sessions.destroy-all', $employee->user) }}"
                    onclick="openSessionPanel(this)">
                    <i class="bi bi-shield-lock me-1"></i> Login Sessions
                </button>
                <a href="{{ route('mailbox.index', ['user_id' => $employee->user->id]) }}" target="_blank" class="btn btn-info btn-sm text-white" title="Open Mailbox">
                    <i class="bi bi-envelope me-1"></i> Open Mailbox
                </a>
            @endif
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary btn-sm" title="Edit Profile">
                <i class="bi bi-pencil me-1"></i> Edit Profile
            </a>
            <form method="POST" action="{{ route('employees.toggle-status', $employee) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-warning btn-sm" title="Toggle Status">
                    <i class="bi bi-power me-1"></i> Toggle Status
                </button>
            </form>
        </div>
    </div>
</div>
@endif

<div class="row g-4">
    <!-- Left Column: User details card -->
    <div class="col-12 col-lg-4">
        <div class="card text-center mb-4">
            <div class="card-body">
                <img src="{{ $employee->user->avatar_url }}" alt="{{ $employee->name }}" class="avatar-circle mb-3 border p-1" style="width: 100px; height: 100px;">
                <h4>{{ $employee->name }}</h4>
                <p class="text-muted mb-2">{{ $employee->designation->name ?? 'N/A' }}</p>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-3">{{ $employee->department->name ?? 'N/A' }}</span>
                
                <hr>
                
                <div class="text-start">
                    <div class="mb-3">
                        <small class="text-muted d-block">Employee Code</small>
                        <span class="fw-semibold">{{ $employee->employee_code }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Work Email</small>
                        <span class="fw-semibold">{{ $employee->user->email }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Personal Email</small>
                        <span class="fw-semibold">{{ $employee->personal_email ?? '—' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone Number</small>
                        <span class="fw-semibold">{{ $employee->phone ?? $employee->user->phone ?? 'â€”' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Role</small>
                        <span class="fw-semibold">{{ $employee->user->role->name ?? 'N/A' }}</span>
                    </div>
                    @if($employee->google_drive_link)
                    <div class="mb-3">
                        <small class="text-muted d-block">Google Drive Folder</small>
                        <a href="{{ $employee->google_drive_link }}" target="_blank" class="btn btn-outline-success btn-xs d-flex align-items-center justify-content-center gap-1.5 mt-1" style="font-size: 11px; border-radius: 6px;">
                            <i class="bi bi-google"></i> Open Drive Folder
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Financial & Status</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Salary Details</small>
                        @if($employee->salary)
                            <span class="fw-semibold text-success">&#8377;{{ number_format($employee->salary, 2) }}</span>
                            <span class="text-muted fs-7">/ {{ $employee->salary_type }}</span>
                        @else
                            <span class="text-muted">Not Configured</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Joining Date</small>
                        <span class="fw-semibold">{{ $employee->joining_date ? $employee->joining_date->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Status</small>
                        @if($employee->status === 'active')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Tabs -->
    <div class="col-12 col-lg-8">
        <!-- Stats Widgets -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card text-center py-3">
                    <div class="fs-4 fw-bold text-primary">{{ number_format($employee->user->workSessions->sum('total_minutes') / 60, 1) }}</div>
                    <div class="text-muted fs-7">Total Hours</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center py-3">
                    <div class="fs-4 fw-bold text-success">{{ $employee->user->workSessions->count() }}</div>
                    <div class="text-muted fs-7">Work Sessions</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center py-3">
                    <div class="fs-4 fw-bold text-warning">{{ $employee->user->leaves->where('status', 'approved')->count() }}</div>
                    <div class="text-muted fs-7">Leaves Taken</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card text-center py-3">
                    <div class="fs-4 fw-bold text-info">{{ $employee->user->dailyReports->count() }}</div>
                    <div class="text-muted fs-7">Daily Reports</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs m-0 border-bottom-0 px-3" id="profileTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-3" id="work-sessions-tab" data-bs-toggle="tab" data-bs-target="#work-sessions" type="button" role="tab">Work Sessions</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="leaves-tab" data-bs-toggle="tab" data-bs-target="#leaves" type="button" role="tab">Leaves</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button" role="tab">Daily Reports</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="attendance-summary-tab" data-bs-toggle="tab" data-bs-target="#attendance-summary" type="button" role="tab">Attendance Summary</button>
                    </li>
                    @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
                    <li class="nav-item">
                        <button class="nav-link py-3" id="payslips-tab" data-bs-toggle="tab" data-bs-target="#payslips" type="button" role="tab">Payslips</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents-pane" type="button" role="tab">Documents</button>
                    </li>
                    @endif
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="profileTabsContent">
                    <!-- Work Sessions -->
                    <div class="tab-pane fade show active" id="work-sessions" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>IN</th>
                                        <th>OUT</th>
                                        <th>Total Duration</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->user->workSessions as $session)
                                        @php
                                            $firstLog = $session->timeLogs->whereNotNull('started_at')->sortBy('started_at')->first();
                                            $lastLog  = $session->timeLogs->whereNotNull('ended_at')->sortByDesc('ended_at')->first();
                                            $inTime   = $firstLog ? \Carbon\Carbon::parse($firstLog->started_at)->format('h:i A') : ($session->started_at ? \Carbon\Carbon::parse($session->started_at)->format('h:i A') : '—');
                                            $outTime  = $lastLog  ? \Carbon\Carbon::parse($lastLog->ended_at)->format('h:i A')   : ($session->ended_at   ? \Carbon\Carbon::parse($session->ended_at)->format('h:i A')   : 'Active');
                                        @endphp
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($session->date)->format('d M Y') }}</td>
                                            <td>{{ $inTime }}</td>
                                            <td>{{ $outTime }}</td>
                                            <td>{{ number_format($session->total_minutes / 60, 2) }} hrs</td>
                                            <td>
                                                <span class="badge bg-{{ $session->ended_at ? 'secondary' : 'success' }}-subtle text-{{ $session->ended_at ? 'secondary' : 'success' }} border border-{{ $session->ended_at ? 'secondary' : 'success' }}-subtle">
                                                    {{ $session->ended_at ? 'Completed' : 'Active' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No work sessions recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Leaves -->
                    <div class="tab-pane fade" id="leaves" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Duration</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->user->leaves as $leave)
                                        <tr>
                                            <td>{{ ucwords(str_replace('_', ' ', $leave->leave_type)) }}</td>
                                            <td>
                                                {{ $leave->from_date->format('d M') }} to {{ $leave->to_date->format('d M Y') }}
                                                <small class="text-muted d-block">({{ $leave->from_date->diffInDays($leave->to_date) + 1 }} days)</small>
                                            </td>
                                            <td>{{ Str::limit($leave->reason, 40) }}</td>
                                            <td>
                                                @if($leave->status === 'approved')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                                                @elseif($leave->status === 'pending')
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No leaves recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Daily Reports -->
                    <div class="tab-pane fade" id="reports" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Completed Work</th>
                                        <th>Git Link</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->user->dailyReports as $report)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($report->date)->format('d M Y') }}</td>
                                            <td>{{ Str::limit($report->completed_work, 40) }}</td>
                                            <td>
                                                @if($report->git_link)
                                                    <a href="{{ $report->git_link }}" target="_blank" class="text-decoration-none text-truncate d-inline-block" style="max-width: 150px;">{{ $report->git_link }}</a>
                                                @else
                                                    â€”
                                                @endif
                                            </td>
                                            <td>
                                                @if($report->status === 'approved')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                                                @elseif($report->status === 'pending')
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No daily reports submitted.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Attendance Summary -->
                    <div class="tab-pane fade" id="attendance-summary" role="tabpanel">
                        <h6 class="fw-bold mb-3">Attendance & Work Summary ({{ now()->format('F Y') }})</h6>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="p-3 border rounded-3 bg-light text-center h-100">
                                    <div class="text-muted fs-8 mb-1">Days Present</div>
                                    <div class="fs-4 fw-bold text-success">{{ $presentDays }}</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-3 border rounded-3 bg-light text-center h-100">
                                    <div class="text-muted fs-8 mb-1">Late Arrivals</div>
                                    <div class="fs-4 fw-bold text-warning">{{ $lateDays }}</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-3 border rounded-3 bg-light text-center h-100">
                                    <div class="text-muted fs-8 mb-1">Absent Days</div>
                                    <div class="fs-4 fw-bold text-danger">{{ $absentDays }}</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-3 border rounded-3 bg-light text-center h-100">
                                    <div class="text-muted fs-8 mb-1">Half Days</div>
                                    <div class="fs-4 fw-bold text-info">{{ $halfDays }}</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-3 border rounded-3 bg-light text-center h-100">
                                    <div class="text-muted fs-8 mb-1">Approved Leaves</div>
                                    <div class="fs-4 fw-bold text-primary">{{ $approvedLeaves }}</div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-3 border rounded-3 bg-light text-center h-100">
                                    <div class="text-muted fs-8 mb-1">Hours Worked</div>
                                    <div class="fs-4 fw-bold text-dark">{{ $totalHoursWorked }} hrs</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payslips -->
                    @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
                    <div class="tab-pane fade" id="payslips" role="tabpanel">
                        <h6 class="fw-bold mb-3">Employee Salary Slips</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-sm">
                                <thead>
                                    <tr class="text-muted">
                                        <th>Month/Year</th>
                                        <th>Amount</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($myPayslips as $slip)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ date('F', mktime(0, 0, 0, $slip->month, 1)) }}</span>
                                            <small class="text-muted d-block" style="font-size: 10px;">{{ $slip->year }}</small>
                                        </td>
                                        <td class="fw-bold text-dark">&#8377;{{ number_format($slip->net_salary, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success" style="font-size: 10px; padding: 4px 8px; border-radius: 6px;">{{ ucfirst($slip->status) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.payroll.show', $slip) }}" target="_blank" class="btn btn-outline-primary btn-xs py-1 px-2" style="font-size: 11px; border-radius: 6px;">
                                                <i class="bi bi-file-earmark-pdf"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No payslips disbursed yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Documents -->
                    @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
                    <div class="tab-pane fade" id="documents-pane" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Employee Documents</h6>
                            @if($employee->google_drive_link)
                            <a href="{{ $employee->google_drive_link }}" target="_blank" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1.5" style="border-radius: 8px;">
                                <i class="bi bi-google"></i> Open Google Drive Folder
                            </a>
                            @endif
                        </div>

                        <!-- Documents List Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle table-sm">
                                <thead>
                                    <tr class="text-muted" style="font-size: 12px;">
                                        <th>Title</th>
                                        <th>File Name</th>
                                        <th>Size</th>
                                        <th>Upload Date</th>
                                        <th>Uploaded By</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employee->uploadedDocuments as $doc)
                                    <tr style="font-size: 13px;">
                                        <td><span class="fw-semibold text-dark">{{ $doc->title }}</span></td>
                                        <td><code class="text-muted">{{ $doc->file_name }}</code></td>
                                        <td>{{ $doc->file_size_human }}</td>
                                        <td>{{ $doc->created_at->format('d M Y h:i A') }}</td>
                                        <td>{{ $doc->uploader->name ?? 'System' }}</td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('documents.view', $doc) }}" target="_blank" class="btn btn-outline-info btn-xs py-1 px-2" style="font-size: 11px; border-radius: 6px;" title="View Document">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="{{ route('documents.download', $doc) }}" class="btn btn-outline-primary btn-xs py-1 px-2" style="font-size: 11px; border-radius: 6px;" title="Download">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                                <form method="POST" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('Are you sure you want to delete this document?')" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-xs py-1 px-2" style="font-size: 11px; border-radius: 6px;" title="Delete">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No documents uploaded yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Upload form for Super Admin/Admin/HR -->
                        <div class="card bg-light border-0" style="border-radius: 8px;">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-3" style="font-size: 13.5px;"><i class="bi bi-upload me-1 text-primary"></i> Upload New Document</h6>
                                <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="documentable_type" value="employee">
                                    <input type="hidden" name="documentable_id" value="{{ $employee->id }}">

                                    <div class="row g-2 align-items-end">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label mb-1" style="font-size: 12px; font-weight: 500;">Document Title (Optional)</label>
                                            <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. Contract, Resume, ID Proof" style="border-radius: 6px;">
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label mb-1" style="font-size: 12px; font-weight: 500;">Select File <span class="text-danger">*</span></label>
                                            <input type="file" name="document" class="form-control form-control-sm" required style="border-radius: 6px;">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <button type="submit" class="btn btn-primary btn-sm w-100" style="border-radius: 6px; padding: 7px 10px;">
                                                Upload
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text mt-2 text-muted" style="font-size: 11px;">Supported formats: PDF, DOC, DOCX, XLS, XLSX, Images. Max: 10MB.</div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     Session Panel â€” Slide-in overlay from the right
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
<div id="session-panel-backdrop" onclick="closeSessionPanel()"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9000; backdrop-filter:blur(2px);"
></div>

<div id="session-panel"
    style="display:none; position:fixed; top:0; right:0; height:100vh; width:100%; max-width:500px;
           background:#fff; z-index:9001; box-shadow:-8px 0 32px rgba(0,0,0,0.18);
           display:flex; flex-direction:column; transform:translateX(100%); transition:transform .3s cubic-bezier(.4,0,.2,1);">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 p-4 border-bottom" style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);">
        <div id="sp-avatar-wrap" style="position:relative;">
            <img id="sp-avatar" src="" alt="" style="width:46px;height:46px;border-radius:50%;border:2px solid rgba(255,255,255,.3);object-fit:cover;">
            <span id="sp-status-dot" style="position:absolute;bottom:1px;right:1px;width:11px;height:11px;border-radius:50%;border:2px solid #1e3a5f;background:#6b7280;"></span>
        </div>
        <div class="flex-grow-1">
            <div class="text-white fw-bold" id="sp-name" style="font-size:15px;"></div>
            <div class="text-white-50" id="sp-email" style="font-size:12px;"></div>
        </div>
        <button type="button" onclick="closeSessionPanel()" class="btn btn-sm btn-outline-light" style="border-radius:50%;width:34px;height:34px;padding:0;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- Sub-header: session count + Force Logout All --}}
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom" style="background:#f8fafc;">
        <div>
            <span class="fw-semibold text-dark" style="font-size:13px;">Active Sessions</span>
            <span id="sp-session-count" class="badge bg-primary-subtle text-primary ms-2" style="font-size:11px;">0</span>
        </div>
        <button id="sp-logout-all-btn" onclick="forceLogoutAll()" class="btn btn-sm btn-danger d-flex align-items-center gap-1" style="font-size:12px;">
            <i class="bi bi-box-arrow-right"></i> Force Logout All
        </button>
    </div>

    {{-- Session List --}}
    <div id="sp-session-list" class="flex-grow-1 overflow-auto p-3">
        {{-- Populated by JS --}}
    </div>
</div>

<!-- Roles & Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-labelledby="permissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; background-color: var(--card-bg, #ffffff);">
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; padding: 20px; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div>
                    <h5 class="modal-title fw-bold" id="permissionsModalLabel">Roles & Direct Permissions</h5>
                    <p class="text-white-50 mb-0" id="permissionsModalUser" style="font-size: 13px;"></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="permissionsForm">
                @csrf
                <div class="modal-body p-4" style="max-height: 62vh; overflow-y: auto;">
                    <!-- Role Select -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em;">Assign User Role</label>
                        <select name="role_id" id="permRoleSelect" class="form-select" style="border-radius: 10px; border: 1.5px solid #cbd5e1;">
                            <!-- Dynamically loaded -->
                        </select>
                    </div>

                    <!-- Direct Permissions Label -->
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <label class="fw-bold text-secondary mb-0" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em;">Direct Permission Overrides</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2" style="font-size: 11px; border-radius: 6px;" onclick="toggleAllCheckboxes(true)">Select All</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2" style="font-size: 11px; border-radius: 6px;" onclick="toggleAllCheckboxes(false)">Deselect All</button>
                        </div>
                    </div>

                    <!-- Permissions Grid grouped by Module -->
                    <div class="row g-3" id="permissionsGrid" style="padding-right: 5px;">
                        <!-- Dynamically loaded grouped permissions -->
                    </div>
                </div>
                <div class="modal-footer border-top p-3" style="background-color: #f8fafc; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-secondary btn-sm" style="border-radius: 8px;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="savePermissionsBtn" class="btn btn-primary btn-sm px-4" style="border-radius: 8px;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Persist active profile tab across page loads/redirects
    document.addEventListener('DOMContentLoaded', () => {
        const profileTabs = document.getElementById('profileTabs');
        if (profileTabs) {
            const activeTabTarget = sessionStorage.getItem('employee_active_tab');
            if (activeTabTarget) {
                const tabButton = document.querySelector(`[data-bs-target="${activeTabTarget}"]`);
                if (tabButton) {
                    const tab = new bootstrap.Tab(tabButton);
                    tab.show();
                }
            }

            profileTabs.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
                btn.addEventListener('shown.bs.tab', (event) => {
                    const target = event.target.getAttribute('data-bs-target');
                    sessionStorage.setItem('employee_active_tab', target);
                });
            });
        }
    });

    let _sessionPanelUserId  = null;
    let _sessionDestroyAllUrl = null;
    let _csrfToken = '{{ csrf_token() }}';

    function openSessionPanel(btn) {
        const userId      = btn.dataset.userId;
        const sessionsUrl = btn.dataset.sessionsUrl;
        _sessionDestroyAllUrl = btn.dataset.destroyAllUrl;
        _sessionPanelUserId   = userId;

        const panel    = document.getElementById('session-panel');
        const backdrop = document.getElementById('session-panel-backdrop');

        // Show skeleton immediately
        document.getElementById('sp-name').textContent  = 'Loadingâ€¦';
        document.getElementById('sp-email').textContent = '';
        document.getElementById('sp-avatar').src        = '';
        document.getElementById('sp-session-count').textContent = 'â€¦';
        document.getElementById('sp-session-list').innerHTML = `
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <div class="mt-2" style="font-size:13px;">Loading sessionsâ€¦</div>
            </div>`;

        panel.style.display    = 'flex';
        backdrop.style.display = 'block';
        requestAnimationFrame(() => { panel.style.transform = 'translateX(0)'; });

        fetch(sessionsUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => renderSessionPanel(data))
            .catch(() => {
                document.getElementById('sp-session-list').innerHTML =
                    '<div class="text-danger p-4">Failed to load sessions.</div>';
            });
    }

    function closeSessionPanel() {
        const panel    = document.getElementById('session-panel');
        const backdrop = document.getElementById('session-panel-backdrop');
        panel.style.transform = 'translateX(100%)';
        setTimeout(() => { panel.style.display = 'none'; backdrop.style.display = 'none'; }, 300);
    }

    function renderSessionPanel(data) {
        const user     = data.user;
        const sessions = data.sessions;

        document.getElementById('sp-name').textContent  = user.name;
        document.getElementById('sp-email').textContent = user.email;
        document.getElementById('sp-avatar').src        = user.avatar;
        document.getElementById('sp-session-count').textContent = sessions.length;

        // Status dot
        const dot = document.getElementById('sp-status-dot');
        if (sessions.length > 0) {
            dot.style.background = '#22c55e';
        } else {
            dot.style.background = '#6b7280';
        }

        const list = document.getElementById('sp-session-list');

        if (sessions.length === 0) {
            list.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-shield-check" style="font-size:40px; opacity:.4;"></i>
                    <div class="mt-3 fw-medium">No active sessions</div>
                    <div style="font-size:12px;">This employee is not currently logged in.</div>
                </div>`;
            return;
        }

        list.innerHTML = sessions.map((s, i) => {
            const deviceIcon = s.device === 'Mobile' ? 'bi-phone'
                             : s.device === 'Tablet' ? 'bi-tablet'
                             : 'bi-display';
            const browserIcon = { Chrome:'bi-browser-chrome', Firefox:'bi-browser-firefox',
                                   Edge:'bi-browser-edge', Safari:'bi-browser-safari' }[s.browser] || 'bi-globe';
            const isCurrent = s.is_current ? `<span class="badge bg-success-subtle text-success ms-1" style="font-size:10px;">You</span>` : '';

            return `
            <div class="session-card mb-3 border rounded-3 overflow-hidden" id="session-card-${s.id.substring(0,8)}">
                {{-- Session Header --}}
                <div class="d-flex align-items-center gap-3 p-3" style="background:#f8fafc;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#e0f2fe;color:#0284c7;font-size:18px;flex-shrink:0;">
                        <i class="bi ${deviceIcon}"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-dark d-flex align-items-center gap-1" style="font-size:13px;">
                            ${s.device} â€” ${s.browser} ${isCurrent}
                        </div>
                        <div class="text-muted" style="font-size:11px;">${s.os}</div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" style="font-size:11px;white-space:nowrap;"
                        onclick="forceLogoutSession('${s.id}')" title="Force Logout This Session">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </div>

                {{-- Session Details Grid --}}
                <div class="p-3 pt-2" style="background:#fff;">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-geo-alt text-danger" style="margin-top:2px;"></i>
                                <div>
                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">IP Address</div>
                                    <div class="fw-medium" style="font-size:12px;word-break:break-all;">${s.ip_address}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-clock text-warning" style="margin-top:2px;"></i>
                                <div>
                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Last Active</div>
                                    <div class="fw-medium" style="font-size:12px;">${s.last_activity}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi ${browserIcon} text-primary" style="margin-top:2px;"></i>
                                <div>
                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Browser</div>
                                    <div class="fw-medium" style="font-size:12px;">${s.browser}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-laptop text-success" style="margin-top:2px;"></i>
                                <div>
                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">OS</div>
                                    <div class="fw-medium" style="font-size:12px;">${s.os}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    function forceLogoutSession(sessionId) {
        if (!confirm('Force logout this specific session?')) return;

        const shortId = sessionId.substring(0, 8);
        const card = document.getElementById('session-card-' + shortId);
        if (card) {
            card.style.opacity = '0.5';
            card.style.pointerEvents = 'none';
        }

        const url = `/users/${_sessionPanelUserId}/sessions/${sessionId}`;

        fetch(url, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': _csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (card) card.remove();
                const countEl = document.getElementById('sp-session-count');
                const newCount = Math.max(0, parseInt(countEl.textContent) - 1);
                countEl.textContent = newCount;
                if (newCount === 0) {
                    document.getElementById('sp-status-dot').style.background = '#6b7280';
                    document.getElementById('sp-session-list').innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check" style="font-size:40px;opacity:.4;"></i>
                            <div class="mt-3 fw-medium">No active sessions</div>
                        </div>`;
                }
            } else {
                alert(data.message || 'Failed to terminate session.');
                if (card) { card.style.opacity = '1'; card.style.pointerEvents = ''; }
            }
        })
        .catch(() => {
            alert('Network error. Please try again.');
            if (card) { card.style.opacity = '1'; card.style.pointerEvents = ''; }
        });
    }

    function forceLogoutAll() {
        if (!confirm('Force logout ALL sessions for this employee? They will be signed out immediately.')) return;

        const btn = document.getElementById('sp-logout-all-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Logging outâ€¦';

        fetch(_sessionDestroyAllUrl, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': _csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('sp-session-count').textContent = 0;
                document.getElementById('sp-status-dot').style.background = '#6b7280';
                document.getElementById('sp-session-list').innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-shield-check" style="font-size:40px;opacity:.4;"></i>
                        <div class="mt-3 fw-medium">All sessions terminated</div>
                        <div style="font-size:12px;">${data.message}</div>
                    </div>`;
                btn.innerHTML = '<i class="bi bi-check"></i> Done';
            } else {
                alert(data.message || 'Failed.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-box-arrow-right"></i> Force Logout All';
            }
        })
        .catch(() => {
            alert('Network error.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-right"></i> Force Logout All';
        });
    }
</script>

<script>
    let currentEmployeeId = null;
    let allRoles = [];
    const permissionsModal = new bootstrap.Modal(document.getElementById('permissionsModal'));

    function openPermissionsModal(employeeId, employeeName) {
        currentEmployeeId = employeeId;
        document.getElementById('permissionsModalUser').textContent = `Manage system access for: ${employeeName}`;
        
        const grid = document.getElementById('permissionsGrid');
        const roleSelect = document.getElementById('permRoleSelect');
        grid.innerHTML = '<div class="col-12 text-center py-5"><span class="spinner-border spinner-border-sm text-primary"></span> Loading permissions...</div>';
        roleSelect.innerHTML = '<option value="">Loading...</option>';

        permissionsModal.show();

        fetch(`/employees/${employeeId}/permissions?_=${Date.now()}`)
            .then(r => r.json())
            .then(data => {
                allRoles = data.roles;
                roleSelect.innerHTML = '';
                data.roles.forEach(role => {
                    const selected = role.id === data.current_role_id ? 'selected' : '';
                    roleSelect.innerHTML += `<option value="${role.id}" ${selected}>${role.name}</option>`;
                });

                grid.innerHTML = '';
                Object.keys(data.permissions).forEach(moduleName => {
                    let moduleHtml = `
                        <div class="col-md-6">
                            <div class="card border-0 shadow-xs mb-2 h-100" style="border-radius: 10px; background-color: #f8fafc; border: 1px solid #e2e8f0 !important;">
                                <div class="card-header bg-light border-0 py-2 fw-bold text-dark d-flex align-items-center justify-content-between" style="font-size: 12.5px; border-top-left-radius: 10px; border-top-right-radius: 10px; background-color: #edf2f7 !important;">
                                    <span>${moduleName}</span>
                                    <input type="checkbox" class="form-check-input module-group-check" data-module="${moduleName}" style="width: 15px; height: 15px; cursor: pointer;" onclick="toggleModuleGroup(this, '${moduleName}')">
                                </div>
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex flex-column gap-2">
                    `;

                    data.permissions[moduleName].forEach(perm => {
                        const checked = perm.checked ? 'checked' : '';
                        moduleHtml += `
                            <div class="form-check d-flex align-items-center gap-2 mb-0">
                                <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="${perm.id}" id="perm_${perm.id}" data-module="${moduleName}" ${checked} style="cursor: pointer;" onchange="updateModuleGroupCheckboxes()">
                                <label class="form-check-label text-dark fs-7" for="perm_${perm.id}" style="cursor: pointer; line-height: 1.2;">
                                    ${perm.name}
                                </label>
                            </div>
                        `;
                    });

                    moduleHtml += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    grid.innerHTML += moduleHtml;
                });

                updateModuleGroupCheckboxes();
            })
            .catch(err => {
                grid.innerHTML = '<div class="col-12 text-center text-danger py-5"><i class="bi bi-exclamation-triangle-fill"></i> Failed to load permissions.</div>';
            });
    }

    function toggleModuleGroup(groupCheckbox, moduleName) {
        const checkboxes = document.querySelectorAll(`.perm-checkbox[data-module="${moduleName}"]`);
        checkboxes.forEach(cb => {
            cb.checked = groupCheckbox.checked;
        });
    }

    function updateModuleGroupCheckboxes() {
        const modules = {};
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            const mod = cb.getAttribute('data-module');
            if (!modules[mod]) {
                modules[mod] = { total: 0, checked: 0 };
            }
            modules[mod].total++;
            if (cb.checked) {
                modules[mod].checked++;
            }
        });

        Object.keys(modules).forEach(mod => {
            const headerCb = document.querySelector(`.module-group-check[data-module="${mod}"]`);
            if (headerCb) {
                headerCb.checked = (modules[mod].total === modules[mod].checked && modules[mod].total > 0);
            }
        });
    }

    function toggleAllCheckboxes(checked) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            cb.checked = checked;
        });
        document.querySelectorAll('.module-group-check').forEach(cb => {
            cb.checked = checked;
        });
    }

    document.getElementById('permRoleSelect').addEventListener('change', function() {
        const selectedRoleId = parseInt(this.value);
        const role = allRoles.find(r => r.id === selectedRoleId);
        if (role && role.permissions) {
            const rolePermIds = role.permissions.map(p => p.id);
            document.querySelectorAll('.perm-checkbox').forEach(cb => {
                const permId = parseInt(cb.value);
                cb.checked = rolePermIds.includes(permId);
            });
            updateModuleGroupCheckboxes();
        }
    });

    document.getElementById('permissionsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('savePermissionsBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

        const formData = new FormData(this);
        
        fetch(`/employees/${currentEmployeeId}/permissions`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': _csrfToken
            }
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
            if (data.success) {
                permissionsModal.hide();
                const alertContainer = document.createElement('div');
                alertContainer.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow-lg';
                alertContainer.style.zIndex = '9999';
                alertContainer.innerHTML = `
                    <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(alertContainer);
                setTimeout(() => {
                    alertContainer.remove();
                    window.location.reload();
                }, 1500);
            } else {
                alert(data.message || 'Something went wrong.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
            alert('Failed to save permissions. Please check connection and try again.');
        });
    });
</script>
@endpush

{{-- ============================================================ --}}
{{-- IMPERSONATION MODAL (Super Admin only) --}}
{{-- ============================================================ --}}
@if(auth()->user()->isSuperAdmin())
<div class="modal fade" id="loginAsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-body p-0">
                {{-- Gradient header --}}
                <div class="d-flex flex-column align-items-center justify-content-center text-white py-5 px-4"
                    style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 shadow"
                        style="width:64px; height:64px; background: rgba(255,255,255,0.15); backdrop-filter: blur(4px);">
                        <i class="bi bi-person-fill-lock" style="font-size: 28px;"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Switch Account</h5>
                    <p class="mb-0 opacity-75 text-center" style="font-size: 13px;">
                        You are about to log in as <strong id="loginAsName"></strong>
                    </p>
                </div>
                {{-- Warning body --}}
                <div class="px-4 py-4">
                    <div class="alert alert-warning border-0 d-flex align-items-start gap-2 mb-4" style="border-radius: 10px; font-size: 13px;">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                        <div>
                            All actions you perform will be recorded under <strong id="loginAsName2"></strong>'s account.
                            Use <strong>"Return to my account"</strong> from the top bar to switch back.
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <form id="loginAsForm" method="POST" action="" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-dark w-100">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Confirm Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function confirmLoginAs(employeeId, employeeName) {
    document.getElementById('loginAsName').textContent = employeeName;
    document.getElementById('loginAsName2').textContent = employeeName;
    document.getElementById('loginAsForm').action = '/employees/' + employeeId + '/login-as';
    const modal = new bootstrap.Modal(document.getElementById('loginAsModal'));
    modal.show();
}
</script>
@endpush
