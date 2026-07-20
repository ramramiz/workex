@extends('layouts.app')

@section('title', 'Onboarding Links')
@section('page-title', 'Intern Onboarding')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('interns.index') }}">Interns</a></li>
    <li class="breadcrumb-item active">Onboarding Links</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Quick Navigation / Stats -->
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="btn-group shadow-sm">
                <a href="{{ route('interns.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-people-fill"></i> Active Interns Directory
                </a>
                <a href="{{ route('interns.onboardings.index') }}" class="btn btn-primary btn-sm active">
                    <i class="bi bi-link-45deg"></i> Onboarding Links
                </a>
            </div>

            @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
                <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#generateLinkModal">
                    <i class="bi bi-plus-circle-fill"></i> Generate Onboarding Link
                </button>
            @endif
        </div>
    </div>

    <!-- Onboarding List Card -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark">Onboarding Form Registrations</h5>
            </div>
            
            <!-- Filters -->
            <div class="card-body border-bottom py-3" style="background: var(--body-bg);">
                <form method="GET" action="{{ route('interns.onboardings.index') }}" class="row g-3">
                    <div class="col-12 col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Submission</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted (Review Required)</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-4 flex-grow-1">Filter</button>
                        @if(request()->filled('search') || request()->filled('status'))
                            <a href="{{ route('interns.onboardings.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Candidate Details</th>
                            <th>Placement Details</th>
                            <th>Onboarding Status</th>
                            <th>Public Form Link</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($onboardings as $ob)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($ob->intern->name ?? 'Pending') }}&background=10b981&color=fff" class="avatar-circle" style="width: 40px; height: 40px; border-radius: 50%;">
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $ob->intern->name ?? 'N/A' }}</div>
                                            <div class="text-secondary style-subtext" style="font-size: 12.5px;">{{ $ob->intern->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $ob->intern->department->name ?? 'N/A' }}</div>
                                    <div class="text-secondary style-subtext" style="font-size: 12.5px;">{{ $ob->sector ?? $ob->intern->sector ?? 'Intern' }}</div>
                                </td>
                                <td>
                                    @if($ob->status === 'pending')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill">Pending Form Fill</span>
                                    @elseif($ob->status === 'submitted')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded-pill"><i class="bi bi-exclamation-circle-fill me-1"></i> Review Required</span>
                                    @elseif($ob->status === 'completed')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">Onboarding Completed</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ob->status !== 'completed')
                                        <div class="input-group input-group-sm" style="max-width: 320px;">
                                            <input type="text" class="form-control bg-light" id="link-{{ $ob->id }}" value="{{ route('interns.onboard.show', $ob->token) }}" readonly style="font-size: 11px;">
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyOnboardingLink('link-{{ $ob->id }}', this)" title="Copy Link">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-muted fs-8"><i class="bi bi-lock-fill me-1"></i> Link Deactivated</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-2">
                                        @if($ob->status === 'pending')
                                            <!-- Send/Resend Email Invitation -->
                                            <form action="{{ route('interns.onboardings.send-email', $ob->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5" title="Send email invitation containing link">
                                                    <i class="bi bi-envelope-at-fill"></i> Send Email
                                                </button>
                                            </form>
                                        @elseif($ob->status === 'submitted')
                                            <!-- Review Submission -->
                                            <a href="{{ route('interns.onboardings.review', $ob->id) }}" class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1.5 text-dark fw-medium shadow-sm">
                                                <i class="bi bi-clipboard2-check-fill"></i> Review & Approve
                                            </a>
                                        @elseif($ob->status === 'completed')
                                            <!-- View Form Details -->
                                            <a href="{{ route('interns.onboardings.view', $ob->id) }}" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1.5" title="Print/View completed form details">
                                                <i class="bi bi-printer-fill"></i> Print Form
                                            </a>

                                            <!-- Edit Onboarding Details -->
                                            <a href="{{ route('interns.onboardings.edit', $ob->id) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5" title="Edit onboarding details">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>

                                            @if(empty($ob->intern->certificate_code) || $ob->intern->status === 'pending_onboarding')
                                                <!-- Generate Certificate Button -->
                                                <form action="{{ route('interns.onboardings.generate-certificate', $ob->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5 fw-medium shadow-sm" title="Generate Certificate Code and Activate Intern Profile">
                                                        <i class="bi bi-patch-check-fill"></i> Generate Certificate
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
                                            <form action="{{ route('interns.onboardings.destroy', $ob->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this onboarding record? This will also delete the associated intern record.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" title="Delete record">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-secondary">
                                    <i class="bi bi-link-45deg" style="font-size: 36px;"></i>
                                    <div class="mt-2 fw-medium">No onboarding form links generated.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($onboardings->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $onboardings->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Generate Onboarding Link -->
<div class="modal fade" id="generateLinkModal" tabindex="-1" aria-labelledby="generateLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="generateLinkModalLabel">Generate Onboarding Link</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('interns.onboardings.generate') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Full Name (As per Aadhaar) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Rahul Sharma" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. rahul@domain.com" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="modal_department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Sector <span class="text-danger">*</span></label>
                            <select name="sector" class="form-select" required>
                                <option value="">Select Sector</option>
                                <option value="Techsoul Technologies">Techsoul Technologies (Development Related)</option>
                                <option value="Techsoul IT Solutions">Techsoul IT Solutions (IT Hardware and Networking Related)</option>
                                <option value="Techsoul Solar">Techsoul Solar (Solar and energy related)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Mentor / Supervisor</label>
                            <select name="mentor_supervisor" class="form-select">
                                <option value="">Select Mentor / Supervisor</option>
                                @foreach($teamLeaders as $leader)
                                    <option value="{{ $leader->name }}">{{ $leader->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Internship Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="joining_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Internship End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Generate Link</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const departments = @json($departments);

    function filterModalDesignations() {
        const deptId = document.getElementById('modal_department_id').value;
        const desigSelect = document.getElementById('modal_designation_id');
        
        // Reset designations
        desigSelect.innerHTML = '<option value="">Select Designation</option>';
        
        if (!deptId) return;
        
        const department = departments.find(d => d.id == deptId);
        if (department && department.designations) {
            department.designations.forEach(desig => {
                const opt = document.createElement('option');
                opt.value = desig.id;
                opt.textContent = desig.name;
                desigSelect.appendChild(opt);
            });
        }
    }

    function copyOnboardingLink(inputId, button) {
        const copyText = document.getElementById(inputId);
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        
        navigator.clipboard.writeText(copyText.value).then(() => {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
            button.classList.add('btn-outline-success');
            button.classList.remove('btn-outline-secondary');
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.add('btn-outline-secondary');
                button.classList.remove('btn-outline-success');
            }, 2000);
        }).catch(err => {
            alert('Failed to copy text: ' . err);
        });
    }
</script>
@endpush
