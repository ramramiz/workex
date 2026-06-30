@extends('layouts.app')

@section('title', 'Applications - ' . $jobVacancy->title)
@section('page-title', 'Job Applications')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('job-vacancies.index') }}">Hiring & Vacancies</a></li>
    <li class="breadcrumb-item active">Applications</li>
@endsection

@section('content')
<style>
    .hover-underline:hover {
        text-decoration: underline !important;
    }
</style>
<div class="mb-4">
    <div class="card shadow-sm border-0 bg-light-subtle">
        <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="mb-1 fw-bold text-dark">{{ $jobVacancy->title }}</h4>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    @if($jobVacancy->department)
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded">
                            <i class="bi bi-building me-1"></i>{{ $jobVacancy->department->name }}
                        </span>
                    @endif
                    <span class="text-secondary"><i class="bi bi-geo-alt-fill me-1"></i>{{ $jobVacancy->location ?? 'Not Specified' }}</span>
                    <span class="text-secondary"><i class="bi bi-clock-fill me-1"></i>{{ $jobVacancy->job_type }}</span>
                </div>
            </div>
            <a href="{{ route('job-vacancies.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Back to Vacancies
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">Candidates ({{ $applications->total() }})</h5>
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2" id="btnInterviewCall" data-bs-toggle="modal" data-bs-target="#interviewScheduleModal" disabled>
            <i class="bi bi-calendar-event-fill"></i> Interview Call
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3" style="width: 50px;">
                            <input type="checkbox" id="selectAllCandidates" class="form-check-input">
                        </th>
                        <th class="py-3" style="width: 25%">Candidate Name</th>
                        <th class="py-3" style="width: 22%">Contact Details</th>
                        <th class="py-3" style="width: 15%">Applied Date</th>
                        <th class="py-3" style="width: 15%">Resume</th>
                        <th class="py-3 text-center" style="width: 13%">Application Status</th>
                        <th class="pe-4 py-3 text-end" style="width: 10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                        <tr>
                            <td class="ps-4" style="width: 50px;">
                                <input type="checkbox" class="candidate-select form-check-input" value="{{ $app->id }}">
                            </td>
                            <td>
                                <div class="fw-semibold">
                                    <a href="#" class="text-primary text-decoration-none hover-underline" 
                                       style="cursor: pointer;"
                                       onclick="event.preventDefault(); showCandidateProfile({
                                           name: '{{ addslashes($app->name) }}',
                                           gender: '{{ addslashes($app->gender) }}',
                                           dob: '{{ $app->dob }}',
                                           qualification: '{{ addslashes($app->qualification) }}',
                                           email: '{{ addslashes($app->email) }}',
                                           phone: '{{ addslashes($app->phone) }}',
                                           state: '{{ addslashes($app->state) }}',
                                           district: '{{ addslashes($app->district) }}',
                                           home_town: '{{ addslashes($app->home_town) }}',
                                           experience: '{{ addslashes($app->experience_years) }}',
                                           salary: '{{ addslashes($app->salary_expectation) }}',
                                           relocate: '{{ addslashes($app->ready_to_relocate) }}',
                                           linkedin: '{{ addslashes($app->linkedin_id) }}',
                                           cover_letter: '{{ addslashes(e($app->cover_letter)) }}',
                                           resume_url: '{{ asset('storage/' . $app->resume_path) }}'
                                       })">
                                        {{ $app->name }}
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark"><i class="bi bi-envelope me-1.5 text-muted"></i>{{ $app->email }}</div>
                                <div class="text-secondary mt-0.5"><i class="bi bi-telephone me-1.5 text-muted"></i>{{ $app->phone }}</div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $app->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $app->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <a href="{{ asset('storage/' . $app->resume_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-6"></i> Download Resume
                                </a>
                            </td>
                            <td>
                                <div class="d-flex flex-column align-items-center">
                                    <form action="{{ route('job-applications.update-status', $app->id) }}" method="POST" class="d-flex justify-content-center">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()" 
                                                class="form-select form-select-sm fw-semibold rounded-pill px-3 text-center border-0 style-status-dropdown 
                                                @if($app->status === 'pending') bg-warning-subtle text-warning border-warning-subtle
                                                @elseif($app->status === 'reviewed' || $app->status === 'interview_scheduled') bg-info-subtle text-info border-info-subtle
                                                @elseif($app->status === 'accepted') bg-success-subtle text-success border-success-subtle
                                                @elseif($app->status === 'rejected') bg-danger-subtle text-danger border-danger-subtle @endif"
                                                style="width: 130px; cursor: pointer;">
                                            <option value="pending" {{ $app->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="reviewed" {{ in_array($app->status, ['reviewed', 'interview_scheduled']) ? 'selected' : '' }}>Reviewed</option>
                                            <option value="accepted" {{ $app->status === 'accepted' ? 'selected' : '' }}>Accepted</option>
                                            <option value="rejected" {{ $app->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                    </form>
                                    @if($app->status === 'interview_scheduled' && $app->interview_date)
                                        <div class="text-primary fw-bold mt-1 text-center" style="font-size: 0.72rem; letter-spacing: -0.1px;">
                                            <i class="bi bi-calendar-event me-0.5"></i>Scheduled: {{ \Carbon\Carbon::parse($app->interview_date)->format('M d') }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-light border text-info" 
                                            onclick="showCandidateProfile({
                                                name: '{{ addslashes($app->name) }}',
                                                gender: '{{ addslashes($app->gender) }}',
                                                dob: '{{ $app->dob }}',
                                                qualification: '{{ addslashes($app->qualification) }}',
                                                email: '{{ addslashes($app->email) }}',
                                                phone: '{{ addslashes($app->phone) }}',
                                                state: '{{ addslashes($app->state) }}',
                                                district: '{{ addslashes($app->district) }}',
                                                home_town: '{{ addslashes($app->home_town) }}',
                                                experience: '{{ addslashes($app->experience_years) }}',
                                                salary: '{{ addslashes($app->salary_expectation) }}',
                                                relocate: '{{ addslashes($app->ready_to_relocate) }}',
                                                linkedin: '{{ addslashes($app->linkedin_id) }}',
                                                cover_letter: '{{ addslashes(e($app->cover_letter)) }}',
                                                resume_url: '{{ asset('storage/' . $app->resume_path) }}'
                                            })"
                                            data-bs-toggle="tooltip" 
                                            title="View Full Profile">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    <form action="{{ route('job-applications.destroy', $app->id) }}" method="POST" 
                                          class="d-inline" onsubmit="return confirm('Are you sure you want to delete/decline this application?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" 
                                                data-bs-toggle="tooltip" 
                                                title="Delete Application">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-people text-secondary display-4 d-block mb-3"></i>
                                    No candidates have applied for this job vacancy yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($applications->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Candidate Profile Modal -->
<div class="modal fade" id="candidateProfileModal" tabindex="-1" aria-labelledby="candidateProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark" id="candidateProfileModalLabel">
                    <i class="bi bi-person-badge-fill text-primary me-2"></i>Candidate Profile Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light-subtle">
                <div class="row g-3">
                    
                    <!-- Left Column: Personal info -->
                    <div class="col-md-6">
                        <div class="bg-white p-3 rounded border h-100">
                            <h6 class="text-uppercase text-primary fw-bold fs-7 mb-3"><i class="bi bi-person-fill me-1.5"></i>Personal Details</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted py-1.5" style="width: 40%">Full Name:</td>
                                    <td class="fw-semibold text-dark py-1.5" id="p-name"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-1.5">Gender:</td>
                                    <td class="fw-semibold text-dark py-1.5" id="p-gender"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-1.5">Date of Birth:</td>
                                    <td class="fw-semibold text-dark py-1.5" id="p-dob"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-1.5">Qualification:</td>
                                    <td class="fw-semibold text-dark py-1.5" id="p-qualification"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Right Column: Employment Details -->
                    <div class="col-md-6">
                        <div class="bg-white p-3 rounded border h-100">
                            <h6 class="text-uppercase text-primary fw-bold fs-7 mb-3"><i class="bi bi-briefcase-fill me-1.5"></i>Employment Info</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted py-1.5" style="width: 45%">Experience:</td>
                                    <td class="fw-semibold text-dark py-1.5" id="p-experience"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-1.5">Salary Expectation:</td>
                                    <td class="fw-semibold text-dark py-1.5" id="p-salary"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-1.5">Ready to Relocate:</td>
                                    <td class="fw-semibold text-dark py-1.5" id="p-relocate"></td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-1.5">LinkedIn Profile:</td>
                                    <td class="py-1.5" id="p-linkedin-container">
                                        <a href="#" id="p-linkedin" target="_blank" class="fw-semibold text-primary text-decoration-none">View Profile <i class="bi bi-box-arrow-up-right small ms-0.5"></i></a>
                                        <span id="p-linkedin-none" class="text-muted fw-semibold">—</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Contact Details -->
                    <div class="col-md-12">
                        <div class="bg-white p-3 rounded border">
                            <h6 class="text-uppercase text-primary fw-bold fs-7 mb-3"><i class="bi bi-telephone-fill me-1.5"></i>Contact & Address Details</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <span class="text-muted me-2">Email:</span>
                                    <span class="fw-semibold text-dark" id="p-email"></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted me-2">Phone Number:</span>
                                    <span class="fw-semibold text-dark" id="p-phone"></span>
                                </div>
                                <div class="col-12 mt-2 border-top pt-2">
                                    <span class="text-muted me-2"><i class="bi bi-geo-alt-fill me-1"></i>Address:</span>
                                    <span class="fw-semibold text-dark" id="p-address"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cover Letter -->
                    <div class="col-md-12">
                        <div class="bg-white p-3 rounded border">
                            <h6 class="text-uppercase text-primary fw-bold fs-7 mb-2"><i class="bi bi-chat-text-fill me-1.5"></i>Cover Letter / Message</h6>
                            <div id="p-cover-letter" class="p-2.5 rounded border bg-light text-secondary" style="white-space: pre-wrap; font-size: 0.9rem; min-height: 80px; line-height: 1.5;"></div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer border-top py-2.5 d-flex justify-content-between">
                <a href="#" id="p-resume-btn" target="_blank" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i> Download Resume File
                </a>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showCandidateProfile(data) {
        document.getElementById('p-name').textContent = data.name;
        document.getElementById('p-gender').textContent = data.gender || '—';
        document.getElementById('p-dob').textContent = data.dob ? formatDate(data.dob) : '—';
        document.getElementById('p-qualification').textContent = data.qualification || '—';
        document.getElementById('p-email').textContent = data.email;
        document.getElementById('p-phone').textContent = data.phone;
        document.getElementById('p-experience').textContent = data.experience || '—';
        document.getElementById('p-salary').textContent = data.salary || '—';
        document.getElementById('p-relocate').textContent = data.relocate || '—';
        
        // Address
        if (data.home_town || data.district || data.state) {
            document.getElementById('p-address').textContent = `${data.home_town || ''}, ${data.district || ''}, ${data.state || ''}`;
        } else {
            document.getElementById('p-address').textContent = '—';
        }
        
        // LinkedIn Link
        const lnLink = document.getElementById('p-linkedin');
        const lnNone = document.getElementById('p-linkedin-none');
        if (data.linkedin) {
            lnLink.href = data.linkedin.startsWith('http') ? data.linkedin : 'https://' + data.linkedin;
            lnLink.classList.remove('d-none');
            lnNone.classList.add('d-none');
        } else {
            lnLink.classList.add('d-none');
            lnNone.classList.remove('d-none');
        }

        // Cover Letter
        const clDiv = document.getElementById('p-cover-letter');
        if (data.cover_letter) {
            clDiv.innerHTML = data.cover_letter;
            clDiv.classList.remove('text-muted');
        } else {
            clDiv.innerHTML = '<em>No cover letter submitted by candidate.</em>';
            clDiv.classList.add('text-muted');
        }

        // Resume Link
        document.getElementById('p-resume-btn').href = data.resume_url;

        // Show Modal
        const modal = new bootstrap.Modal(document.getElementById('candidateProfileModal'));
        modal.show();
    }

    function formatDate(dateString) {
        if (!dateString) return '—';
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    // Select/Deselect All Checkboxes
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAllCandidates');
        const candidateCheckboxes = document.querySelectorAll('.candidate-select');
        const btnInterviewCall = document.getElementById('btnInterviewCall');
        const hiddenContainer = document.getElementById('hiddenCandidateIdsContainer');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                candidateCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                toggleInterviewButton();
            });
        }

        candidateCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (!this.checked && selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                }
                const allChecked = Array.from(candidateCheckboxes).every(c => c.checked);
                if (allChecked && selectAllCheckbox) {
                    selectAllCheckbox.checked = true;
                }
                toggleInterviewButton();
            });
        });

        function toggleInterviewButton() {
            const anyChecked = Array.from(candidateCheckboxes).some(cb => cb.checked);
            if (btnInterviewCall) {
                btnInterviewCall.disabled = !anyChecked;
            }
        }

        // Modal Form submit intercept to append selected candidate IDs
        const interviewForm = document.getElementById('interviewScheduleForm');
        if (interviewForm) {
            interviewForm.addEventListener('submit', function(e) {
                if (hiddenContainer) {
                    hiddenContainer.innerHTML = '';
                    const checkedBoxes = document.querySelectorAll('.candidate-select:checked');
                    checkedBoxes.forEach(cb => {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'candidate_ids[]';
                        hiddenInput.value = cb.value;
                        hiddenContainer.appendChild(hiddenInput);
                    });
                }
            });
        }
    });
</script>

<!-- Interview Schedule Modal -->
<div class="modal fade" id="interviewScheduleModal" tabindex="-1" aria-labelledby="interviewScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <form id="interviewScheduleForm" method="POST" action="{{ route('job-applications.schedule-interview') }}">
                @csrf
                <div id="hiddenCandidateIdsContainer"></div>

                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark" id="interviewScheduleModalLabel">
                        <i class="bi bi-calendar-check text-primary me-2"></i>Schedule Interview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4 bg-light-subtle">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Interview Date <span class="text-danger">*</span></label>
                        <input type="date" name="interview_date" class="form-control" required min="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Time <span class="text-danger">*</span></label>
                        <input type="time" name="interview_time" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Venue <span class="text-danger">*</span></label>
                        <textarea name="interview_venue" class="form-control" rows="3" required placeholder="e.g. Infopark Kochi, Room 4B / Zoom Meeting Link"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Send Call Letters</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
