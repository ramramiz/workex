@extends('layouts.app')

@section('title', 'Disburse Salary')
@section('page-title', 'Disburse Salary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll</a></li>
    <li class="breadcrumb-item active">Disburse</li>
@endsection

@push('styles')
<style>
    .calculation-row input, .calculation-row select {
        padding: 4px 8px;
        font-size: 0.85rem;
        border-radius: 6px;
    }
    .net-salary-display {
        font-weight: 700;
        color: var(--primary);
        font-size: 1rem;
    }
</style>
@endpush

@section('content')
<!-- Month/Year Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.payroll.create') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label fs-8 text-muted mb-1 text-uppercase fw-bold">Salary Month</label>
                <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fs-8 text-muted mb-1 text-uppercase fw-bold">Salary Year</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <a href="{{ route('admin.payroll.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Payroll</a>
            </div>
        </form>
    </div>
</div>

<!-- Payroll Sheet Grid -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Payroll Sheet: {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</h5>
    </div>
    
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead>
                <tr>
                    <th>Employee Details</th>
                    <th>Salary Structure</th>
                    <th>Attendance Parameters</th>
                    <th>Gross Salary</th>
                    <th style="width: 110px;">Allowances</th>
                    <th style="width: 110px;">Deductions</th>
                    <th>Net Salary</th>
                    <th>Payment Method</th>
                    <th>Remarks</th>
                    <th class="text-end">Disbursal Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrollList as $item)
                    @php
                        $emp = $item->employee;
                    @endphp
                    <tr class="calculation-row" data-employee-id="{{ $emp->id }}" id="row-{{ $emp->id }}">
                        <!-- Employee Info -->
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $emp->user->avatar_url }}" alt="" class="avatar-circle" style="width: 32px; height: 32px;">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $emp->name }}</div>
                                    <div class="text-muted fs-8 font-monospace">{{ $emp->employee_code }}</div>
                                    <span class="badge bg-light text-secondary border font-monospace mt-1" style="font-size: 10px;">
                                        {{ $emp->department->name ?? 'No Dept' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Salary Info -->
                        <td>
                            @if($emp->salary_type === 'hourly')
                                <div class="fw-bold text-success">₹{{ number_format($emp->hourly_rate, 2) }} <span class="fs-8 fw-normal text-muted">/ hr</span></div>
                                <small class="text-muted d-block">Hourly</small>
                            @else
                                <div class="fw-bold text-dark">₹{{ number_format($emp->salary, 2) }} <span class="fs-8 fw-normal text-muted">/ mo</span></div>
                                <small class="text-muted d-block">Monthly</small>
                            @endif
                        </td>
                        
                        <!-- Attendance Stats -->
                        <td>
                            @if($emp->salary_type === 'hourly')
                                <div class="fs-7 text-dark"><i class="bi bi-clock me-1"></i><strong>{{ $item->worked_hours }}</strong> hrs</div>
                            @else
                                <div class="fs-8 text-dark mb-1">
                                    Present: <strong>{{ $item->days_present }}</strong> d
                                    @if($item->half_days > 0)
                                        | Half: <strong>{{ $item->half_days }}</strong> d
                                    @endif
                                    @if($item->leaves_count > 0)
                                        | Leaves: <strong>{{ $item->leaves_count }}</strong> d
                                    @endif
                                </div>
                                <div class="fs-8 text-muted mb-1">
                                    Offs: <strong>{{ $item->weekly_offs }}</strong> d
                                    @if($item->holidays > 0)
                                        | Holidays: <strong>{{ $item->holidays }}</strong> d
                                    @endif
                                </div>
                                <div class="badge bg-light text-primary border font-monospace" style="font-size: 10px;">
                                    Paid Days: <strong>{{ $item->paid_days }}</strong> d
                                </div>
                            @endif
                        </td>
                        
                        <!-- Gross Salary -->
                        <td>
                            <span class="fw-semibold text-dark gross-salary-display">₹{{ number_format($item->calculated_gross, 2) }}</span>
                            @if($emp->salary_type !== 'hourly' && $item->daily_rate > 0)
                                <small class="text-muted d-block" style="font-size: 10px; line-height: 1.2; margin-top: 2px;">
                                    Rate: ₹{{ number_format($item->daily_rate, 2) }}/d<br>
                                    ({{ $item->paid_days }} d × ₹{{ number_format($item->daily_rate, 2) }})
                                </small>
                            @endif
                            <input type="hidden" class="basic-salary-input" value="{{ $item->basic_salary }}">
                            <input type="hidden" class="gross-salary-input" value="{{ $item->calculated_gross }}">
                        </td>
                        
                        <!-- Allowance input -->
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm allowance-input" value="0.00" {{ $item->is_paid ? 'disabled' : '' }}>
                        </td>
                        
                        <!-- Deduction input -->
                        <td>
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm deduction-input" value="0.00" {{ $item->is_paid ? 'disabled' : '' }}>
                        </td>
                        
                        <!-- Net Salary display -->
                        <td>
                            <span class="net-salary-display">₹{{ number_format($item->calculated_gross, 2) }}</span>
                        </td>
                        
                        <!-- Payment Method -->
                        <td>
                            <select class="form-select form-select-sm payment-method-input" {{ $item->is_paid ? 'disabled' : '' }}>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </td>
                        
                        <!-- Remarks -->
                        <td>
                            <input type="text" class="form-control form-control-sm remarks-input" placeholder="Remarks..." {{ $item->is_paid ? 'disabled' : '' }}>
                        </td>
                        
                        <!-- Disburse Action -->
                        <td class="text-end action-cell">
                            @if($item->is_paid)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i>Paid</span>
                                <a href="{{ route('admin.payroll.show', $item->disbursal) }}" target="_blank" class="btn btn-link btn-sm p-0 d-block text-center mt-1">View Slip</a>
                            @else
                                <button type="button" class="btn btn-primary btn-sm px-3 btn-disburse" onclick="disburseSalary(this, {{ $emp->id }})" style="border-radius: 8px;">
                                    Disburse
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-people" style="font-size: 36px;"></i>
                            <div class="mt-2">No active employees found to disburse salary.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Live update Net Salary calculations
    document.addEventListener('DOMContentLoaded', () => {
        const rows = document.querySelectorAll('.calculation-row');
        
        rows.forEach(row => {
            const allowance = row.querySelector('.allowance-input');
            const deduction = row.querySelector('.deduction-input');
            
            if (allowance && deduction) {
                allowance.addEventListener('input', () => recalculateRow(row));
                deduction.addEventListener('input', () => recalculateRow(row));
            }
        });
    });

    function recalculateRow(row) {
        const grossVal = parseFloat(row.querySelector('.gross-salary-input').value) || 0;
        const allowanceVal = parseFloat(row.querySelector('.allowance-input').value) || 0;
        const deductionVal = parseFloat(row.querySelector('.deduction-input').value) || 0;
        
        const net = Math.max(0, grossVal + allowanceVal - deductionVal);
        row.querySelector('.net-salary-display').innerText = '₹' + net.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function disburseSalary(button, employeeId) {
        const row = document.getElementById(`row-${employeeId}`);
        const grossVal = parseFloat(row.querySelector('.gross-salary-input').value) || 0;
        const basicSalaryVal = parseFloat(row.querySelector('.basic-salary-input').value) || 0;
        const allowanceVal = parseFloat(row.querySelector('.allowance-input').value) || 0;
        const deductionVal = parseFloat(row.querySelector('.deduction-input').value) || 0;
        const netVal = Math.max(0, grossVal + allowanceVal - deductionVal);
        const paymentMethodVal = row.querySelector('.payment-method-input').value;
        const remarksVal = row.querySelector('.remarks-input').value;

        button.disabled = true;
        button.innerText = 'Processing...';

        fetch("{{ route('admin.payroll.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                employee_id: employeeId,
                month: {{ $month }},
                year: {{ $year }},
                basic_salary: basicSalaryVal,
                allowances: allowanceVal,
                deductions: deductionVal,
                net_salary: netVal,
                payment_method: paymentMethodVal,
                remarks: remarksVal
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Disable inputs
                row.querySelector('.allowance-input').disabled = true;
                row.querySelector('.deduction-input').disabled = true;
                row.querySelector('.payment-method-input').disabled = true;
                row.querySelector('.remarks-input').disabled = true;

                // Replace Action button with Paid badge
                const actionCell = row.querySelector('.action-cell');
                actionCell.innerHTML = `
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i>Paid</span>
                    <a href="/admin/payroll/${data.disbursal.id}/payslip" target="_blank" class="btn btn-link btn-sm p-0 d-block text-center mt-1">View Slip</a>
                `;
                
                // Show Alert Success toast
                alert('Salary disbursed successfully!');
            } else {
                alert(data.message || 'An error occurred during disbursal.');
                button.disabled = false;
                button.innerText = 'Disburse';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to disburse salary. Please try again.');
            button.disabled = false;
            button.innerText = 'Disburse';
        });
    }
</script>
@endpush
