@extends('layouts.app')

@section('title', 'Payroll Management')
@section('page-title', 'Payroll Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Payroll</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <!-- Stat Cards -->
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm bg-primary bg-gradient text-white">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fs-7 text-white-50 text-uppercase fw-bold">Disbursed This Month</span>
                        <h2 class="display-6 fw-extrabold mt-1">₹{{ number_format($totalPaidThisMonth, 2) }}</h2>
                    </div>
                    <div class="fs-1 text-white-50"><i class="bi bi-wallet2"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fs-7 text-muted text-uppercase fw-bold">Slips Disbursed</span>
                        <h2 class="display-6 fw-extrabold mt-1 text-dark">{{ $slipsCount }} <span class="fs-5 fw-normal text-muted">/ {{ $activeEmployeesCount }} employees</span></h2>
                    </div>
                    <div class="fs-1 text-primary"><i class="bi bi-file-earmark-check"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4 d-flex align-items-center justify-content-md-end">
        <a href="{{ route('admin.payroll.create') }}" class="btn btn-primary btn-lg w-100 py-3 shadow" style="border-radius: 12px; font-weight: 700;">
            <i class="bi bi-plus-circle me-2"></i> Disburse New Salary
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold">Salary Disbursal Logs</h5>
        <form method="GET" action="{{ route('admin.payroll.index') }}" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search employee..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary btn-sm px-3">Search</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Salary Month</th>
                    <th>Basic Salary</th>
                    <th>Allowances</th>
                    <th>Deductions</th>
                    <th class="fw-bold">Net Salary</th>
                    <th>Payment Method</th>
                    <th>Disbursed Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $slip)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $slip->employee->user->avatar_url }}" alt="" class="avatar-circle" style="width: 32px; height: 32px;">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $slip->employee->name }}</div>
                                    <div class="text-muted fs-8 font-monospace">{{ $slip->employee->employee_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium text-dark">
                                {{ date('F Y', mktime(0, 0, 0, $slip->month, 1, $slip->year)) }}
                            </span>
                        </td>
                        <td>₹{{ number_format($slip->basic_salary, 2) }}</td>
                        <td class="text-success">+₹{{ number_format($slip->allowances, 2) }}</td>
                        <td class="text-danger">-₹{{ number_format($slip->deductions, 2) }}</td>
                        <td class="fw-bold text-primary">₹{{ number_format($slip->net_salary, 2) }}</td>
                        <td>
                            @if($slip->payment_method === 'bank_transfer')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">Bank Transfer</span>
                            @elseif($slip->payment_method === 'cheque')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Cheque</span>
                            @elseif($slip->payment_method === 'Cash')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Cash</span>
                            @else
                                @php
                                    $matchedBank = $banks->firstWhere('name', $slip->payment_method);
                                    $payMethodDisplay = $matchedBank 
                                        ? ($matchedBank->name . ' - ' . $matchedBank->branch . ' - ****' . substr($matchedBank->account_number, -4)) 
                                        : $slip->payment_method;
                                @endphp
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">{{ $payMethodDisplay }}</span>
                            @endif
                        </td>
                        <td class="fs-7 text-muted">{{ $slip->payment_date->format('d M Y') }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.payroll.show', $slip) }}" target="_blank" class="btn btn-outline-primary btn-sm px-3" style="border-radius: 8px;">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Payslip
                                </a>
                                <form method="POST" action="{{ route('admin.payroll.destroy', $slip) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to revoke this salary disbursal? This will also delete the associated expense log.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm px-3" style="border-radius: 8px;">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Revoke
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-wallet" style="font-size: 36px;"></i>
                            <div class="mt-2 fw-medium">No salary slips disbursed yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($history->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $history->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
