@extends('layouts.app')

@section('title', 'AMC Contract Details - ' . $projectAmc->project->name)
@section('page-title', 'Project AMC Contract Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('project-amcs.index') }}">Project AMCs</a></li>
    <li class="breadcrumb-item active">Statement</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <!-- Header Area -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">{{ $projectAmc->project->name }}</h4>
            <span class="fs-7 text-muted">
                <i class="bi bi-info-circle me-1"></i> AMC Renewal Payments & History Ledger
            </span>
        </div>
        <div>
            <span class="badge bg-{{ $projectAmc->status_badge }}-subtle text-{{ $projectAmc->status_badge }} border border-{{ $projectAmc->status_badge }}-subtle px-3 py-2 fs-7" style="border-radius: 20px;">
                <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> 
                @if($projectAmc->status === 'pending_renewal')
                    Pending Renewal
                @else
                    {{ ucfirst($projectAmc->status) }}
                @endif
            </span>
            @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
                <button type="button" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#addPaymentModal" style="border-radius: 8px; padding: 6px 14px;">
                    <i class="bi bi-plus-circle me-1"></i> Log AMC Payment
                </button>
            @endif
            <a href="{{ route('project-amcs.index') }}" class="btn btn-outline-secondary btn-sm ms-2" style="border-radius: 8px;">
                <i class="bi bi-arrow-left me-1"></i> Back to AMCs
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Contract Information -->
        <div class="col-lg-6 col-md-12">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary mb-3 text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">Contract Information</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-8">Client Name</span>
                            <span class="fw-semibold text-dark fs-7">
                                {{ $projectAmc->project->client ? $projectAmc->project->client->company_name : '—' }}
                            </span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-8">Billing Frequency</span>
                            <span class="fw-semibold text-dark fs-7 text-capitalize">{{ str_replace('_', ' ', $projectAmc->frequency) }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-8">Start Date</span>
                            <span class="fw-semibold text-dark fs-7">{{ $projectAmc->start_date->format('d M Y') }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block fs-8">End Date</span>
                            <span class="fw-semibold text-dark fs-7">{{ $projectAmc->end_date->format('d M Y') }}</span>
                        </div>
                        <div class="col-sm-12">
                            <span class="text-muted d-block fs-8">Contract Remarks</span>
                            <span class="fw-semibold text-dark fs-7">{{ $projectAmc->remarks ?: '—' }}</span>
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
                        <span class="fw-bold text-secondary text-uppercase" style="letter-spacing: 0.05em; font-size: 11px;">AMC Value</span>
                        <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                            <i class="bi bi-wallet2 fs-6"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold text-dark mb-1">₹{{ number_format($projectAmc->amount, 2) }}</h3>
                        <span class="text-muted fs-8">Total contracted amount</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            @php
                $totalPaid = $projectAmc->logs->sum('amount_paid');
                $pending = max(0, $projectAmc->amount - $totalPaid);
            @endphp
            <div class="card border-0 shadow-sm h-100 bg-success-subtle text-success border border-success-subtle" style="border-radius: 12px;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-bold text-uppercase" style="letter-spacing: 0.05em; font-size: 11px; color: #495057;">Total Payments Received</span>
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                            <i class="bi font-weight-bold bi-currency-rupee fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1" style="color: #0f172a;">₹{{ number_format($totalPaid, 2) }}</h3>
                        <span class="text-dark-50 fs-8" style="color: #475569;">
                            @if($pending > 0)
                                Pending Balance: ₹{{ number_format($pending, 2) }}
                            @else
                                Fully Cleared
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Renewal / Payment History Logs -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold text-dark" style="font-size: 16px;">Renewal & Payments History</h5>
            <span class="badge bg-light text-dark border px-3 py-1.5 font-monospace fs-8">
                {{ count($projectAmc->logs) }} {{ Str::plural('Payment Log', count($projectAmc->logs)) }}
            </span>
        </div>
        
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-4 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 20%;">Payment Date</th>
                        <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 20%;">Reference No</th>
                        <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 20%;">Payment Mode</th>
                        <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Description/Remarks</th>
                        <th class="py-3 px-4 text-secondary text-end" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 20%;">Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projectAmc->logs as $log)
                        <tr>
                            <td class="px-4 text-dark font-monospace fs-7">
                                {{ $log->payment_date ? $log->payment_date->format('d M Y') : '—' }}
                            </td>
                            <td class="font-monospace text-muted fs-7">
                                {{ $log->reference_no ?: '—' }}
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border px-2.5 py-1 fw-medium fs-8" style="border-radius:20px;">
                                    {{ $log->payment_mode ?: '—' }}
                                </span>
                            </td>
                            <td class="text-secondary fs-7">
                                {{ $log->remarks ?: 'No remarks' }}
                            </td>
                            <td class="px-4 text-end fw-bold text-success fs-7">
                                ₹{{ number_format($log->amount_paid, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="mb-2">
                                    <i class="bi bi-card-list" style="font-size: 36px;"></i>
                                </div>
                                <div class="fw-semibold">No payments logged yet</div>
                                <small class="text-muted">Register AMC renewal or payments received under this contract to view logs.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Payment Log Modal --}}
@if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Log AMC Renewal Payment</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('project-amcs.logs.store', $projectAmc) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Amount Paid <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">₹</span>
                            <input type="number" step="0.01" min="0.01" name="amount_paid" class="form-control" placeholder="0.00" value="{{ $pending }}" required style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Payment Mode</label>
                        <input type="text" name="payment_mode" class="form-control" placeholder="e.g. Bank Transfer, GPay, Check" style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Reference / Voucher No</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="e.g. UPI Ref, Invoice Ref" style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Remarks / Description</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Renewal notes, period covered..." style="border-radius:8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
