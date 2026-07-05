@extends('layouts.app')

@section('title', 'Investor Ledger - ' . $investor->name)
@section('page-title', 'Investor Account Ledger')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('investors.index') }}">Investors</a></li>
    <li class="breadcrumb-item active">Statement</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <!-- Header Area -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">{{ $investor->name }}</h4>
            <span class="fs-7 text-muted">
                <i class="bi bi-info-circle me-1"></i> Investor Ledger Statement & Fund History
            </span>
        </div>
        <div>
            @if($investor->status === 'active')
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-7" style="border-radius: 20px;">
                    <i class="bi bi-check-circle-fill me-1"></i> Active Investor
                </span>
            @else
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-7" style="border-radius: 20px;">
                    <i class="bi bi-x-circle-fill me-1"></i> Inactive
                </span>
            @endif
            <button type="button" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#addTransactionModal" style="border-radius: 8px; padding: 6px 14px;">
                <i class="bi bi-plus-circle me-1"></i> Add / Deduct Money
            </button>
            <a href="{{ route('investors.index') }}" class="btn btn-outline-secondary btn-sm ms-2" style="border-radius: 8px;">
                <i class="bi bi-arrow-left me-1"></i> Back to Investors
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Investor Details Card -->
        <div class="col-lg-6 col-md-12">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary mb-3 text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Investor Information</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-8">Email Address</span>
                            <span class="fw-semibold text-dark fs-7">{{ $investor->email ?: '—' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-8">Phone Number</span>
                            <span class="fw-semibold text-dark fs-7">{{ $investor->phone ?: '—' }}</span>
                        </div>
                        <div class="col-sm-12">
                            <span class="text-muted d-block fs-8">Remarks / Description</span>
                            <span class="fw-semibold text-dark fs-7">{{ $investor->description ?: '—' }}</span>
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
                        <h3 class="fw-bold text-dark mb-1">₹{{ number_format($investor->opening_balance, 2) }}</h3>
                        <span class="text-muted fs-8">Initial opening amount</span>
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
                                {{ $txn->date ? (\Carbon\Carbon::parse($txn->date)->format('d M Y')) : '—' }}
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
                                <small class="text-muted">Manual deposits, withdrawals, and salaries linked to this investor will appear here.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Transaction Modal --}}
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-cash me-2"></i>Add or Deduct Money</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('investors.transactions.store', $investor) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Transaction Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required style="border-radius:8px;">
                            <option value="Credit">Add Money (Credit)</option>
                            <option value="Debit">Deduct Money (Debit)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">₹</span>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="0.00" required style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Reference No (optional)</label>
                        <input type="text" name="reference" class="form-control" placeholder="e.g. TXN-0001, Bank Transfer Ref" style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Description / Remarks</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe the reason for addition or deduction..." style="border-radius:8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Submit Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
