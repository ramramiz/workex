@extends('layouts.app')

@section('title', 'Edit Leave Request')
@section('page-title', 'Edit Leave Request')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leaves</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-md-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Leave Request</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leaves.update', $leave) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type" class="form-select @error('leave_type') is-invalid @enderror" required>
                            <option value="sick_leave" {{ old('leave_type', $leave->leave_type) === 'sick_leave' ? 'selected' : '' }}>Sick Leave</option>
                            <option value="casual_leave" {{ old('leave_type', $leave->leave_type) === 'casual_leave' ? 'selected' : '' }}>Casual Leave</option>
                            <option value="annual_leave" {{ old('leave_type', $leave->leave_type) === 'annual_leave' ? 'selected' : '' }}>Annual Leave</option>
                            <option value="unpaid_leave" {{ old('leave_type', $leave->leave_type) === 'unpaid_leave' ? 'selected' : '' }}>Unpaid Leave</option>
                            <option value="other" {{ old('leave_type', $leave->leave_type) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('leave_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">From Date <span class="text-danger">*</span></label>
                            <input type="date" name="from_date" class="form-control @error('from_date') is-invalid @enderror" value="{{ old('from_date', $leave->from_date ? $leave->from_date->format('Y-m-d') : '') }}" required>
                            @error('from_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">To Date <span class="text-danger">*</span></label>
                            <input type="date" name="to_date" class="form-control @error('to_date') is-invalid @enderror" value="{{ old('to_date', $leave->to_date ? $leave->to_date->format('Y-m-d') : '') }}" required>
                            @error('to_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Reason / Justification <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" required>{{ old('reason', $leave->reason) }}</textarea>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
