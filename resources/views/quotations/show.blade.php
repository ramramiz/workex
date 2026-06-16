@extends('layouts.app')

@section('title', 'Quotation details')
@section('page-title', 'Quotation details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('quotations.index') }}">Quotations</a></li>
    <li class="breadcrumb-item active">{{ $quotation->quotation_number }}</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Main Proposal Document Card -->
    <div class="col-12 col-lg-9 mx-auto">
        <!-- Top Controls -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to List</a>
            <div class="d-flex gap-2">
                <a href="{{ route('quotations.pdf', $quotation) }}" class="btn btn-info btn-sm text-white"><i class="bi bi-file-pdf"></i> Download PDF</a>
                <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                
                @if($quotation->status !== 'accepted')
                    <form method="POST" action="{{ route('quotations.convert', $quotation) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Accept & Convert to Project</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border border-light">
            <div class="card-body p-4 p-md-5">
                <!-- Document Header -->
                <div class="row align-items-start mb-4">
                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-primary text-white rounded p-2 d-inline-block"><i class="bi bi-lightning-charge-fill fs-4"></i></div>
                            <span class="fs-4 fw-bold">WorkeX</span>
                        </div>
                        <p class="text-muted fs-7 mb-0">
                            System settings company info will appear here.<br>
                            Email: info@company.com | Phone: +91-9999999999
                        </p>
                    </div>
                    <div class="col-12 col-md-6 text-md-end">
                        <h2 class="text-uppercase text-primary fw-bold mb-1">Proposal</h2>
                        <div class="fw-semibold"># {{ $quotation->quotation_number }}</div>
                        <div class="text-muted fs-7 mt-1">Date: {{ $quotation->created_at->format('d M Y') }}</div>
                        @if($quotation->valid_until)
                            <div class="text-danger fs-7">Valid Until: {{ $quotation->valid_until->format('d M Y') }}</div>
                        @endif
                    </div>
                </div>

                <hr class="my-4">

                <!-- Client Billing Address -->
                <div class="row mb-4">
                    <div class="col-12 col-md-6">
                        <h6 class="text-uppercase text-muted fs-8 mb-2">Prepared For:</h6>
                        @if($quotation->client)
                            <div class="fw-bold fs-6 text-dark">{{ $quotation->client->company_name }}</div>
                            <div class="text-muted fs-7 mt-1">
                                Attn: {{ $quotation->client->contact_person ?? '—' }}<br>
                                {{ $quotation->client->address }}<br>
                                {{ $quotation->client->city }}, {{ $quotation->client->state }} - {{ $quotation->client->pincode }}<br>
                                Email: {{ $quotation->client->email }}
                            </div>
                        @elseif($quotation->lead)
                            <div class="fw-bold fs-6 text-dark">{{ $quotation->lead->client_name }}</div>
                            <div class="text-muted fs-7 mt-1">
                                Email: {{ $quotation->lead->client_email ?? '—' }}<br>
                                Phone: {{ $quotation->lead->client_phone ?? '—' }}
                            </div>
                        @else
                            <div class="text-muted">No Client Attached</div>
                        @endif
                    </div>
                </div>

                <!-- Quotation Title / Scope of Work -->
                <div class="mb-4">
                    <h5 class="fw-bold mb-2">{{ $quotation->title }}</h5>
                    @if($quotation->scope)
                        <div class="bg-light p-3 border rounded text-muted fs-7" style="white-space: pre-wrap;">{{ $quotation->scope }}</div>
                    @endif
                </div>

                <!-- Item Breakdown Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Scope / Module Description</th>
                                <th style="width: 180px;" class="text-end">Price (INR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quotation->modules ?? [] as $mod)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $mod['name'] ?? '' }}</div>
                                    </td>
                                    <td class="text-end fw-medium">₹{{ number_format($mod['price'] ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-3 text-muted fs-7">No modules added.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Calculations -->
                <div class="row justify-content-end mb-4">
                    <div class="col-12 col-md-5">
                        <table class="table table-sm table-borderless fs-7 mb-0">
                            <tr>
                                <td class="text-muted">Subtotal:</td>
                                <td class="text-end fw-semibold">₹{{ number_format($quotation->subtotal ?? 0, 2) }}</td>
                            </tr>
                            @if($quotation->discount > 0)
                                <tr>
                                    <td class="text-muted">Discount:</td>
                                    <td class="text-end fw-semibold text-danger">- ₹{{ number_format($quotation->discount, 2) }}</td>
                                </tr>
                            @endif
                            @if($quotation->tax > 0)
                                <tr>
                                    <td class="text-muted">GST Tax:</td>
                                    <td class="text-end fw-semibold">₹{{ number_format($quotation->tax, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="border-top border-dark">
                                <td class="fw-bold fs-6 pt-2">Grand Total:</td>
                                <td class="text-end fw-bold text-success fs-5 pt-2">₹{{ number_format($quotation->total ?? 0, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                @if($quotation->terms)
                    <div class="border-top pt-4">
                        <h6 class="text-uppercase text-muted fs-8 mb-2">Terms & Conditions:</h6>
                        <div class="text-muted fs-7" style="white-space: pre-wrap;">{{ $quotation->terms }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
