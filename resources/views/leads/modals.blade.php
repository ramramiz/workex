<!-- Log Call Modal -->
<div class="modal fade" id="logCallModal" tabindex="-1" aria-labelledby="logCallModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="">
                @csrf
                <input type="hidden" name="source" id="logCallSource" value="">
                <input type="hidden" name="duration" id="logCallDuration" value="0">
                <input type="hidden" name="is_followup" id="logCallIsFollowup" value="0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="logCallModalLabel"><i class="bi bi-telephone-outbound me-2 text-success"></i>Log Call Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div id="logCallModalBigContactInfo" class="d-none mb-4 p-3 bg-light rounded-3 text-center border">
                        <div class="fs-4 fw-bold text-dark" id="logCallBigName">Client Name</div>
                        <div class="fs-3 fw-extrabold text-success font-monospace mt-1" id="logCallBigPhone">
                            <i class="bi bi-telephone-fill me-2"></i>**********
                        </div>
                        <div class="mt-2 text-muted fs-8">
                            <i class="bi bi-stopwatch me-1 text-danger"></i>Call Duration: <span id="logCallTimerVal" class="fw-bold text-dark font-monospace">00:00</span>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary fs-7">Call Status <span class="text-danger">*</span></label>
                            <select name="status" id="logCallStatusSelect" class="form-select" required>
                                <option value="Connected">Connected</option>
                                <option value="Not Connected">Not Connected</option>
                                <option value="Busy">Busy</option>
                                <option value="Switched Off">Switched Off</option>
                            </select>
                        </div>

                        <!-- Connected Only Fields -->
                        <div class="col-12 row g-3 m-0 p-0" id="logCallConnectedFields">
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary fs-7">Update Lead Stage <span class="text-danger">*</span></label>
                                <select name="lead_status" id="logCallLeadStatus" class="form-select">
                                    <option value="">Choose Stage...</option>
                                    <option value="new">New Lead</option>
                                    <option value="interested">Interested</option>
                                    <option value="not_interested">Not Interested</option>
                                    <option value="call_back_later">Call Back Later</option>
                                    <option value="follow_up_required">Follow-up Required</option>
                                    <option value="converted">Converted</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary fs-7">Call Remarks / Internal Notes</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Any private remarks or notes..."></textarea>
                            </div>
                            
                            <div class="col-12 border-top pt-3 mt-3" id="logCallModalScheduleSection">
                                <h6 class="fw-semibold text-dark mb-2"><i class="bi bi-calendar-plus me-2 text-warning"></i>Schedule Next Follow-up (Optional)</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-secondary fs-7">Follow Up Date</label>
                                        <input type="date" name="next_follow_up_date" class="form-control">
                                        <div class="mt-2 d-flex flex-wrap gap-1">
                                            <button type="button" class="btn btn-sm quick-date-btn rounded-pill" data-date="{{ date('Y-m-d', strtotime('+1 day')) }}">Tomorrow</button>
                                            <button type="button" class="btn btn-sm quick-date-btn rounded-pill" data-date="{{ date('Y-m-d', strtotime('+2 days')) }}">Day After</button>
                                            <button type="button" class="btn btn-sm quick-date-btn rounded-pill" data-date="{{ date('Y-m-d', strtotime('+7 days')) }}">Next Week</button>
                                            <button type="button" class="btn btn-sm quick-date-btn rounded-pill" data-date="{{ date('Y-m-d', strtotime('+1 month')) }}">Next Month</button>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-secondary fs-7">Follow Up Time</label>
                                        <input type="time" name="next_follow_up_time" class="form-control" value="10:00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Register Call Log</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Follow-up Modal -->
<div class="modal fade" id="scheduleFollowUpModal" tabindex="-1" aria-labelledby="scheduleFollowUpModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="scheduleFollowUpModalLabel"><i class="bi bi-calendar-event me-2 text-warning"></i>Schedule Next Follow-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary fs-7">Next Action / Follow Up Note <span class="text-danger">*</span></label>
                            <input type="text" name="note" class="form-control @error('note') is-invalid @enderror" required placeholder="e.g. Discuss proposal structure or Confirm budget">
                            @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary fs-7">Next Follow Up Date <span class="text-danger">*</span></label>
                            <input type="date" name="next_follow_up" class="form-control @error('next_follow_up') is-invalid @enderror" required value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                            @error('next_follow_up')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary fs-7">Follow Up Time</label>
                            <input type="time" name="follow_up_time" class="form-control" value="10:00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-clock me-1"></i> Set Follow-up</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Book Appointment Modal -->
@php
    $executives = \App\Models\User::where('status', 'active')
        ->whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'super-admin', 'employee', 'team-leader']))
        ->get();
@endphp
<div class="modal fade" id="bookAppointmentModal" tabindex="-1" aria-labelledby="bookAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="bookAppointmentModalLabel"><i class="bi bi-calendar-check me-2 text-primary"></i>Book Appointment / Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary fs-7">Assign Meeting to Sales Executive <span class="text-danger">*</span></label>
                            <select name="sales_executive_id" class="form-select" required>
                                <option value="">Select Executive...</option>
                                @foreach($executives as $ex)
                                    <option value="{{ $ex->id }}">{{ $ex->name }} ({{ $ex->role->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-secondary fs-7">Appointment Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="Demo">Schedule Demo</option>
                                <option value="Visit">On-Site Visit</option>
                                <option value="Online">Online Video Call</option>
                                <option value="Call">Call Discussion</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary fs-7">Meeting Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="meeting_date_time" class="form-control" required value="{{ date('Y-m-d\TH:i', strtotime('+2 days 10:00:00')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary fs-7">Meeting Notes / Instructions</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Provide context, required presentation materials, or customer preferences..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-calendar-plus me-1"></i> Book & Notify Sales Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalsList = ['logCallModal', 'scheduleFollowUpModal', 'bookAppointmentModal'];
        modalsList.forEach(modalId => {
            const modalEl = document.getElementById(modalId);
            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const actionUrl = button.getAttribute('data-bs-action');
                    const form = modalEl.querySelector('form');
                    form.setAttribute('action', actionUrl);
                    
                    // Reset form fields
                    form.reset();
                    
                    // Reset quick date buttons
                    const quickBtnsInside = modalEl.querySelectorAll('.quick-date-btn');
                    quickBtnsInside.forEach(b => b.classList.remove('active'));
                    
                    // Toggle optional schedule next follow-up section for logCallModal
                    if (modalId === 'logCallModal') {
                        const showSchedule = button.getAttribute('data-show-schedule') !== 'false';
                        const scheduleSection = modalEl.querySelector('#logCallModalScheduleSection');
                        if (scheduleSection) {
                            if (showSchedule) {
                                scheduleSection.classList.remove('d-none');
                            } else {
                                scheduleSection.classList.add('d-none');
                            }
                        }

                        // Big Contact Info & Timer Logic
                        const bigName = button.getAttribute('data-bs-client-name');
                        const bigPhone = button.getAttribute('data-bs-client-phone');
                        const isFollowup = button.getAttribute('data-bs-is-followup') === '1';
                        const bigInfoContainer = modalEl.querySelector('#logCallModalBigContactInfo');
                        const sourceInput = modalEl.querySelector('#logCallSource');
                        const durationInput = modalEl.querySelector('#logCallDuration');
                        const isFollowupInput = modalEl.querySelector('#logCallIsFollowup');
                        const timerValEl = modalEl.querySelector('#logCallTimerVal');

                        if (isFollowupInput) {
                            isFollowupInput.value = isFollowup ? '1' : '0';
                        }

                        if (bigName && bigPhone && bigInfoContainer) {
                            bigInfoContainer.classList.remove('d-none');
                            modalEl.querySelector('#logCallBigName').textContent = bigName;
                            modalEl.querySelector('#logCallBigPhone').innerHTML = '<i class="bi bi-telephone-fill me-2"></i>' + bigPhone;
                            sourceInput.value = 'room_work';
                            durationInput.value = '0';
                            timerValEl.textContent = '00:00';

                            // Clear any active timer first
                            if (window.callTimerInterval) {
                                clearInterval(window.callTimerInterval);
                            }

                            // Send AJAX request to store current call details
                            fetch('{{ route("leads.start-work.set-current-call") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    phone: bigPhone,
                                    name: bigName
                                })
                            }).catch(err => console.error('Error starting current call: ', err));

                            let seconds = 0;
                            window.callTimerInterval = setInterval(() => {
                                seconds++;
                                const mins = Math.floor(seconds / 60);
                                const secs = seconds % 60;
                                timerValEl.textContent = [
                                    String(mins).padStart(2, '0'),
                                    String(secs).padStart(2, '0')
                                ].join(':');
                                durationInput.value = seconds;
                            }, 1000);
                        } else {
                            if (bigInfoContainer) bigInfoContainer.classList.add('d-none');
                            if (sourceInput) sourceInput.value = '';
                            if (durationInput) durationInput.value = '0';
                            if (isFollowupInput) isFollowupInput.value = '0';
                            if (window.callTimerInterval) {
                                clearInterval(window.callTimerInterval);
                                window.callTimerInterval = null;
                            }
                        }
                    }
                });

                modalEl.addEventListener('hidden.bs.modal', function() {
                    if (modalId === 'logCallModal') {
                        if (window.callTimerInterval) {
                            clearInterval(window.callTimerInterval);
                            window.callTimerInterval = null;
                        }
                        // Send AJAX request to clear current call
                        fetch('{{ route("leads.start-work.clear-current-call") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).catch(err => console.error('Error clearing current call: ', err));
                    }
                });
            }
        });

        // Quick date speed dial buttons logic
        const dateInput = document.querySelector('input[name="next_follow_up_date"]');
        const quickBtns = document.querySelectorAll('.quick-date-btn');
        
        if (dateInput && quickBtns.length > 0) {
            quickBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    dateInput.value = this.getAttribute('data-date');
                    quickBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            dateInput.addEventListener('input', function() {
                quickBtns.forEach(b => b.classList.remove('active'));
            });
        }

        // Toggle conditional fields in logCallModal based on Call Status
        const statusSelect = document.getElementById('logCallStatusSelect');
        const connectedFields = document.getElementById('logCallConnectedFields');
        const leadStatusInput = document.getElementById('logCallLeadStatus');

        if (statusSelect && connectedFields && leadStatusInput) {
            const toggleStatusFields = () => {
                if (statusSelect.value === 'Connected') {
                    connectedFields.classList.remove('d-none');
                    leadStatusInput.setAttribute('required', 'required');
                } else {
                    connectedFields.classList.add('d-none');
                    leadStatusInput.removeAttribute('required');
                }
            };

            statusSelect.addEventListener('change', toggleStatusFields);
            
            const logCallModalEl = document.getElementById('logCallModal');
            if (logCallModalEl) {
                logCallModalEl.addEventListener('show.bs.modal', function() {
                    statusSelect.value = 'Connected';
                    toggleStatusFields();
                });
            }
        }
    });
</script>
@endpush

@push('styles')
<style>
    .quick-date-btn {
        font-size: 10.5px !important;
        padding: 3px 8px !important;
        transition: all 0.15s ease-in-out;
        border-color: #dee2e6 !important;
        background-color: #f8f9fa;
        color: #495057;
    }
    .quick-date-btn:hover, .quick-date-btn.active {
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
        color: #212529 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
</style>
@endpush
