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
            @php
                $daysRemaining = (int) today()->diffInDays($projectAmc->end_date, false);
            @endphp
            @if($projectAmc->status !== 'expired' && $daysRemaining >= 0 && $daysRemaining <= 30)
                <span class="badge bg-warning text-dark border border-warning-subtle px-3 py-2 fs-7 ms-1" style="border-radius: 20px; font-weight: 600;">
                    <i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> {{ $daysRemaining }} {{ Str::plural('day', $daysRemaining) }} left
                </span>
            @endif
            @if($projectAmc->project && $projectAmc->project->client && $projectAmc->project->client->phone)
                <form method="POST" action="{{ route('project-amcs.send-whatsapp-reminder', $projectAmc) }}" class="d-inline" onsubmit="return confirm('Send WhatsApp AMC renewal reminder to this client?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-success btn-sm ms-2" style="border-radius: 8px; padding: 6px 14px; font-weight: 500;">
                        <i class="bi bi-whatsapp me-1"></i> Send WhatsApp Reminder
                    </button>
                </form>
            @else
                <button type="button" class="btn btn-outline-success btn-sm ms-2 disabled" style="border-radius: 8px; padding: 6px 14px; font-weight: 500;" title="Client has no phone number" disabled>
                    <i class="bi bi-whatsapp me-1"></i> Send WhatsApp Reminder
                </button>
            @endif
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAccounts())
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
                            <span class="fw-semibold text-dark fs-7">
                                {{ $projectAmc->end_date->format('d M Y') }}
                                @php
                                    $daysRemainingVal = (int) today()->diffInDays($projectAmc->end_date, false);
                                @endphp
                                @if($projectAmc->status === 'expired' || $daysRemainingVal < 0)
                                    <span class="text-danger fs-8 fw-semibold ms-1">({{ abs($daysRemainingVal) }} {{ Str::plural('day', abs($daysRemainingVal)) }} overdue)</span>
                                @elseif($daysRemainingVal <= 30)
                                    <span class="text-warning-emphasis fs-8 fw-bold ms-1">({{ $daysRemainingVal }} {{ Str::plural('day', $daysRemainingVal) }} left)</span>
                                @else
                                    <span class="text-muted fs-8 fw-normal ms-1">({{ $daysRemainingVal }} days left)</span>
                                @endif
                            </span>
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
                    <div class="mb-3">
                        <h3 class="fw-bold text-dark mb-1">
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAccounts())
                                ₹{{ number_format($projectAmc->amount, 2) }}
                            @else
                                —
                            @endif
                        </h3>
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
                        <h3 class="fw-bold mb-1" style="color: #0f172a;">
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAccounts())
                                ₹{{ number_format($totalPaid, 2) }}
                            @else
                                —
                            @endif
                        </h3>
                        <span class="text-dark-50 fs-8" style="color: #475569;">
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAccounts())
                                @if($pending > 0)
                                    Pending Balance: ₹{{ number_format($pending, 2) }}
                                @else
                                    Fully Cleared
                                @endif
                            @else
                                Pending Balance: —
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Renewal / Payment History & WhatsApp Logs -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <ul class="nav nav-pills" id="logTabs" role="tablist" style="background: #f1f5f9; padding: 4px; border-radius: 10px;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-3 py-1.5 fw-semibold" id="payment-tab" data-bs-toggle="pill" data-bs-target="#payment-pane" type="button" role="tab" aria-controls="payment-pane" aria-selected="true" style="font-size: 13px; border-radius: 8px;">
                        <i class="bi bi-credit-card me-1"></i> Payment History ({{ count($projectAmc->logs) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-3 py-1.5 fw-semibold ms-1" id="whatsapp-tab" data-bs-toggle="pill" data-bs-target="#whatsapp-pane" type="button" role="tab" aria-controls="whatsapp-pane" aria-selected="false" style="font-size: 13px; border-radius: 8px;">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp Reminders ({{ count($whatsappLogs) }})
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="tab-content" id="logTabsContent">
            <!-- Payment History Pane -->
            <div class="tab-pane fade show active" id="payment-pane" role="tabpanel" aria-labelledby="payment-tab">
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
                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isAccounts())
                                            ₹{{ number_format($log->amount_paid, 2) }}
                                        @else
                                            —
                                        @endif
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

            <!-- WhatsApp Reminders Pane -->
            <div class="tab-pane fade" id="whatsapp-pane" role="tabpanel" aria-labelledby="whatsapp-tab">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-4 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 25%;">Reminder Schedule</th>
                                <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 20%;">Alert Date</th>
                                <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 15%;">Send to Number</th>
                                <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 20%;">Message Status</th>
                                <th class="py-3 px-4 text-secondary text-end" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600; width: 20%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $thresholds = [
                                    ['days' => 40, 'label' => '40 Days Before Expiry'],
                                    ['days' => 30, 'label' => '30 Days Before Expiry'],
                                    ['days' => 20, 'label' => '20 Days Before Expiry'],
                                    ['days' => 10, 'label' => '10 Days Before Expiry'],
                                    ['days' => 5,  'label' => '5 Days Before Expiry'],
                                    ['days' => 3,  'label' => '3 Days Before Expiry'],
                                    ['days' => 1,  'label' => '1 Day Before Expiry (Tomorrow)'],
                                    ['days' => 0,  'label' => 'Expiry Day (Today)']
                                ];
                                $alertPhone = $projectAmc->alert_phone ?: ($projectAmc->project?->client?->phone ?: '—');
                            @endphp
                            @foreach($thresholds as $item)
                                @php
                                    $alertDate = $projectAmc->end_date->copy()->subDays($item['days']);
                                    
                                    // Look up if a reminder was already sent for this threshold during this contract period
                                    $sentLog = $whatsappLogs->first(function($log) use ($item, $projectAmc) {
                                        return (int) ($log->new_values['days_remaining'] ?? -1) === $item['days']
                                            && ($log->new_values['end_date'] ?? '') === $projectAmc->end_date->toDateString();
                                    });
                                @endphp
                                <tr>
                                    <td class="px-4 fw-semibold text-dark fs-7">
                                        {{ $item['label'] }}
                                    </td>
                                    <td class="text-secondary font-monospace fs-7">
                                        {{ $alertDate->format('d M Y') }}
                                    </td>
                                    <td class="text-secondary font-monospace fs-7">
                                        {{ $alertPhone }}
                                    </td>
                                    <td>
                                        @if($sentLog)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-semibold fs-8" style="border-radius:20px;">
                                                <i class="bi bi-check2-all me-1"></i> Sent
                                            </span>
                                            <small class="d-block text-muted font-monospace mt-0.5" style="font-size: 10px;">
                                                {{ $sentLog->created_at->format('d M Y h:i A') }}
                                            </small>
                                        @elseif(today()->isSameDay($alertDate))
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 fw-semibold fs-8" style="border-radius:20px;">
                                                <i class="bi bi-clock-history me-1"></i> Due Today
                                            </span>
                                        @elseif(today()->greaterThan($alertDate))
                                            <span class="badge bg-light text-secondary border px-2.5 py-1 fw-semibold fs-8" style="border-radius:20px;">
                                                <i class="bi bi-x-circle me-1 text-danger"></i> Not Sent / Past
                                            </span>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fw-semibold fs-8" style="border-radius:20px;">
                                                <i class="bi bi-hourglass-split me-1"></i> Scheduled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            @if($sentLog)
                                                <button type="button" class="btn btn-outline-info btn-sm" style="border-radius: 6px; padding: 4px 10px;"
                                                        data-bs-toggle="modal" data-bs-target="#viewWhatsappLogModal"
                                                        data-phone="{{ $sentLog->new_values['phone'] ?? $alertPhone }}"
                                                        data-time="{{ $sentLog->created_at->format('d M Y h:i A') }}"
                                                        data-sender="{{ $sentLog->user ? $sentLog->user->name : 'System (Auto)' }}"
                                                        data-message="{{ $sentLog->new_values['message'] ?? $sentLog->description }}"
                                                        onclick="showWhatsappDetails(this)">
                                                    <i class="bi bi-eye"></i> View Message
                                                </button>
                                            @endif

                                            <form method="POST" action="{{ route('project-amcs.send-whatsapp-reminder', [$projectAmc, 'days_remaining' => $item['days']]) }}" 
                                                  onsubmit="return confirm('Do you want to manually send the reminder for {{ $item['label'] }} now?')">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-sm" style="border-radius: 6px; padding: 4px 10px;">
                                                    <i class="bi bi-whatsapp"></i> {{ $sentLog ? 'Resend' : 'Send' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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

{{-- View WhatsApp Log Modal --}}
<div class="modal fade" id="viewWhatsappLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-whatsapp me-2 text-success"></i>Sent WhatsApp Reminder</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <small class="text-muted d-block" style="font-size: 11px;">RECIPIENT NUMBER</small>
                    <span id="detail-phone" class="fw-bold text-dark font-monospace fs-7"></span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block" style="font-size: 11px;">SENT DATE/TIME</small>
                    <span id="detail-time" class="fw-semibold text-secondary fs-8"></span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block" style="font-size: 11px;">SENT BY</small>
                    <span id="detail-sender" class="fw-semibold text-secondary fs-8"></span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block" style="font-size: 11px; text-transform: uppercase;">Message Content</small>
                    <div class="p-3 border rounded text-dark fs-7 mt-1" id="detail-message" style="background-color: #efeae2; max-height: 250px; overflow-y: auto; white-space: pre-wrap; font-family: inherit; line-height: 1.5; border-color: #e2e8f0; border-left: 4px solid #075e54;"></div>
                </div>
            </div>
            <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal" style="border-radius:6px;">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showWhatsappDetails(button) {
    const phone = button.getAttribute('data-phone');
    const time = button.getAttribute('data-time');
    const sender = button.getAttribute('data-sender');
    const message = button.getAttribute('data-message');

    document.getElementById('detail-phone').innerText = phone;
    document.getElementById('detail-time').innerText = time;
    document.getElementById('detail-sender').innerText = sender;
    document.getElementById('detail-message').innerText = message;
}
</script>
@endpush
@endsection
