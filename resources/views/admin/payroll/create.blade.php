@extends('layouts.app')

@section('title', 'Disburse Salary')
@section('page-title', 'Disburse Salary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll</a></li>
    <li class="breadcrumb-item active">Disburse</li>
@endsection

@push('styles')
<style>
    .payroll-table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6b7280;
        font-weight: 600;
        white-space: nowrap;
    }
    .payroll-table td { vertical-align: middle; }

    /* Disbursal Modal */
    #disburseModal .modal-header {
        background: #1e293b;
        color: #fff;
        border-radius: 12px 12px 0 0;
        padding: 18px 24px;
    }
    #disburseModal .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    }
    #disburseModal .modal-body { padding: 24px; }
    #disburseModal .form-row-label {
        width: 260px;
        min-width: 260px;
        text-align: left;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }
    #disburseModal .form-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }
    #disburseModal .form-row .form-control,
    #disburseModal .form-row .form-select {
        border-radius: 8px;
        border: 1.5px solid #e5e7eb;
        background: #f9fafb;
        font-size: 14px;
        padding: 9px 14px;
        flex: 1;
        min-width: 0;
    }
    #disburseModal .form-row .form-control:focus,
    #disburseModal .form-row .form-select:focus {
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
    }
    #disburseModal .allowance-line {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    #disburseModal .allowance-line input { flex: 1; }
    #disburseModal .btn-add-allowance {
        background: #4f46e5;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: background .2s;
    }
    #disburseModal .btn-add-allowance:hover { background: #4338ca; }
    #disburseModal .btn-add-deduction {
        background: #dc2626;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: background .2s;
    }
    #disburseModal .btn-add-deduction:hover { background: #b91c1c; }
    #disburseModal .deduction-line {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    #disburseModal .btn-make-payment {
        background: #4f46e5;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 28px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background .2s;
    }
    #disburseModal .btn-make-payment:hover { background: #4338ca; }
    #disburseModal .btn-make-payment:disabled { background: #9ca3af; cursor: not-allowed; }
    .modal-title-name {
        font-size: 16px;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .modal-title-name span {
        color: #a5b4fc;
        font-weight: 400;
        font-size: 13px;
        margin-left: 4px;
    }
</style>
@endpush

@section('content')
{{-- Month/Year Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.payroll.create') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-{{ ($company->salary_cycle ?? 'monthly') === 'twice_monthly' ? '3' : '4' }}">
                <label class="form-label fs-8 text-muted mb-1 text-uppercase fw-bold">Salary Month</label>
                <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-{{ ($company->salary_cycle ?? 'monthly') === 'twice_monthly' ? '3' : '4' }}">
                <label class="form-label fs-8 text-muted mb-1 text-uppercase fw-bold">Salary Year</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    @for($y = now()->year - 2; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            @if(($company->salary_cycle ?? 'monthly') === 'twice_monthly')
            <div class="col-12 col-md-3">
                <label class="form-label fs-8 text-muted mb-1 text-uppercase fw-bold">Disbursal Period</label>
                <select name="cycle" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="1" {{ ($cycle ?? 1) == 1 ? 'selected' : '' }}>1st Cycle (1st - 15th)</option>
                    <option value="2" {{ ($cycle ?? 1) == 2 ? 'selected' : '' }}>2nd Cycle (16th - End)</option>
                </select>
            </div>
            @endif
            <div class="col-12 col-md-{{ ($company->salary_cycle ?? 'monthly') === 'twice_monthly' ? '3' : '4' }} text-md-end">
                <a href="{{ route('admin.payroll.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Payroll
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Payroll Sheet --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Payroll Sheet: {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }} @if(($company->salary_cycle ?? 'monthly') === 'twice_monthly') (Cycle {{ $cycle }}) @endif</h5>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover payroll-table">
            <thead>
                <tr>
                    <th>Employee Details</th>
                    <th>Salary Structure</th>
                    <th>Attendance Parameters</th>
                    <th>Gross Salary</th>
                    <th>Net Salary</th>
                    <th class="text-end">Disbursal Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrollList as $item)
                    @php $emp = $item->employee; @endphp
                    <tr id="row-{{ $emp->id }}">
                        {{-- Employee Info --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $emp->user->avatar_url }}" alt="" class="avatar-circle" style="width:32px;height:32px;">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $emp->name }}</div>
                                    <div class="text-muted fs-8 font-monospace">{{ $emp->employee_code }}</div>
                                    <span class="badge bg-light text-secondary border font-monospace mt-1" style="font-size:10px;">
                                        {{ $emp->department->name ?? 'No Dept' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Salary Info --}}
                        <td>
                            @if($emp->salary_type === 'hourly')
                                <div class="fw-bold text-success">₹{{ number_format($emp->hourly_rate, 2) }} <span class="fs-8 fw-normal text-muted">/ hr</span></div>
                                <small class="text-muted d-block">Hourly</small>
                            @else
                                <div class="fw-bold text-dark">₹{{ number_format($emp->salary, 2) }} <span class="fs-8 fw-normal text-muted">/ mo</span></div>
                                <small class="text-muted d-block">Monthly</small>
                            @endif
                        </td>

                        {{-- Attendance Stats --}}
                        <td>
                            @if($emp->salary_type === 'hourly')
                                <div class="fs-7 text-dark d-flex align-items-center gap-2">
                                    <span><i class="bi bi-clock me-1"></i><strong>{{ $item->worked_hours }}</strong> hrs</span>
                                    <button type="button" class="btn btn-outline-secondary p-0 d-flex align-items-center justify-content-center btn-report-trigger" 
                                            style="width: 16px; height: 16px; border-radius: 50%; font-size: 10px;"
                                            data-emp-id="{{ $emp->id }}"
                                            title="View Attendance Report">
                                        <i class="bi bi-info-lg"></i>
                                    </button>
                                </div>
                            @else
                                <div class="fs-8 text-dark mb-1 d-flex align-items-center gap-2">
                                    <span>
                                        Present: <strong>{{ $item->days_present }}</strong> d
                                        @if($item->half_days > 0) | Half: <strong>{{ $item->half_days }}</strong> d @endif
                                        @if($item->leaves_count > 0) | Leaves: <strong>{{ $item->leaves_count }}</strong> d @endif
                                    </span>
                                    <button type="button" class="btn btn-outline-secondary p-0 d-flex align-items-center justify-content-center btn-report-trigger" 
                                            style="width: 16px; height: 16px; border-radius: 50%; font-size: 10px;"
                                            data-emp-id="{{ $emp->id }}"
                                            title="View Attendance Report">
                                        <i class="bi bi-info-lg"></i>
                                    </button>
                                </div>
                                <div class="fs-8 text-muted mb-1">
                                    Offs: <strong>{{ $item->weekly_offs }}</strong> d
                                    @if($item->holidays > 0) | Holidays: <strong>{{ $item->holidays }}</strong> d @endif
                                </div>
                                <div class="badge bg-light text-primary border font-monospace" style="font-size:10px;">
                                    Paid Days: <strong>{{ $item->paid_days }}</strong> d
                                </div>
                            @endif
                        </td>

                        {{-- Gross Salary --}}
                        <td>
                            <span class="fw-semibold text-dark">₹{{ number_format($item->calculated_gross, 2) }}</span>
                            @if($emp->salary_type !== 'hourly' && $item->daily_rate > 0)
                                <small class="text-muted d-block" style="font-size:10px;line-height:1.2;margin-top:2px;">
                                    LOP Rate: ₹{{ number_format($item->daily_rate, 2) }}/d<br>
                                    @if($item->lop_count > 0)
                                        Deduction: -₹{{ number_format($item->lop_deduction, 2) }} ({{ $item->lop_count }} d LOP)
                                    @elseif($item->cl_count > 0)
                                        Exempted CL: {{ $item->cl_count }} d
                                    @else
                                        No deductions
                                    @endif
                                </small>
                            @endif
                        </td>

                        {{-- Net Salary --}}
                        <td>
                            <span class="fw-bold text-primary" id="net-display-{{ $emp->id }}">₹{{ number_format($item->calculated_gross, 2) }}</span>
                        </td>

                        {{-- Disbursal Action --}}
                        <td class="text-end" id="action-cell-{{ $emp->id }}">
                            @if($item->is_paid)
                                <div class="d-flex flex-column align-items-end gap-1">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fw-semibold fs-8 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i>Paid
                                    </span>
                                    <a href="{{ route('admin.payroll.show', $item->disbursal) }}" target="_blank"
                                       class="btn btn-link btn-sm p-0 text-primary text-decoration-none fw-medium d-inline-flex align-items-center gap-1"
                                       style="font-size: 11px;">
                                        <i class="bi bi-receipt"></i> View Slip
                                    </a>
                                </div>
                            @else
                                <button type="button" class="btn btn-primary btn-sm px-3 btn-disburse-trigger"
                                    style="border-radius:8px;"
                                    data-id="{{ $emp->id }}"
                                    data-name="{{ $emp->name }}"
                                    data-gross="{{ $item->calculated_gross }}"
                                    data-basic="{{ $item->basic_salary }}"
                                    data-lop-deduction="{{ $item->lop_deduction }}"
                                    data-daily-rate="{{ $item->daily_rate }}"
                                    data-working-days="{{ $item->total_working_days }}"
                                    data-leaves-count="{{ $item->leaves_count }}"
                                    data-cl-count="{{ $item->cl_count }}"
                                    data-lop-count="{{ $item->lop_count }}"
                                    data-leaves-details="{{ json_encode($item->leaves_details) }}"
                                    data-days-present="{{ $item->days_present }}"
                                    data-half-days="{{ $item->half_days }}"
                                    data-salary-type="{{ $emp->salary_type }}">
                                    Disburse
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people" style="font-size:36px;"></i>
                            <div class="mt-2">No active employees found to disburse salary.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     Disburse Salary Modal
══════════════════════════════════════════════ --}}
<div class="modal fade" id="disburseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:540px;">
        <div class="modal-content">
            {{-- Header --}}
            <div class="modal-header">
                <div>
                    <div class="modal-title-name">
                        Salary <span id="dm-emp-name">[Employee]</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body">
                {{-- Upcoming Salary Term --}}
                <div class="form-row py-1">
                    <label class="form-row-label">Upcoming Salary Term</label>
                    <span id="dm-salary-term" class="fw-semibold text-dark fs-7"></span>
                </div>

                {{-- Basic Salary --}}
                <div class="form-row py-1">
                    <label class="form-row-label">Basic Salary</label>
                    <span class="fw-bold text-dark fs-6">₹<span id="dm-basic-salary">0.00</span></span>
                </div>

                {{-- Casual Leave --}}
                <div class="form-row py-1" id="dm-cl-row" style="display: none;">
                    <label class="form-row-label">Casual Leave (CL)</label>
                    <div>
                        <span class="fw-bold text-success fs-6">₹0.00</span>
                        <div class="text-muted fs-8" id="dm-cl-calc-text" style="margin-top:-2px;">(0 days - No Deduction)</div>
                    </div>
                </div>

                {{-- Leave Deduction / LOP --}}
                <div class="form-row py-1" id="dm-lop-row" style="display: none;">
                    <label class="form-row-label">Leave Deduction / LOP</label>
                    <div>
                        <span class="fw-bold text-danger fs-6">- ₹<span id="dm-lop-deduction">0.00</span></span>
                        <div class="text-muted fs-8" id="dm-lop-calc-text" style="margin-top:-2px;">(0 days × ₹0.00)</div>
                    </div>
                </div>

                {{-- Attendance Summary Table --}}
                <div class="mb-4">
                    <div class="fw-semibold text-secondary text-uppercase mb-2" style="font-size:11px;letter-spacing:.05em;">Attendance Summary</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered text-center mb-0 align-middle" style="font-size:13px;background:#f9fafb;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2 fw-semibold text-secondary" style="font-size:11px;font-weight:600;">Total Working Days</th>
                                    <th class="py-2 fw-semibold text-secondary" style="font-size:11px;font-weight:600;">No of Leaves</th>
                                    <th class="py-2 fw-semibold text-secondary" style="font-size:11px;font-weight:600;">Total Present Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="py-2 fw-bold text-dark" id="dm-summary-working-days">0</td>
                                    <td class="py-2">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <span class="fw-bold text-dark" id="dm-summary-leaves">0</span>
                                            <button type="button" class="btn btn-outline-primary rounded-circle p-0 d-flex align-items-center justify-content-center" 
                                                    id="dm-leaves-info-btn" 
                                                    style="width:18px;height:18px;border-radius:50%;" 
                                                    onclick="toggleLeavesDetails()"
                                                    title="View detailed leaves">
                                                <i class="bi bi-info" style="font-size:11px;font-weight:bold;"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-2 fw-bold text-success" id="dm-summary-present">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" id="dm-working-days" value="0">
                    <input type="hidden" id="dm-leave-days" value="0">
                </div>

                {{-- Leaves detail toggle box --}}
                <div id="dm-leaves-details-box" class="p-3 mb-3 bg-light border rounded-3" style="display:none;font-size:13px;">
                    <div class="fw-semibold text-secondary text-uppercase mb-2" style="font-size:10px;letter-spacing:.05em;">Approved Leaves Details</div>
                    <div id="dm-leaves-details-list" class="d-flex flex-column gap-2"></div>
                </div>

                {{-- Payable Amount --}}
                <div class="form-row py-1">
                    <label class="form-row-label">Payable Amount</label>
                    <span class="fw-bold text-dark fs-6">₹<span id="dm-payable">0.00</span></span>
                </div>

                {{-- Allowances section --}}
                <div id="dm-allowances-list"></div>

                {{-- Deductions section --}}
                <div id="dm-deductions-list" class="mb-3"></div>

                <div class="d-flex justify-content-end gap-2 mb-4">
                    <button type="button" class="btn-add-deduction" onclick="dmAddDeduction()">
                        <i class="bi bi-dash-circle me-1"></i> Add Deduction
                    </button>
                    <button type="button" class="btn-add-allowance" onclick="dmAddAllowance()">
                        <i class="bi bi-plus-circle me-1"></i> Add Allowance
                    </button>
                </div>

                {{-- Total Amount --}}
                <div class="form-row py-1">
                    <label class="form-row-label">Total Amount</label>
                    <span class="fw-bold text-primary fs-5">₹<span id="dm-total">0.00</span></span>
                </div>



                {{-- Payment Method --}}
                <div class="form-row">
                    <label class="form-row-label">Payment Method</label>
                    <select class="form-select" id="dm-payment-method">
                        <option value="">Select</option>
                        <option value="Cash">Cash</option>
                        <optgroup label="Bank Accounts">
                            @foreach($banks as $bank)
                                <option value="{{ $bank->name }}">{{ $bank->name }} - {{ $bank->branch }} - ****{{ substr($bank->account_number, -4) }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Investors">
                            @foreach($investors as $investor)
                                <option value="Investor: {{ $investor->name }}">Investor: {{ $investor->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                {{-- Bank Reference No / Voucher No --}}
                <div class="form-row">
                    <label class="form-row-label">Bank Reference No / Voucher No</label>
                    <input type="text" class="form-control" id="dm-bank-ref" placeholder="">
                </div>

                {{-- Remarks --}}
                <div class="form-row">
                    <label class="form-row-label">Remarks</label>
                    <input type="text" class="form-control" id="dm-remarks" placeholder="Optional remarks…">
                </div>

                {{-- Footer --}}
                <div class="d-flex justify-content-end mt-2">
                    <button type="button" class="btn-make-payment" id="dm-submit-btn" onclick="dmSubmit()">
                        Make Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Attendance Report Modal --}}
<div class="modal fade" id="attendanceReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header bg-dark text-white" style="border-top-left-radius:16px; border-top-right-radius:16px; padding:18px 24px;">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="arm-title">Attendance Detail Report</h5>
                    <p class="mb-0 text-muted fs-7" id="arm-subtitle"></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                <div class="text-center py-5" id="arm-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Generating attendance report...</p>
                </div>
                <div id="arm-content" style="display:none;">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover text-center border mb-0" style="font-size:13px; border-radius:12px; overflow:hidden;">
                            <thead class="table-dark">
                                <tr>
                                    <th class="py-2 text-start px-3">Date</th>
                                    <th class="py-2">Day</th>
                                    <th class="py-2">Status</th>
                                    <th class="py-2">Worked Hours</th>
                                    <th class="py-2 text-start px-3">Details / Session Info</th>
                                </tr>
                            </thead>
                            <tbody id="arm-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2" style="border-bottom-left-radius:16px; border-bottom-right-radius:16px;">
                <button type="button" class="btn btn-secondary btn-sm px-4" style="border-radius:8px;" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ──────────────────────────────────────────────
// State kept when modal opens
// ──────────────────────────────────────────────
let _dmEmployeeId   = null;
let _dmGross        = 0;
let _dmBasic        = 0;
let _dmLopDeduction = 0;
let _dmDailyRate    = 0;
let _dmWorkingDays  = 0;
let _dmLeavesCount  = 0;
let _dmSalaryType   = 'monthly';
let _dmAllowanceIdx = 0;
let _dmDeductionIdx = 0;

document.addEventListener('DOMContentLoaded', () => {
    // Dynamic binding for disburse buttons
    document.querySelectorAll('.btn-disburse-trigger').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const gross = parseFloat(btn.dataset.gross) || 0;
            const basic = parseFloat(btn.dataset.basic) || 0;
            const lopDeduction = parseFloat(btn.dataset.lopDeduction) || 0;
            const dailyRate = parseFloat(btn.dataset.dailyRate) || 0;
            const workingDays = parseInt(btn.dataset.workingDays) || 0;
            const leavesCount = parseFloat(btn.dataset.leavesCount) || 0;
            const clCount = parseFloat(btn.dataset.clCount) || 0;
            const lopCount = parseFloat(btn.dataset.lopCount) || 0;
            const daysPresent = parseFloat(btn.dataset.daysPresent) || 0;
            const halfDays = parseFloat(btn.dataset.halfDays) || 0;
            const salaryType = btn.dataset.salaryType;
            let leavesDetails = [];
            try {
                leavesDetails = JSON.parse(btn.dataset.leavesDetails);
            } catch (e) {
                console.error('Failed to parse leaves details', e);
            }

            openDisburseModal(id, name, gross, basic, lopDeduction, dailyRate, workingDays, leavesCount, salaryType, leavesDetails, daysPresent, halfDays, clCount, lopCount);
        });
    });
});

function openDisburseModal(empId, empName, gross, basic, lopDeduction, dailyRate, workingDays, leavesCount, salaryType, leavesDetails, daysPresent, halfDays, clCount, lopCount) {
    _dmEmployeeId   = empId;
    _dmGross        = gross;
    _dmBasic        = basic;
    _dmLopDeduction = lopDeduction;
    _dmDailyRate    = dailyRate;
    _dmWorkingDays  = workingDays;
    _dmLeavesCount  = leavesCount;
    _dmSalaryType   = salaryType;
    _dmAllowanceIdx = 0;
    _dmDeductionIdx = 0;

    // Fill header
    document.getElementById('dm-emp-name').textContent = '[' + empName.toUpperCase() + ']';

    // Salary term
    const monthName = '{{ date("F", mktime(0,0,0,$month,1)) }}';
    const year      = {{ $year }};
    @if(($company->salary_cycle ?? 'monthly') === 'twice_monthly')
        const range = '{{ $cycle == 1 ? "1 to 15" : "16 to " . \Carbon\Carbon::create($year,$month,1)->daysInMonth }}';
    @else
        const range = '1 to {{ \Carbon\Carbon::create($year,$month,1)->daysInMonth }}';
    @endif
    document.getElementById('dm-salary-term').textContent = year + ' - ' + monthName + ' (' + range + ')';

    // Basic
    document.getElementById('dm-basic-salary').textContent = basic.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Show/hide and populate CL and LOP
    const clRow = document.getElementById('dm-cl-row');
    const lopRow = document.getElementById('dm-lop-row');
    if (salaryType === 'monthly') {
        if (clCount > 0) {
            clRow.style.display = 'flex';
            document.getElementById('dm-cl-calc-text').innerHTML = `(${clCount} ${clCount == 1 ? 'day' : 'days'} - No Deduction)`;
        } else {
            clRow.style.display = 'none';
        }

        if (lopCount > 0) {
            lopRow.style.display = 'flex';
            document.getElementById('dm-lop-deduction').textContent = lopDeduction.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('dm-lop-calc-text').innerHTML = `(${lopCount} ${lopCount == 1 ? 'day' : 'days'} × ₹${dailyRate.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`;
        } else {
            lopRow.style.display = 'none';
        }
    } else {
        clRow.style.display = 'none';
        lopRow.style.display = 'none';
    }

    // Populate Attendance Summary Table
    document.getElementById('dm-summary-working-days').textContent = workingDays;
    document.getElementById('dm-summary-leaves').textContent = leavesCount;
    document.getElementById('dm-summary-present').textContent = (daysPresent + halfDays * 0.5);

    // Hidden inputs
    document.getElementById('dm-working-days').value = workingDays;
    document.getElementById('dm-leave-days').value = leavesCount;

    // Build leaves details list HTML
    let detailsHtml = '';
    if (leavesDetails && leavesDetails.length > 0) {
        leavesDetails.forEach(l => {
            detailsHtml += `
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                     <div>
                        <span class="fw-semibold text-dark">${l.type}</span>
                        <small class="text-muted d-block">${l.from} to ${l.to}</small>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary border">${l.days} ${l.days > 1 ? 'days' : 'day'}</span>
                </div>
            `;
        });
    } else {
        detailsHtml = '<div class="text-muted text-center py-2">No leaves taken this month.</div>';
    }
    document.getElementById('dm-leaves-details-list').innerHTML = detailsHtml;

    // Hide details box initially
    document.getElementById('dm-leaves-details-box').style.display = 'none';

    // Clear allowances and deductions
    document.getElementById('dm-allowances-list').innerHTML = '';
    document.getElementById('dm-deductions-list').innerHTML = '';

    // Payment method reset
    document.getElementById('dm-payment-method').value = '';
    document.getElementById('dm-bank-ref').value = '';
    document.getElementById('dm-remarks').value = '';

    document.getElementById('dm-submit-btn').disabled = false;
    document.getElementById('dm-submit-btn').textContent = 'Make Payment';

    dmRecalculate();

    const modal = new bootstrap.Modal(document.getElementById('disburseModal'));
    modal.show();
}

function toggleLeavesDetails() {
    const box = document.getElementById('dm-leaves-details-box');
    if (box.style.display === 'none') {
        box.style.display = 'block';
    } else {
        box.style.display = 'none';
    }
}

function dmRecalculate() {
    const payable = _dmGross;
    document.getElementById('dm-payable').textContent = payable.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Sum allowances
    let allowanceSum = 0;
    document.querySelectorAll('.dm-allowance-amount').forEach(inp => {
        allowanceSum += parseFloat(inp.value) || 0;
    });

    // Sum manual deductions
    let manualDeductionSum = 0;
    document.querySelectorAll('.dm-deduction-amount').forEach(inp => {
        manualDeductionSum += parseFloat(inp.value) || 0;
    });

    const total = payable + allowanceSum - manualDeductionSum;
    document.getElementById('dm-total').textContent = total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function dmAddAllowance() {
    _dmAllowanceIdx++;
    const idx  = _dmAllowanceIdx;
    const list = document.getElementById('dm-allowances-list');
    const div  = document.createElement('div');
    div.className = 'allowance-line';
    div.id = 'dm-allowance-' + idx;
    div.innerHTML = `
        <input type="text" class="form-control dm-allowance-label" placeholder="Allowance name" style="max-width:180px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;padding:9px 14px;font-size:14px;">
        <input type="number" class="form-control dm-allowance-amount" placeholder="0.00" min="0" step="0.01"
               style="border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;padding:9px 14px;font-size:14px;"
               oninput="dmRecalculate()">
        <button type="button" class="btn btn-sm btn-outline-danger" style="border-radius:8px;"
                onclick="dmRemoveAllowance(${idx})"><i class="bi bi-x"></i></button>
    `;
    list.appendChild(div);
    dmRecalculate();
}

function dmRemoveAllowance(idx) {
    const el = document.getElementById('dm-allowance-' + idx);
    if (el) { el.remove(); dmRecalculate(); }
}

function dmAddDeduction() {
    _dmDeductionIdx++;
    const idx  = _dmDeductionIdx;
    const list = document.getElementById('dm-deductions-list');
    const div  = document.createElement('div');
    div.className = 'deduction-line';
    div.id = 'dm-deduction-' + idx;
    div.innerHTML = `
        <input type="text" class="form-control dm-deduction-label" placeholder="Deduction name" style="max-width:180px;border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;padding:9px 14px;font-size:14px;">
        <input type="number" class="form-control dm-deduction-amount" placeholder="0.00" min="0" step="0.01"
               style="border-radius:8px;border:1.5px solid #e5e7eb;background:#f9fafb;padding:9px 14px;font-size:14px;"
               oninput="dmRecalculate()">
        <button type="button" class="btn btn-sm btn-outline-danger" style="border-radius:8px;"
                onclick="dmRemoveDeduction(${idx})"><i class="bi bi-x"></i></button>
    `;
    list.appendChild(div);
    dmRecalculate();
}

function dmRemoveDeduction(idx) {
    const el = document.getElementById('dm-deduction-' + idx);
    if (el) { el.remove(); dmRecalculate(); }
}

function dmSubmit() {
    const paymentMethod = document.getElementById('dm-payment-method').value;
    if (!paymentMethod) {
        alert('Please select a payment method.');
        return;
    }

    const payable      = _dmGross;
    const leaveDays    = parseFloat(document.getElementById('dm-leave-days').value) || 0;
    const bankRef      = document.getElementById('dm-bank-ref').value;
    const remarks      = document.getElementById('dm-remarks').value;

    // Build allowance description
    let allowanceTotal = 0;
    let allowanceNote  = '';
    document.querySelectorAll('.allowance-line').forEach(line => {
        const label  = line.querySelector('.dm-allowance-label').value || 'Allowance';
        const amount = parseFloat(line.querySelector('.dm-allowance-amount').value) || 0;
        allowanceTotal += amount;
        if (amount > 0) allowanceNote += label + ': ₹' + amount + '; ';
    });

    // Build deduction description
    let deductionTotal = 0;
    let deductionNote  = '';
    document.querySelectorAll('.deduction-line').forEach(line => {
        const label  = line.querySelector('.dm-deduction-label').value || 'Deduction';
        const amount = parseFloat(line.querySelector('.dm-deduction-amount').value) || 0;
        deductionTotal += amount;
        if (amount > 0) deductionNote += label + ': ₹' + amount + '; ';
    });

    const paying       = payable + allowanceTotal - deductionTotal;
    const deductions   = _dmLopDeduction + deductionTotal;

    const btn = document.getElementById('dm-submit-btn');
    btn.disabled = true;
    btn.textContent = 'Processing…';

    const cell = document.getElementById('action-cell-' + _dmEmployeeId);

    fetch("{{ route('admin.payroll.store') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            employee_id:    _dmEmployeeId,
            month:          {{ $month }},
            year:           {{ $year }},
            cycle:          {{ $cycle ?? 1 }},
            basic_salary:   _dmBasic,
            allowances:     allowanceTotal,
            deductions:     deductions,
            net_salary:     paying,
            payment_method: paymentMethod,
            remarks:        (bankRef ? 'Ref: ' + bankRef + '. ' : '') + remarks + (allowanceNote ? ' | Allowances: ' + allowanceNote : '') + (deductionNote ? ' | Deductions: ' + deductionNote : ''),
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('disburseModal')).hide();

            // Update table row action cell
            cell.innerHTML = `
                <div class="d-flex flex-column align-items-end gap-1">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fw-semibold fs-8 rounded-pill">
                        <i class="bi bi-check-circle-fill me-1"></i>Paid
                    </span>
                    <a href="/admin/payroll/${data.disbursal.encrypted_id}/payslip" target="_blank"
                       class="btn btn-link btn-sm p-0 text-primary text-decoration-none fw-medium d-inline-flex align-items-center gap-1"
                       style="font-size: 11px;">
                        <i class="bi bi-receipt"></i> View Slip
                    </a>
                </div>
            `;

            // Show net on row
            document.getElementById('net-display-' + _dmEmployeeId).textContent =
                '₹' + paying.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        } else {
            alert(data.message || 'An error occurred during disbursal.');
            btn.disabled    = false;
            btn.textContent = 'Make Payment';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Failed to disburse salary. Please try again.');
        btn.disabled    = false;
        btn.textContent = 'Make Payment';
    });
}

// ──────────────────────────────────────────────
// Attendance Report Modal Fetch & Render
// ──────────────────────────────────────────────
document.querySelectorAll('.btn-report-trigger').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const empId = this.getAttribute('data-emp-id');
        
        // Show loading spinner
        document.getElementById('arm-loading').style.display = 'block';
        document.getElementById('arm-content').style.display = 'none';
        
        const reportModal = new bootstrap.Modal(document.getElementById('attendanceReportModal'));
        reportModal.show();
        
        const url = `/admin/payroll/employee/${empId}/attendance-report?month={{ $month }}&year={{ $year }}&cycle={{ $cycle ?? 1 }}`;
        fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('arm-title').textContent = data.employee.name + ' (' + data.employee.code + ')';
                document.getElementById('arm-subtitle').innerHTML = `<i class="bi bi-calendar3 me-1"></i> Period: <strong>${data.term}</strong>`;
                
                let rowsHtml = '';
                data.report.forEach(row => {
                    rowsHtml += `
                        <tr>
                            <td class="text-start px-3 fw-semibold text-dark">${row.date}</td>
                            <td class="text-muted">${row.day}</td>
                            <td>
                                <span class="badge ${row.badge_class} border px-2 py-1" style="font-size:11px;">
                                    ${row.status}
                                </span>
                            </td>
                            <td class="font-monospace fw-bold text-dark">${row.worked_hours || '00:00'}</td>
                            <td class="text-start px-3 text-muted" style="max-width:200px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">
                                ${row.details || '-'}
                            </td>
                        </tr>
                    `;
                });
                
                document.getElementById('arm-table-body').innerHTML = rowsHtml;
                document.getElementById('arm-loading').style.display = 'none';
                document.getElementById('arm-content').style.display = 'block';
            } else {
                alert(data.message || 'Failed to load report data.');
                reportModal.hide();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to generate report. Please try again.');
            reportModal.hide();
        });
    });
});
</script>
@endpush
