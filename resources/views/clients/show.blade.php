@extends('layouts.app')

@section('title', 'Client Details')
@section('page-title', 'Client Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
    <li class="breadcrumb-item active">{{ $client->company_name }}</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Company Card -->
    <div class="col-12 col-lg-4">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary-subtle text-primary border border-primary-subtle rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-building fs-3"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ $client->company_name }}</h4>
                        <span class="badge bg-{{ ($client->status ?? 'active') === 'active' ? 'success' : 'danger' }}-subtle text-{{ ($client->status ?? 'active') === 'active' ? 'success' : 'danger' }} border border-{{ ($client->status ?? 'active') === 'active' ? 'success' : 'danger' }}-subtle mt-1">
                            {{ ucfirst($client->status ?? 'active') }}
                        </span>
                    </div>
                </div>

                <hr>

                <div class="text-start">
                    <div class="mb-3">
                        <small class="text-muted d-block">Contact Representative</small>
                        <span class="fw-semibold">{{ $client->contact_person ?? '—' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Email Address</small>
                        <span class="fw-semibold">{{ $client->email }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone / Mobile</small>
                        <span class="fw-semibold">{{ $client->mobile ?? $client->phone ?? '—' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Website</small>
                        @if($client->website)
                            <a href="{{ Str::startsWith($client->website, 'http') ? $client->website : 'https://' . $client->website }}" target="_blank" class="fw-semibold text-decoration-none">
                                {{ $client->website }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">GST Number</small>
                        <span class="fw-semibold">{{ $client->gst_number ?? '—' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Billing Address</small>
                        <span class="fw-medium text-muted">
                            {{ $client->address }}
                            @if($client->city || $client->state || $client->pincode)
                                <br>{{ $client->city }}, {{ $client->state }} - {{ $client->pincode }}
                            @endif
                            @if($client->country)
                                <br>{{ $client->country }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @if($client->notes)
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Internal Notes</h6></div>
                <div class="card-body">
                    <p class="text-muted fs-7 mb-0">{{ $client->notes }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Stats & Logs Tabs -->
    <div class="col-12 col-lg-8">
        <!-- Financial & Project Summary Stats -->
        @php
            $totalInvoiced = $client->invoices->sum('amount');
            $totalPaid = $client->payments->sum('amount');
            $balance = $totalInvoiced - $totalPaid;
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card bg-light border text-center py-3">
                    <div class="fs-4 fw-bold text-primary">{{ $client->projects->count() }}</div>
                    <div class="text-muted fs-7">Active Projects</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-light border text-center py-3">
                    <div class="fs-4 fw-bold text-dark">₹{{ number_format($totalInvoiced, 2) }}</div>
                    <div class="text-muted fs-7">Invoiced Amount</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-light border text-center py-3">
                    <div class="fs-4 fw-bold text-success">₹{{ number_format($totalPaid, 2) }}</div>
                    <div class="text-muted fs-7">Payments Received</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-light border text-center py-3">
                    <div class="fs-4 fw-bold text-danger">₹{{ number_format($balance, 2) }}</div>
                    <div class="text-muted fs-7">Outstanding Balance</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs m-0 border-bottom-0 px-3" id="clientTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-3" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab">Projects</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">Invoices</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">Payments</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="clientTabsContent">
                    <!-- Projects -->
                    <div class="tab-pane fade show active" id="projects" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Deadline</th>
                                        <th>Budget</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($client->projects as $proj)
                                        <tr>
                                            <td class="fw-semibold">
                                                <a href="{{ route('projects.show', $proj) }}" class="text-decoration-none">{{ $proj->name }}</a>
                                            </td>
                                            <td>{{ $proj->deadline ? $proj->deadline->format('d M Y') : '—' }}</td>
                                            <td class="fw-medium">₹{{ number_format($proj->budget, 2) }}</td>
                                            <td>
                                                @php $pct = $proj->progress_percentage; @endphp
                                                <div class="d-flex align-items-center gap-2" style="min-width: 120px;">
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pct }}%"></div>
                                                    </div>
                                                    <span class="fs-7 fw-semibold">{{ $pct }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                    {{ ucwords(str_replace('_', ' ', $proj->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No projects found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Invoices -->
                    <div class="tab-pane fade" id="invoices" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($client->invoices as $inv)
                                        <tr>
                                            <td class="fw-semibold">
                                                <a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none">{{ $inv->invoice_number }}</a>
                                            </td>
                                            <td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}</td>
                                            <td class="fw-medium">₹{{ number_format($inv->amount, 2) }}</td>
                                            <td>
                                                @if($inv->status === 'paid')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Paid</span>
                                                @elseif($inv->status === 'sent')
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Sent</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Draft</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No invoices found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payments -->
                    <div class="tab-pane fade" id="payments" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-sm">
                                <thead>
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Reference #</th>
                                        <th>Mode</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($client->payments as $payment)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                            <td class="fw-semibold text-uppercase">{{ $payment->reference ?? '—' }}</td>
                                            <td>{{ ucfirst($payment->payment_mode) }}</td>
                                            <td class="fw-medium text-success">₹{{ number_format($payment->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No payments found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
