@extends('layouts.app')

@section('title', 'Profit & Loss Report')
@section('page-title', 'Profit & Loss Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Profit & Loss</li>
@endsection

@php
    $monthName = DateTime::createFromFormat('!m', $month)->format('F');
    
    // Fetch detailed records for the chosen month/year to show breakdown
    $detailedIncome = \App\Models\Payment::with(['client', 'project'])
        ->whereMonth('payment_date', $month)
        ->whereYear('payment_date', $year)
        ->get();
        
    $detailedExpenses = \App\Models\Expense::with(['project', 'addedBy'])
        ->whereMonth('date', $month)
        ->whereYear('date', $year)
        ->get();
        
    $net = $income - $expenses;
    $profitMargin = $income > 0 ? ($net / $income) * 100 : 0;
    
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
        7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    
    $currentYear = date('Y');
    $years = range($currentYear - 4, $currentYear + 2);
@endphp

@section('content')
<!-- Filter bar -->
<div class="card border border-light shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('reports.profit-loss') }}" class="row g-3 align-items-center">
            <div class="col-12 col-md-5">
                <label class="form-label fs-7 fw-semibold text-secondary">Month</label>
                <select name="month" class="form-select form-select-sm">
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label fs-7 fw-semibold text-secondary">Year</label>
                <select name="year" class="form-select form-select-sm">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-grid mt-md-4 pt-md-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter"></i> Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Financial Summary Cards -->
<div class="row g-4 mb-4">
    <!-- Income -->
    <div class="col-12 col-lg-4">
        <div class="card border border-light shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary fw-semibold">Total Revenue (Income)</span>
                    <div class="bg-success-subtle text-success border border-success-subtle rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-up-right fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-extrabold text-success mb-2">₹{{ number_format($income, 2) }}</h2>
                <p class="text-muted fs-7 mb-0">{{ $detailedIncome->count() }} Client Payments Received in {{ $monthName }} {{ $year }}</p>
            </div>
        </div>
    </div>

    <!-- Expenses -->
    <div class="col-12 col-lg-4">
        <div class="card border border-light shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary fw-semibold">Total Expenditures (Expenses)</span>
                    <div class="bg-danger-subtle text-danger border border-danger-subtle rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-down-left fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-extrabold text-danger mb-2">₹{{ number_format($expenses, 2) }}</h2>
                <p class="text-muted fs-7 mb-0">{{ $detailedExpenses->count() }} Outward Expense Claims Registered</p>
            </div>
        </div>
    </div>

    <!-- Net Profit / Loss -->
    <div class="col-12 col-lg-4">
        <div class="card border border-light shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary fw-semibold">Net P&L Balance</span>
                    <div class="bg-{{ $net >= 0 ? 'success' : 'danger' }}-subtle text-{{ $net >= 0 ? 'success' : 'danger' }} border border-{{ $net >= 0 ? 'success' : 'danger' }}-subtle rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-calculator fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-extrabold text-{{ $net >= 0 ? 'success' : 'danger' }} mb-2">
                    {{ $net >= 0 ? '+' : '-' }}₹{{ number_format(abs($net), 2) }}
                </h2>
                <div class="d-flex align-items-center gap-2">
                    @if($net >= 0)
                        <span class="badge bg-success-subtle text-success border border-success-subtle">PROFITABLE</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">NET LOSS</span>
                    @endif
                    <span class="text-muted fs-7">Margin: {{ number_format($profitMargin, 1) }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Visual comparison chart -->
<div class="card border border-light shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Revenue vs Expense Ratio</h6>
        @php
            $totalVolume = $income + $expenses;
            $incomePct = $totalVolume > 0 ? ($income / $totalVolume) * 100 : 50;
            $expensePct = $totalVolume > 0 ? ($expenses / $totalVolume) * 100 : 50;
        @endphp
        <div class="progress" style="height: 25px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $incomePct }}%" aria-valuenow="{{ $incomePct }}" aria-valuemin="0" aria-valuemax="100" title="Revenue">
                {{ number_format($incomePct, 1) }}% Revenue
            </div>
            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $expensePct }}%" aria-valuenow="{{ $expensePct }}" aria-valuemin="0" aria-valuemax="100" title="Expenses">
                {{ number_format($expensePct, 1) }}% Expense
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Detailed Income -->
    <div class="col-12 col-xl-6">
        <div class="card border border-light shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-success"><i class="bi bi-plus-circle-fill"></i> Income breakdown</h6>
                <span class="badge bg-success text-white">₹{{ number_format($income, 2) }}</span>
            </div>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th>Date</th>
                            <th>Client / Ref</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailedIncome as $inc)
                            <tr>
                                <td>{{ $inc->payment_date ? $inc->payment_date->format('d M Y') : '—' }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $inc->client?->company_name ?: 'Unknown Client' }}</div>
                                    <div class="text-muted fs-8">{{ $inc->payment_reference ?: 'Direct Payment' }}</div>
                                </td>
                                <td class="text-end fw-bold text-success">₹{{ number_format($inc->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="bi bi-x-circle fs-3 text-secondary"></i>
                                    <div class="mt-2">No income received in this month.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Expenses -->
    <div class="col-12 col-xl-6">
        <div class="card border border-light shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-dash-circle-fill"></i> Expenses breakdown</h6>
                <span class="badge bg-danger text-white">₹{{ number_format($expenses, 2) }}</span>
            </div>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th>Date</th>
                            <th>Category / Details</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailedExpenses as $exp)
                            <tr>
                                <td>{{ $exp->date ? $exp->date->format('d M Y') : '—' }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $exp->title }}</div>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fs-8">{{ $exp->category_label }}</span>
                                </td>
                                <td class="text-end fw-bold text-danger">₹{{ number_format($exp->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="bi bi-x-circle fs-3 text-secondary"></i>
                                    <div class="mt-2">No expenses logged in this month.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
