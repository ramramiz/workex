@extends('layouts.app')

@section('title', 'Invoice Details')
@section('page-title', 'Invoice Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
    <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Main Invoice Document -->
    <div class="col-12 col-lg-9 mx-auto">
        <!-- Controls -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to List</a>
            <div class="d-flex gap-2">
                <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-info btn-sm text-white"><i class="bi bi-file-pdf"></i> Download PDF</a>
                @if($invoice->status !== 'paid')
                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                    
                    @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
                        <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success btn-sm"><i class="bi bi-credit-card"></i> Record Payment</a>
                    @endif

                    <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-envelope"></i> Send Invoice</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border border-light">
            <div class="card-body p-4 p-md-5">
                <!-- Status Banner -->
                @if($invoice->status === 'paid')
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 py-2">
                        <i class="bi bi-check-circle-fill"></i> This invoice has been fully paid and cleared.
                    </div>
                @elseif($invoice->is_overdue)
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 py-2">
                        <i class="bi bi-exclamation-octagon-fill"></i> Warning: This invoice is OVERDUE by {{ now()->diffInDays($invoice->due_date) }} days.
                    </div>
                @endif

                <!-- Document Header -->
                <div class="row align-items-start mb-4">
                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-primary text-white rounded p-2 d-inline-block"><i class="bi bi-lightning-charge-fill fs-4"></i></div>
                            <span class="fs-4 fw-bold">WorkeX</span>
                        </div>
                        <p class="text-muted fs-7 mb-0">
                            Email: billing@company.com | Phone: +91-9999999999
                        </p>
                    </div>
                    <div class="col-12 col-md-6 text-md-end">
                        <h2 class="text-uppercase text-primary fw-bold mb-1">Invoice</h2>
                        <div class="fw-semibold"># {{ $invoice->invoice_number }}</div>
                        <div class="text-muted fs-7 mt-1">Date: {{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '—' }}</div>
                        <div class="text-danger fs-7">Due Date: {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}</div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Client Billing Address -->
                <div class="row mb-4">
                    <div class="col-12 col-md-6">
                        <h6 class="text-uppercase text-muted fs-8 mb-2">Billed To:</h6>
                        @if($invoice->client)
                            <div class="fw-bold fs-6 text-dark">{{ $invoice->client->company_name }}</div>
                            <div class="text-muted fs-7 mt-1">
                                Attn: {{ $invoice->client->contact_person ?? '—' }}<br>
                                {{ $invoice->client->address }}<br>
                                {{ $invoice->client->city }}, {{ $invoice->client->state }} - {{ $invoice->client->pincode }}<br>
                                Email: {{ $invoice->client->email }}
                            </div>
                        @else
                            <div class="text-muted">No Client Attached</div>
                        @endif
                    </div>
                </div>

                <!-- Items Breakdown Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Item Description</th>
                                <th style="width: 100px;" class="text-center">Qty</th>
                                <th style="width: 160px;" class="text-end">Unit Price</th>
                                <th style="width: 180px;" class="text-end">Amount (INR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items ?? [] as $item)
                                @php
                                    $qty = floatval($item['qty'] ?? 1);
                                    $price = floatval($item['price'] ?? 0);
                                    $rowTotal = $qty * $price;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item['name'] ?? '' }}</div>
                                    </td>
                                    <td class="text-center">{{ $qty }}</td>
                                    <td class="text-end">₹{{ number_format($price, 2) }}</td>
                                    <td class="text-end fw-medium">₹{{ number_format($rowTotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted fs-7">No items added.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Calculations -->
                <div class="row justify-content-end mb-4">
                    <div class="col-12 col-md-6">
                        <table class="table table-sm table-borderless fs-7 mb-0">
                            <tr>
                                <td class="text-muted">Subtotal:</td>
                                <td class="text-end fw-semibold">₹{{ number_format($invoice->subtotal ?? 0, 2) }}</td>
                            </tr>
                            @if($invoice->discount > 0)
                                <tr>
                                    <td class="text-muted">Discount:</td>
                                    <td class="text-end fw-semibold text-danger">- ₹{{ number_format($invoice->discount, 2) }}</td>
                                </tr>
                            @endif
                            @if($invoice->tax_amount > 0)
                                <tr>
                                    <td class="text-muted">GST Tax ({{ $invoice->tax_percentage ?? 18 }}%):</td>
                                    <td class="text-end fw-semibold">₹{{ number_format($invoice->tax_amount, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="border-top border-secondary-subtle">
                                <td class="fw-bold fs-6 pt-2">Grand Total:</td>
                                <td class="text-end fw-bold text-dark fs-6 pt-2">₹{{ number_format($invoice->total ?? 0, 2) }}</td>
                            </tr>
                            <tr class="text-success">
                                <td class="fw-bold pt-1">Amount Paid:</td>
                                <td class="text-end fw-bold pt-1">₹{{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
                            </tr>
                            <tr class="border-top border-dark text-danger">
                                <td class="fw-bold fs-6 pt-2">Balance Due:</td>
                                <td class="text-end fw-bold fs-5 pt-2">₹{{ number_format($invoice->balance_amount ?? $invoice->total, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                @if($invoice->notes)
                    <div class="border-top pt-4">
                        <h6 class="text-uppercase text-muted fs-8 mb-2">Payment Details / Notes:</h6>
                        <div class="text-muted fs-7" style="white-space: pre-wrap;">{{ $invoice->notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
