@extends('layouts.app')

@section('title', 'Sent Mails Log - Hiring')
@section('page-title', 'Sent Mails Log')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('job-vacancies.index') }}">Hiring & Vacancies</a></li>
    <li class="breadcrumb-item active">Sent Mails Log</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <!-- Header Card -->
    <div class="card border shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1 fw-bold text-dark"><i class="bi bi-envelope-paper-fill text-primary me-2"></i>Sent Mails Log</h4>
                <p class="text-secondary mb-0">Review all interview invitation and call letter emails sent to candidate applicants.</p>
            </div>
            <a href="{{ route('job-vacancies.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius: 10px;">
                <i class="bi bi-arrow-left"></i> Back to Vacancies
            </a>
        </div>
    </div>

    <!-- Logs List -->
    <div class="card border shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 py-3" style="font-size: 13px; font-weight: 600;">Candidate</th>
                            <th class="py-3" style="font-size: 13px; font-weight: 600;">Job Position</th>
                            <th class="py-3" style="font-size: 13px; font-weight: 600;">Scheduled Date/Time</th>
                            <th class="py-3" style="font-size: 13px; font-weight: 600;">Interview Venue</th>
                            <th class="py-3" style="font-size: 13px; font-weight: 600;">Sent By</th>
                            <th class="pe-4 py-3 text-end" style="font-size: 13px; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; min-width: 36px;">
                                            <i class="bi bi-person-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 14px;">{{ $log->candidate_name }}</div>
                                            <small class="text-secondary d-block" style="font-size: 12.5px;">
                                                <i class="bi bi-envelope me-1"></i>{{ $log->candidate_email }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="fw-semibold text-dark" style="font-size: 14px;">{{ $log->vacancy_title }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="fw-semibold text-dark" style="font-size: 13.5px;">
                                        <i class="bi bi-calendar-event me-1 text-primary"></i>{{ \Carbon\Carbon::parse($log->interview_date)->format('d M Y') }}
                                    </div>
                                    <small class="text-secondary" style="font-size: 12.5px;">
                                        <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($log->interview_time)->format('h:i A') }}
                                    </small>
                                </td>
                                <td class="py-3">
                                    <div class="text-secondary text-truncate" style="max-width: 250px; font-size: 13px;" title="{{ $log->interview_venue }}">
                                        <i class="bi bi-geo-alt-fill me-1 text-danger"></i>{{ $log->interview_venue }}
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="fw-semibold text-dark" style="font-size: 13.5px;">{{ $log->sender->name ?? 'System' }}</div>
                                    <small class="text-secondary" style="font-size: 12px;">
                                        {{ $log->created_at->format('d M Y, h:i A') }}
                                    </small>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <button type="button" class="btn btn-sm btn-light border px-2.5 py-1.5" 
                                            style="border-radius: 8px;"
                                            onclick="showMailDetails({
                                                candidate_name: '{{ addslashes($log->candidate_name) }}',
                                                candidate_email: '{{ addslashes($log->candidate_email) }}',
                                                vacancy_title: '{{ addslashes($log->vacancy_title) }}',
                                                subject: '{{ addslashes($log->subject) }}',
                                                interview_date: '{{ \Carbon\Carbon::parse($log->interview_date)->format('F d, Y') }}',
                                                interview_time: '{{ \Carbon\Carbon::parse($log->interview_time)->format('h:i A') }}',
                                                interview_venue: '{{ addslashes($log->interview_venue) }}',
                                                sender_name: '{{ addslashes($log->sender->name ?? 'System') }}',
                                                sent_at: '{{ $log->created_at->format('d M Y, h:i A') }}'
                                            })">
                                        <i class="bi bi-file-earmark-text me-1 text-info"></i> View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-envelope-paper text-muted opacity-50" style="font-size: 48px;"></i>
                                    <h5 class="fw-bold text-dark mt-3 mb-1">No Mails Logged</h5>
                                    <p class="text-secondary mb-0">Logs of successfully sent interview call letters will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="mailDetailsModal" tabindex="-1" aria-labelledby="mailDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark" id="mailDetailsModalLabel">
                    <i class="bi bi-file-earmark-text-fill text-info me-2"></i>Mail Invitation Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-light-subtle">
                <div class="mb-3 border-bottom pb-2">
                    <label class="form-label text-muted small text-uppercase mb-0 font-weight-bold">Candidate</label>
                    <div class="fw-bold text-dark" id="m-candidate"></div>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <label class="form-label text-muted small text-uppercase mb-0 font-weight-bold">Job Position</label>
                    <div class="fw-semibold text-dark" id="m-vacancy"></div>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <label class="form-label text-muted small text-uppercase mb-0 font-weight-bold">Email Subject</label>
                    <div class="text-dark" id="m-subject"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label text-muted small text-uppercase mb-0 font-weight-bold">Interview Date</label>
                        <div class="fw-semibold text-dark" id="m-date"></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small text-uppercase mb-0 font-weight-bold">Interview Time</label>
                        <div class="fw-semibold text-dark" id="m-time"></div>
                    </div>
                </div>

                <div class="mb-3 border-bottom pb-2">
                    <label class="form-label text-muted small text-uppercase mb-0 font-weight-bold">Venue Details</label>
                    <div class="text-dark bg-white border rounded p-2.5 mt-1" style="white-space: pre-wrap; font-size: 0.9rem;" id="m-venue"></div>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label text-muted small text-uppercase mb-0 font-weight-bold">Sent By</label>
                        <div class="text-dark" id="m-sender"></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small text-uppercase mb-0 font-weight-bold">Sent Date/Time</label>
                        <div class="text-dark" id="m-sent-at"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-top py-2.5">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showMailDetails(data) {
        document.getElementById('m-candidate').textContent = `${data.candidate_name} (${data.candidate_email})`;
        document.getElementById('m-vacancy').textContent = data.vacancy_title;
        document.getElementById('m-subject').textContent = data.subject;
        document.getElementById('m-date').textContent = data.interview_date;
        document.getElementById('m-time').textContent = data.interview_time;
        document.getElementById('m-venue').textContent = data.interview_venue;
        document.getElementById('m-sender').textContent = data.sender_name;
        document.getElementById('m-sent-at').textContent = data.sent_at;
        
        const modal = new bootstrap.Modal(document.getElementById('mailDetailsModal'));
        modal.show();
    }
</script>
@endsection
