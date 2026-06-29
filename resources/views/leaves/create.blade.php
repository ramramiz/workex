@extends('layouts.app')

@section('title', 'Apply for Leave')
@section('page-title', 'Apply for Leave')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leaves</a></li>
    <li class="breadcrumb-item active">Apply</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-md-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Request Time Off</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leaves.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type" class="form-select @error('leave_type') is-invalid @enderror" required>
                            <option value="">-- Select Type --</option>
                            <option value="sick_leave" {{ old('leave_type') === 'sick_leave' ? 'selected' : '' }} {{ $sickDisabled ? 'disabled' : '' }}>
                                Sick Leave @if($sickDisabled) (Taken on {{ $sickDate }}) @endif
                            </option>
                            <option value="casual_leave" {{ old('leave_type') === 'casual_leave' ? 'selected' : '' }} {{ $casualDisabled ? 'disabled' : '' }}>
                                Casual Leave 
                                @if($casualDisabled)
                                    @if($casualDate)
                                        (Taken on {{ $casualDate }})
                                    @else
                                        (Disabled: 2 half days taken)
                                    @endif
                                @endif
                            </option>
                            <option value="unpaid_leave" {{ old('leave_type') === 'unpaid_leave' ? 'selected' : '' }}>Unpaid Leave</option>
                            <option value="half_day" {{ old('leave_type') === 'half_day' ? 'selected' : '' }}>Half Day Leave</option>
                        </select>
                        @error('leave_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3" id="halfDaySessionGroup" style="display: none;">
                        <label class="form-label">Half Day Session <span class="text-danger">*</span></label>
                        <select name="half_day_session" class="form-select @error('half_day_session') is-invalid @enderror">
                            <option value="">-- Select Shift --</option>
                            <option value="morning" {{ old('half_day_session') === 'morning' ? 'selected' : '' }}>Morning Shift</option>
                            <option value="evening" {{ old('half_day_session') === 'evening' ? 'selected' : '' }}>Evening Shift</option>
                        </select>
                        @error('half_day_session')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3" id="medicalDocumentGroup" style="display: none;">
                        <label class="form-label">Medical Document <span class="text-muted">(Optional)</span></label>
                        <input type="file" name="medical_document" class="form-control @error('medical_document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">Upload a medical document (PDF, JPG, JPEG, PNG). Max 5MB.</small>
                        @error('medical_document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">From Date <span class="text-danger">*</span></label>
                            <input type="date" name="from_date" class="form-control @error('from_date') is-invalid @enderror" value="{{ old('from_date', date('Y-m-d')) }}" required>
                            @error('from_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">To Date <span class="text-danger">*</span></label>
                            <input type="date" name="to_date" class="form-control @error('to_date') is-invalid @enderror" value="{{ old('to_date', date('Y-m-d')) }}" required>
                            @error('to_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Reason / Justification <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" required placeholder="Explain your reason for requesting time off..."></textarea>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const leaveTypeSelect = document.querySelector('select[name="leave_type"]');
        const fromDateInput = document.querySelector('input[name="from_date"]');
        const toDateInput = document.querySelector('input[name="to_date"]');
        const toDateCol = toDateInput.closest('.col-6');
        const medicalDocGroup = document.getElementById('medicalDocumentGroup');
        const halfDaySessionGroup = document.getElementById('halfDaySessionGroup');
        const halfDaySessionSelect = halfDaySessionGroup.querySelector('select');

        function handleLeaveTypeChange() {
            if (leaveTypeSelect.value === 'half_day' || leaveTypeSelect.value === 'casual_leave' || leaveTypeSelect.value === 'sick_leave') {
                toDateInput.readOnly = true;
                toDateInput.value = fromDateInput.value;
                if (toDateCol) {
                    toDateCol.style.opacity = '0.5';
                    toDateCol.style.pointerEvents = 'none';
                }
            } else {
                toDateInput.readOnly = false;
                if (toDateCol) {
                    toDateCol.style.opacity = '1';
                    toDateCol.style.pointerEvents = 'auto';
                }
            }

            if (leaveTypeSelect.value === 'half_day') {
                halfDaySessionGroup.style.display = 'block';
                halfDaySessionSelect.required = true;
            } else {
                halfDaySessionGroup.style.display = 'none';
                halfDaySessionSelect.required = false;
                halfDaySessionSelect.value = '';
            }

            if (leaveTypeSelect.value === 'sick_leave') {
                medicalDocGroup.style.display = 'block';
            } else {
                medicalDocGroup.style.display = 'none';
                const fileInput = medicalDocGroup.querySelector('input[type="file"]');
                if (fileInput) fileInput.value = '';
            }
        }

        leaveTypeSelect.addEventListener('change', handleLeaveTypeChange);
        fromDateInput.addEventListener('change', function () {
            if (leaveTypeSelect.value === 'half_day' || leaveTypeSelect.value === 'casual_leave' || leaveTypeSelect.value === 'sick_leave') {
                toDateInput.value = fromDateInput.value;
            }
        });

        // Run once on load
        handleLeaveTypeChange();
    });
</script>
@endsection
