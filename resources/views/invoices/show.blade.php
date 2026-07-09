@extends('layouts.app')

@section('title', 'Tax Invoice Details')
@section('page-title', 'Tax Invoice Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
    <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
@endsection

@section('content')
@php
    $stateCodes = [
        'jammu & kashmir' => '01', 'jammu and kashmir' => '01', 'himachal pradesh' => '02', 'punjab' => '03',
        'chandigarh' => '04', 'uttarakhand' => '05', 'haryana' => '06', 'delhi' => '07', 'rajasthan' => '08',
        'uttar pradesh' => '09', 'bihar' => '10', 'sikkim' => '11', 'arunachal pradesh' => '12', 'assam' => '18',
        'nagaland' => '13', 'manipur' => '14', 'mizoram' => '15', 'tripura' => '16', 'meghalaya' => '17',
        'west bengal' => '19', 'jharkhand' => '20', 'odisha' => '21', 'chhattisgarh' => '22', 'madhya pradesh' => '23',
        'gujarat' => '24', 'daman & diu' => '25', 'daman and diu' => '25', 'dadra & nagar haveli' => '26', 'dadra and nagar haveli' => '26',
        'maharashtra' => '27', 'andhra pradesh' => '37', 'karnataka' => '29', 'goa' => '30', 'lakshadweep' => '31',
        'kerala' => '32', 'tamil nadu' => '33', 'puducherry' => '34', 'andaman & nicobar islands' => '35', 'andaman and nicobar islands' => '35',
        'telangana' => '36', 'ladakh' => '38'
    ];

    $cityStateMapping = [
        'kerala' => ['kerala', 'kochi', 'cochin', 'kozhikode', 'calicut', 'malappuram', 'randathani', 'trivandrum', 'thiruvananthapuram', 'thrissur', 'palakkad', 'kannur', 'kollam', 'alappuzha', 'idukki', 'kottayam', 'pathanamthitta', 'wayanad', 'kasaragod'],
        'karnataka' => ['karnataka', 'bangalore', 'bengaluru', 'mysore', 'mysuru', 'mangalore', 'mangaluru', 'hubli', 'dharwad', 'belgaum'],
        'maharashtra' => ['maharashtra', 'mumbai', 'bombay', 'pune', 'nagpur', 'thane', 'nashik', 'aurangabad', 'solapur'],
        'delhi' => ['delhi', 'new delhi'],
        'uttar pradesh' => ['uttar pradesh', 'noida', 'greater noida', 'ghaziabad', 'lucknow', 'kanpur', 'agra', 'varanasi', 'meerut'],
        'tamil nadu' => ['tamil nadu', 'chennai', 'madras', 'coimbatore', 'madurai', 'trichy', 'salem'],
        'telangana' => ['telangana', 'hyderabad', 'secunderabad', 'warangal'],
        'west bengal' => ['west bengal', 'kolkata', 'calcutta', 'howrah', 'durgapur'],
        'gujarat' => ['gujarat', 'ahmedabad', 'surat', 'vadodara', 'baroda', 'rajkot']
    ];

    $supplierCompany = $invoice->company ?? (auth()->check() ? auth()->user()->company : null);
    
    // Smarter state detection for supplier
    $supplierAddress = $supplierCompany?->address ?? '123 Tech Park, Sector 62, Noida, Uttar Pradesh - 201301';
    $supplierState = 'Kerala'; // Standard default for Techsoul
    $foundSupplier = false;
    foreach ($cityStateMapping as $state => $keywords) {
        foreach ($keywords as $kw) {
            if (str_contains(strtolower($supplierAddress), $kw)) {
                $supplierState = ucwords($state);
                $foundSupplier = true;
                break 2;
            }
        }
    }
    if (!$foundSupplier) {
        foreach (array_keys($stateCodes) as $sName) {
            if (str_contains(strtolower($supplierAddress), $sName)) {
                $supplierState = ucwords($sName);
                break;
            }
        }
    }
    $supplierStateLower = strtolower($supplierState);
    $supplierStateCode = $stateCodes[$supplierStateLower] ?? '32'; // Default to Kerala (32)

    // Smarter state detection for buyer
    $buyerAddress = ($invoice->client?->address ?? '') . ' ' . ($invoice->client?->city ?? '') . ' ' . ($invoice->client?->state ?? '');
    $buyerState = $supplierState; // Default to supplier state
    $foundBuyer = false;
    if ($invoice->client?->state) {
        foreach (array_keys($stateCodes) as $sName) {
            if (strtolower(trim($invoice->client->state)) === $sName) {
                $buyerState = ucwords($sName);
                $foundBuyer = true;
                break;
            }
        }
    }
    if (!$foundBuyer) {
        foreach ($cityStateMapping as $state => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains(strtolower($buyerAddress), $kw)) {
                    $buyerState = ucwords($state);
                    $foundBuyer = true;
                    break 2;
                }
            }
        }
    }
    $buyerStateLower = strtolower(trim($buyerState));
    $buyerStateCode = $stateCodes[$buyerStateLower] ?? $supplierStateCode;

    // Determine Tax Type
    $isInterState = ($supplierStateCode !== $buyerStateCode);
    
    $taxPercent = floatval($invoice->tax_percentage ?? 18);
    $taxAmount = floatval($invoice->tax_amount ?? 0);
    $subtotal = floatval($invoice->subtotal ?? 0);
    $discount = floatval($invoice->discount ?? 0);
    $total = floatval($invoice->total ?? 0);

    if ($isInterState) {
        $igstPercent = $taxPercent;
        $igstAmount = $taxAmount;
        $cgstPercent = 0;
        $cgstAmount = 0;
        $sgstPercent = 0;
        $sgstAmount = 0;
    } else {
        $igstPercent = 0;
        $igstAmount = 0;
        $cgstPercent = $taxPercent / 2;
        $cgstAmount = $taxAmount / 2;
        $sgstPercent = $taxPercent / 2;
        $sgstAmount = $taxAmount / 2;
    }

    // Proportionate discount factor
    $discountRatio = $subtotal > 0 ? ($discount / $subtotal) : 0;
@endphp

<div class="row g-4">
    <!-- Main Invoice Document -->
    <div class="col-12 col-lg-10 mx-auto">
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

        <div class="card shadow-sm border border-light" style="border-radius:12px;">
            <div class="card-body p-4 p-md-5">
                <!-- Status Banner -->
                @if($invoice->status === 'paid')
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 py-2 border-0" style="background-color: #f0fdf4; color: #15803d; border-radius: 8px;">
                        <i class="bi bi-check-circle-fill fs-5"></i> <span>This tax invoice has been fully paid and cleared.</span>
                    </div>
                @elseif($invoice->is_overdue)
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 py-2 border-0" style="background-color: #fef2f2; color: #b91c1c; border-radius: 8px;">
                        <i class="bi bi-exclamation-octagon-fill fs-5"></i> <span>Warning: This invoice is OVERDUE by {{ now()->diffInDays($invoice->due_date) }} days.</span>
                    </div>
                @endif

                <!-- Document Header -->
                <div class="row align-items-start mb-4">
                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-primary text-white rounded p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                <i class="bi bi-lightning-charge-fill fs-5"></i>
                            </div>
                            <span class="fs-4 fw-bold text-dark">{{ $supplierCompany?->name ?? 'WorkeX' }}</span>
                        </div>
                        <div class="text-muted fs-7">
                            <div class="mb-1"><i class="bi bi-geo-alt-fill text-secondary me-1"></i> {{ $supplierAddress }}</div>
                            <div class="mb-1"><i class="bi bi-envelope-fill text-secondary me-1"></i> {{ $supplierCompany?->email ?? 'billing@company.com' }}</div>
                            <div class="mb-1"><i class="bi bi-telephone-fill text-secondary me-1"></i> {{ $supplierCompany?->phone ?? '+91-9999999999' }}</div>
                            <div class="mt-2 text-dark font-monospace fw-semibold">
                                GSTIN: {{ $supplierCompany?->gst ?? '27AAAAA1111A1Z1' }} (Supplier)<br>
                                State: {{ $supplierState }} (Code: {{ $supplierStateCode }})
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 text-md-end">
                        <h2 class="text-uppercase text-primary fw-extrabold mb-1" style="letter-spacing:1px;">Tax Invoice</h2>
                        <div class="fw-bold fs-5 text-dark">Invoice No: {{ $invoice->invoice_number }}</div>
                        <div class="text-muted fs-7 mt-2">Date of Issue: <strong>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '—' }}</strong></div>
                        <div class="text-danger fs-7">Due Date: <strong>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}</strong></div>
                        <div class="mt-2">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-1.5 fs-8 fw-semibold rounded-pill text-uppercase">
                                GST Registered
                            </span>
                        </div>
                    </div>
                </div>

                <hr class="my-4" style="border-color: #cbd5e1;">

                <!-- Client Billing Address -->
                <div class="row mb-4">
                    <div class="col-12 col-md-6">
                        <h6 class="text-uppercase text-muted fs-8 fw-bold mb-2">Billed To (Recipient):</h6>
                        @if($invoice->client)
                            <div class="fw-bold fs-6 text-dark">{{ $invoice->client->company_name }}</div>
                            <div class="text-muted fs-7 mt-1">
                                Attn: {{ $invoice->client->contact_person ?? '—' }}<br>
                                {{ $invoice->client->address }}<br>
                                {{ $invoice->client->city }}, {{ $invoice->client->state }} - {{ $invoice->client->pincode }}<br>
                                Email: {{ $invoice->client->email }}
                            </div>
                            <div class="mt-2 font-monospace fs-7 text-dark fw-semibold">
                                GSTIN: {{ $invoice->client->gst_number ?? 'URP (Unregistered Person)' }}<br>
                                State: {{ $buyerState }} (Code: {{ $buyerStateCode }})
                            </div>
                        @else
                            <div class="text-muted">No Client Attached</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                        <h6 class="text-uppercase text-muted fs-8 fw-bold mb-2">Place of Supply:</h6>
                        <div class="fw-bold text-dark font-monospace fs-6">
                            {{ $buyerState }} (Code: {{ $buyerStateCode }})
                        </div>
                        <div class="text-muted fs-7 mt-1">
                            Supply Type: {{ $isInterState ? 'Inter-State (IGST)' : 'Intra-State (CGST + SGST)' }}
                        </div>
                    </div>
                </div>

                <!-- Items Breakdown Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle fs-7 mb-0">
                        <thead class="table-light">
                            <tr class="align-middle text-center">
                                <th rowspan="2" style="min-width: 200px;" class="text-start">Description of Service/Goods</th>
                                <th rowspan="2" style="width: 90px;">HSN/SAC</th>
                                <th rowspan="2" style="width: 60px;">Qty</th>
                                <th rowspan="2" style="width: 100px;" class="text-end">Unit Rate</th>
                                <th rowspan="2" style="width: 110px;" class="text-end">Taxable Val</th>
                                @if($isInterState)
                                    <th colspan="2" style="width: 130px;">IGST</th>
                                @else
                                    <th colspan="2" style="width: 130px;">CGST</th>
                                    <th colspan="2" style="width: 130px;">SGST</th>
                                @endif
                                <th rowspan="2" style="width: 120px;" class="text-end">Total (INR)</th>
                            </tr>
                            <tr class="text-center">
                                @if($isInterState)
                                    <th style="width: 50px;">Rate</th>
                                    <th style="width: 80px;" class="text-end">Amount</th>
                                @else
                                    <th style="width: 50px;">Rate</th>
                                    <th style="width: 80px;" class="text-end">Amount</th>
                                    <th style="width: 50px;">Rate</th>
                                    <th style="width: 80px;" class="text-end">Amount</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items ?? [] as $item)
                                @php
                                    $qty = floatval($item['qty'] ?? 1);
                                    $price = floatval($item['price'] ?? 0);
                                    $rowSubtotal = $qty * $price;
                                    
                                    // Proportionate discount deduction
                                    $rowDiscount = $rowSubtotal * $discountRatio;
                                    $rowTaxable = $rowSubtotal - $rowDiscount;

                                    if ($isInterState) {
                                        $rowIgstAmount = $rowTaxable * ($taxPercent / 100);
                                        $rowCgstAmount = 0;
                                        $rowSgstAmount = 0;
                                        $rowTotal = $rowTaxable + $rowIgstAmount;
                                    } else {
                                        $rowIgstAmount = 0;
                                        $rowCgstAmount = $rowTaxable * (($taxPercent / 2) / 100);
                                        $rowSgstAmount = $rowTaxable * (($taxPercent / 2) / 100);
                                        $rowTotal = $rowTaxable + $rowCgstAmount + $rowSgstAmount;
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item['name'] ?? '' }}</div>
                                    </td>
                                    <td class="text-center font-monospace">{{ $item['hsn_sac'] ?? '998313' }}</td>
                                    <td class="text-center">{{ $qty }}</td>
                                    <td class="text-end">₹{{ number_format($price, 2) }}</td>
                                    <td class="text-end fw-medium">₹{{ number_format($rowTaxable, 2) }}</td>
                                    @if($isInterState)
                                        <td class="text-center font-monospace">{{ $taxPercent }}%</td>
                                        <td class="text-end">₹{{ number_format($rowIgstAmount, 2) }}</td>
                                    @else
                                        <td class="text-center font-monospace">{{ $taxPercent / 2 }}%</td>
                                        <td class="text-end">₹{{ number_format($rowCgstAmount, 2) }}</td>
                                        <td class="text-center font-monospace">{{ $taxPercent / 2 }}%</td>
                                        <td class="text-end">₹{{ number_format($rowSgstAmount, 2) }}</td>
                                    @endif
                                    <td class="text-end fw-semibold text-dark">₹{{ number_format($rowTotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isInterState ? 8 : 10 }}" class="text-center py-3 text-muted fs-7">No items added.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Calculations -->
                <div class="row justify-content-end mb-4 mt-3">
                    <div class="col-12 col-md-6">
                        <table class="table table-sm table-borderless fs-7 mb-0">
                            <tr>
                                <td class="text-muted">Total Taxable Value (Subtotal):</td>
                                <td class="text-end fw-semibold text-dark">₹{{ number_format($subtotal, 2) }}</td>
                            </tr>
                            @if($discount > 0)
                                <tr>
                                    <td class="text-muted">Less: Total Discount:</td>
                                    <td class="text-end fw-semibold text-danger">- ₹{{ number_format($discount, 2) }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted">Net Taxable Value:</td>
                                    <td class="text-end fw-semibold text-dark">₹{{ number_format($subtotal - $discount, 2) }}</td>
                                </tr>
                            @endif
                            @if($isInterState)
                                <tr>
                                    <td class="text-muted">Integrated Tax (IGST) ({{ $taxPercent }}%):</td>
                                    <td class="text-end fw-semibold text-dark">₹{{ number_format($igstAmount, 2) }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="text-muted">Central Tax (CGST) ({{ $taxPercent / 2 }}%):</td>
                                    <td class="text-end fw-semibold text-dark">₹{{ number_format($cgstAmount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">State Tax (SGST) ({{ $taxPercent / 2 }}%):</td>
                                    <td class="text-end fw-semibold text-dark">₹{{ number_format($sgstAmount, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="border-top border-secondary-subtle">
                                <td class="fw-bold fs-6 pt-2">Grand Total (Post Tax):</td>
                                <td class="text-end fw-bold text-dark fs-6 pt-2">₹{{ number_format($total, 2) }}</td>
                            </tr>
                            <tr class="text-success border-bottom">
                                <td class="fw-bold pt-1">Amount Paid:</td>
                                <td class="text-end fw-bold pt-1">₹{{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
                            </tr>
                            <tr class="text-danger">
                                <td class="fw-bold fs-6 pt-2">Balance Due:</td>
                                <td class="text-end fw-bold fs-5 pt-2">₹{{ number_format($invoice->balance_amount ?? $total, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Detailed GST Breakdown Summary -->
                <div class="card bg-light border-0 mb-4" style="border-radius: 8px;">
                    <div class="card-body p-3">
                        <h6 class="text-uppercase text-muted fs-8 fw-bold mb-2">GST Tax Summary Breakdown:</h6>
                        <table class="table table-sm table-bordered fs-8 mb-0 bg-white">
                            <thead class="table-secondary">
                                <tr class="text-center font-monospace">
                                    <th>HSN/SAC</th>
                                    <th>Taxable Value</th>
                                    @if($isInterState)
                                        <th>IGST Rate</th>
                                        <th>IGST Amount</th>
                                    @else
                                        <th>CGST Rate</th>
                                        <th>CGST Amount</th>
                                        <th>SGST Rate</th>
                                        <th>SGST Amount</th>
                                    @endif
                                    <th>Total Tax</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-center font-monospace">
                                    <td>998313</td>
                                    <td class="text-end">₹{{ number_format($subtotal - $discount, 2) }}</td>
                                    @if($isInterState)
                                        <td>{{ $taxPercent }}%</td>
                                        <td class="text-end">₹{{ number_format($igstAmount, 2) }}</td>
                                    @else
                                        <td>{{ $taxPercent / 2 }}%</td>
                                        <td class="text-end">₹{{ number_format($cgstAmount, 2) }}</td>
                                        <td>{{ $taxPercent / 2 }}%</td>
                                        <td class="text-end">₹{{ number_format($sgstAmount, 2) }}</td>
                                    @endif
                                    <td class="text-end fw-semibold">₹{{ number_format($taxAmount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Terms and Signatory -->
                <div class="row align-items-end mt-5">
                    <div class="col-12 col-md-7 mb-4 mb-md-0">
                        @if($invoice->notes)
                            <h6 class="text-uppercase text-muted fs-8 fw-bold mb-2">Declaration / Bank Details:</h6>
                            <div class="text-muted fs-8 font-monospace" style="white-space: pre-wrap; line-height: 1.5;">{{ $invoice->notes }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-5 text-md-end">
                        <div class="fs-7 text-muted mb-4">For <strong>{{ $supplierCompany?->name ?? 'WorkeX' }}</strong></div>
                        <div class="border-top d-inline-block pt-2 px-4 text-center" style="border-top-style: dashed !important; border-top-color: #64748b !important;">
                            <span class="fs-8 text-secondary font-monospace">Authorized Signatory</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
