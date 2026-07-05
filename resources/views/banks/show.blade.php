@extends('layouts.app')

@section('title', 'Bank Statement - ' . $bank->name)
@section('page-title', 'Bank Account Statement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banks.index') }}">Banks</a></li>
    <li class="breadcrumb-item active">Statement</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <!-- Header Area -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">{{ $bank->name }}</h4>
            <span class="fs-7 text-muted">
                <i class="bi bi-info-circle me-1"></i> Account Statement & Ledger
            </span>
        </div>
        <div>
            @if($bank->status === 'active')
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-7" style="border-radius: 20px;">
                    <i class="bi bi-check-circle-fill me-1"></i> Active Account
                </span>
            @else
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-7" style="border-radius: 20px;">
                    <i class="bi bi-x-circle-fill me-1"></i> Inactive
                </span>
            @endif
            <a href="{{ route('banks.index') }}" class="btn btn-outline-secondary btn-sm ms-2" style="border-radius: 8px;">
                <i class="bi bi-arrow-left me-1"></i> Back to Accounts
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Account Details Card -->
        <div class="col-lg-6 col-md-12">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary mb-3 text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Account Information</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-8">Account Holder Name</span>
                            <span class="fw-semibold text-dark fs-7">{{ $bank->account_name ?: '—' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-8">Account Number</span>
                            <span class="fw-semibold text-dark font-monospace fs-7">{{ $bank->account_number ?: '—' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-8">IFSC Code</span>
                            <span class="fw-semibold text-dark font-monospace fs-7">{{ $bank->ifsc_code ?: '—' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-8">Branch / Location</span>
                            <span class="fw-semibold text-dark fs-7">{{ $bank->branch ?: '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Metrics Cards -->
        <div class="col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-bold text-secondary text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Opening Balance</span>
                        <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                            <i class="bi bi-wallet2 fs-6"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold text-dark mb-1">₹{{ number_format($bank->opening_balance, 2) }}</h3>
                        <span class="text-muted fs-8">Initial deposit / base amount</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 bg-primary-subtle text-primary border border-primary-subtle" style="border-radius: 12px;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-bold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px; color: #495057;">Current Balance</span>
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                            <i class="bi font-weight-bold bi-currency-rupee fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1" style="color: #0f172a;">₹{{ number_format($currentBalance, 2) }}</h3>
                        <span class="text-dark-50 fs-8" style="color: #475569;">Updated in real-time</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger/Statement Section -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold text-dark" style="font-size: 16px;">Ledger Statement & Transactions</h5>
            <span class="badge bg-light text-dark border px-3 py-1.5 font-monospace fs-8">
                {{ count($statement) }} {{ Str::plural('Transaction', count($statement)) }}
            </span>
        </div>
        
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-4 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 15%;">Date</th>
                        <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 12%;">Reference</th>
                        <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 15%;">Transaction Type</th>
                        <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Description</th>
                        <th class="py-3 text-secondary text-end" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 13%;">Debit (Out)</th>
                        <th class="py-3 text-secondary text-end" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 13%;">Credit (In)</th>
                        <th class="py-3 px-4 text-secondary text-end" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 15%;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statement as $txn)
                        <tr>
                            <td class="px-4 text-dark font-monospace fs-7">
                                {{ $txn->date ? $txn->date->format('d M Y') : '—' }}
                            </td>
                            <td class="font-monospace text-muted fs-7">
                                {{ $txn->reference }}
                            </td>
                            <td>
                                @if($txn->type === 'Credit')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase fs-9">
                                        {{ $txn->category }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-uppercase fs-9">
                                        {{ $txn->category }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-secondary fs-7">
                                {{ $txn->description }}
                            </td>
                            <td class="text-end fw-semibold text-danger fs-7">
                                @if($txn->out > 0)
                                    - ₹{{ number_format($txn->out, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end fw-semibold text-success fs-7">
                                @if($txn->in > 0)
                                    + ₹{{ number_format($txn->in, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 text-end fw-bold text-dark fs-7">
                                ₹{{ number_format($txn->running_balance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-2">
                                    <i class="bi bi-card-list" style="font-size: 36px;"></i>
                                </div>
                                <div class="fw-semibold">No transactions recorded yet</div>
                                <small class="text-muted">Payments, salaries, and expenses linked to this bank will appear here.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
