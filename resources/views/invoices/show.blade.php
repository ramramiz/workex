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
    
    $supplierAddress = $supplierCompany?->address ?? 'OPP. TRUST HOSPITAL ROOM NO: 20/792, RM-VENTURES, RANDATHANI.PO, MALAPPURAM-KERALA Pin : 676510';
    $supplierState = 'Kerala';
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
    $supplierStateCode = $stateCodes[$supplierStateLower] ?? '32';

    $buyerAddress = ($invoice->client?->address ?? '') . ' ' . ($invoice->client?->city ?? '') . ' ' . ($invoice->client?->state ?? '');
    $buyerState = $supplierState;
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

    $isInterState = ($supplierStateCode !== $buyerStateCode);
    
    $taxPercent = floatval($invoice->tax_percentage ?? 18);
    $taxAmount = floatval($invoice->tax_amount ?? 0);
    $subtotal = floatval($invoice->subtotal ?? 0);
    $discount = floatval($invoice->discount ?? 0);
    $total = floatval($invoice->total ?? 0);

    if ($isInterState) {
        $igstAmount = $taxAmount;
        $cgstAmount = 0;
        $sgstAmount = 0;
    } else {
        $igstAmount = 0;
        $cgstAmount = $taxAmount / 2;
        $sgstAmount = $taxAmount / 2;
    }

    $discountRatio = $subtotal > 0 ? ($discount / $subtotal) : 0;

    if (!function_exists('numberToWords')) {
        function numberToWords($num) {
            $ones = array(
                0 => "ZERO", 1 => "ONE", 2 => "TWO", 3 => "THREE", 4 => "FOUR", 
                5 => "FIVE", 6 => "SIX", 7 => "SEVEN", 8 => "EIGHT", 9 => "NINE", 
                10 => "TEN", 11 => "ELEVEN", 12 => "TWELVE", 13 => "THIRTEEN", 
                14 => "FOURTEEN", 15 => "FIFTEEN", 16 => "SIXTEEN", 17 => "SEVENTEEN", 
                18 => "EIGHTEEN", 19 => "NINETEEN"
            );
            $tens = array(
                0 => "ZERO", 1 => "TEN", 2 => "TWENTY", 3 => "THIRTY", 4 => "FORTY", 
                5 => "FIFTY", 6 => "SIXTY", 7 => "SEVENTY", 8 => "EIGHTY", 9 => "NINETY"
            );
            
            $num = str_replace([',', ' '], '', $num);
            $num = (float)$num;
            $num = round($num, 2);
            
            $num_arr = explode(".", sprintf("%.2f", $num));
            $wholenum = (int)$num_arr[0];
            $decnum = (int)$num_arr[1];
            
            $result = "";
            
            if ($wholenum == 0) {
                $result = "ZERO RUPEES";
            } else {
                $words = array();
                
                if ($wholenum >= 10000000) {
                    $crore = (int)($wholenum / 10000000);
                    $wholenum = $wholenum % 10000000;
                    $words[] = convertGroup($crore, $ones, $tens) . " CRORE";
                }
                
                if ($wholenum >= 100000) {
                    $lakh = (int)($wholenum / 100000);
                    $wholenum = $wholenum % 100000;
                    $words[] = convertGroup($lakh, $ones, $tens) . " LAKH";
                }
                
                if ($wholenum >= 1000) {
                    $thousand = (int)($wholenum / 1000);
                    $wholenum = $wholenum % 1000;
                    $words[] = convertGroup($thousand, $ones, $tens) . " THOUSAND";
                }
                
                if ($wholenum >= 100) {
                    $hundred = (int)($wholenum / 100);
                    $wholenum = $wholenum % 100;
                    $words[] = convertGroup($hundred, $ones, $tens) . " HUNDRED";
                }
                
                if ($wholenum > 0) {
                    $words[] = convertGroup($wholenum, $ones, $tens);
                }
                
                $result = implode(" ", $words) . " RUPEES";
            }
            
            if ($decnum > 0) {
                $result .= " AND " . convertGroup($decnum, $ones, $tens) . " PAISE";
            }
            
            return $result . " ONLY";
        }

        function convertGroup($n, $ones, $tens) {
            $n = (int)$n;
            if ($n < 20) {
                return $ones[$n];
            }
            $res = $tens[(int)($n / 10)];
            if ($n % 10 > 0) {
                $res .= " " . $ones[$n % 10];
            }
            return $res;
        }
    }
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
            <div class="card-body p-4 p-md-5 text-dark" style="font-size: 13px;">
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

                <!-- Redesigned Document Header -->
                <div class="row border-bottom border-2 pb-3 mb-4" style="border-color: #0E4B8B !important;">
                    <div class="col-12 col-md-7 mb-3 mb-md-0">
                        <div class="fs-2 fw-extrabold text-uppercase mb-0" style="color: #0E4B8B; font-family: sans-serif; letter-spacing: 0.5px; line-height: 1;">{{ $supplierCompany?->name ?? 'TECHSOUL' }}</div>
                        <div class="fw-bold mb-3" style="color: #0E4B8B; font-size: 9px; letter-spacing: 0.2px;">COMPUTERS | LAPTOPS | PRINTERS | SECURITY SYSTEMS</div>
                        
                        <div class="text-dark" style="font-size: 12px; line-height: 1.45;">
                            <strong>GSTIN : {{ $supplierCompany?->gst ?? '32ADNPO8730B1ZO' }}</strong><br>
                            {{ strtoupper($supplierAddress) }}<br>
                            MALAPPURAM-KERALA Pin : 676510 Tel: {{ $supplierCompany?->phone ?? '+918891989842' }}<br>
                            Email: {{ $supplierCompany?->email ?? 'service@teamtechsoul.com' }}<br>
                            Web: www.teamtechsoul.com
                        </div>
                    </div>
                    <div class="col-12 col-md-5 text-md-start ps-md-4">
                        <h3 class="text-uppercase fw-bold mb-3 text-md-end" style="color: #0E4B8B; letter-spacing: 0.5px;">Retail Invoice</h3>
                        
                        <div class="row justify-content-md-end mb-3">
                            <div class="col-auto">
                                <span class="d-block text-muted" style="font-size: 11px;">Invoice No</span>
                                <strong class="fs-6 text-danger">{{ $invoice->invoice_number }}</strong>
                            </div>
                            <div class="col-auto ms-4 text-md-end">
                                <span class="d-block text-muted" style="font-size: 11px;">Date</span>
                                <strong class="fs-6 text-danger">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : '—' }}</strong>
                            </div>
                        </div>

                        <div class="border-top pt-3 border-secondary-subtle">
                            <span style="font-size: 11px; color: #666; font-weight: bold; display: block; text-transform: uppercase;">Customer Details</span>
                            <strong class="fs-6 text-dark display-block mt-1">{{ strtoupper($invoice->client?->company_name ?? '—') }}</strong>
                            <span class="fs-7 text-dark display-block">GSTIN : {{ $invoice->client?->gst_number ?? '—' }}</span>
                        </div>

                        <div class="border-top mt-3 pt-3 border-secondary-subtle">
                            <span style="font-size: 11px; color: #666; font-weight: bold; display: block; text-transform: uppercase;">Payment Method</span>
                            <strong class="fs-6 text-dark display-block mt-1">{{ strtoupper($invoice->payment_method ?? 'CREDIT') }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Items Breakdown Table -->
                <div class="table-responsive mb-0">
                    <table class="table table-bordered border-dark align-middle text-center fs-7 mb-0">
                        <thead class="bg-light">
                            <tr class="align-middle">
                                <th rowspan="2" style="width: 4%;" class="border-dark">SL. NO</th>
                                <th rowspan="2" style="width: 28%;" class="border-dark text-start">Description of Goods</th>
                                <th rowspan="2" style="width: 9%;" class="border-dark">HSN CODE (GST)</th>
                                <th rowspan="2" style="width: 4%;" class="border-dark">Qty</th>
                                <th rowspan="2" style="width: 8%;" class="border-dark text-end">Unit Price</th>
                                <th rowspan="2" style="width: 9%;" class="border-dark text-end">Amount</th>
                                <th rowspan="2" style="width: 9%;" class="border-dark text-end">Taxable Value</th>
                                @if($isInterState)
                                    <th colspan="2" class="border-dark">IGST</th>
                                @else
                                    <th colspan="2" class="border-dark">SGST</th>
                                    <th colspan="2" class="border-dark">CGST</th>
                                @endif
                                <th rowspan="2" style="width: 9%;" class="border-dark text-end">Grand Total</th>
                            </tr>
                            <tr>
                                @if($isInterState)
                                    <th class="border-dark" style="width: 4%;">Rate</th>
                                    <th class="border-dark text-end" style="width: 7%;">Amount</th>
                                @else
                                    <th class="border-dark" style="width: 3%;">Rate</th>
                                    <th class="border-dark text-end" style="width: 6%;">Amount</th>
                                    <th class="border-dark" style="width: 3%;">Rate</th>
                                    <th class="border-dark text-end" style="width: 6%;">Amount</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $slNo = 1; 
                                $totalQty = 0;
                            @endphp
                            @forelse($invoice->items ?? [] as $item)
                                @php
                                    $qty = floatval($item['qty'] ?? 1);
                                    $price = floatval($item['price'] ?? 0);
                                    $rowSubtotal = $qty * $price;
                                    
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
                                    $totalQty += $qty;
                                @endphp
                                <tr>
                                    <td class="border-dark">{{ $slNo++ }}</td>
                                    <td class="border-dark text-start fw-semibold text-uppercase" style="font-size: 12px;">{{ $item['name'] ?? '' }}</td>
                                    <td class="border-dark">{{ $item['hsn_sac'] ?? '998313' }}</td>
                                    <td class="border-dark">{{ $qty }}</td>
                                    <td class="border-dark text-end">₹{{ number_format($price, 2) }}</td>
                                    <td class="border-dark text-end">₹{{ number_format($rowSubtotal, 2) }}</td>
                                    <td class="border-dark text-end">₹{{ number_format($rowTaxable, 2) }}</td>
                                    @if($isInterState)
                                        <td class="border-dark">{{ $taxPercent }}%</td>
                                        <td class="border-dark text-end">₹{{ number_format($rowIgstAmount, 2) }}</td>
                                    @else
                                        <td class="border-dark">{{ $taxPercent / 2 }}%</td>
                                        <td class="border-dark text-end">₹{{ number_format($rowSgstAmount, 2) }}</td>
                                        <td class="border-dark">{{ $taxPercent / 2 }}%</td>
                                        <td class="border-dark text-end">₹{{ number_format($rowCgstAmount, 2) }}</td>
                                    @endif
                                    <td class="border-dark text-end fw-bold">₹{{ number_format($rowTotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isInterState ? 10 : 12 }}" class="text-center py-3 text-muted border-dark">No items added.</td>
                                </tr>
                            @endforelse

                            <!-- Filler spacer rows to match image styling of extending vertical lines -->
                            @php
                                $itemsCount = count($invoice->items ?? []);
                                $minRows = 8; // On-screen can be slightly fewer than PDF to save space
                            @endphp
                            @for($i = $itemsCount; $i < $minRows; $i++)
                                <tr>
                                    <td class="border-dark" style="height: 25px;">&nbsp;</td>
                                    <td class="border-dark">&nbsp;</td>
                                    <td class="border-dark">&nbsp;</td>
                                    <td class="border-dark">&nbsp;</td>
                                    <td class="border-dark">&nbsp;</td>
                                    <td class="border-dark">&nbsp;</td>
                                    <td class="border-dark">&nbsp;</td>
                                    @if($isInterState)
                                        <td class="border-dark">&nbsp;</td>
                                        <td class="border-dark">&nbsp;</td>
                                    @else
                                        <td class="border-dark">&nbsp;</td>
                                        <td class="border-dark">&nbsp;</td>
                                        <td class="border-dark">&nbsp;</td>
                                        <td class="border-dark">&nbsp;</td>
                                    @endif
                                    <td class="border-dark">&nbsp;</td>
                                </tr>
                            @endfor

                            <!-- TOTAL ROW -->
                            <tr class="fw-bold bg-light">
                                <td class="border-dark text-end" colspan="3">TOTAL</td>
                                <td class="border-dark text-center">{{ $totalQty }}</td>
                                <td class="border-dark">&nbsp;</td>
                                <td class="border-dark">&nbsp;</td>
                                <td class="border-dark text-end">₹{{ number_format($subtotal - $discount, 2) }}</td>
                                @if($isInterState)
                                    <td class="border-dark">&nbsp;</td>
                                    <td class="border-dark text-end">₹{{ number_format($igstAmount, 2) }}</td>
                                @else
                                    <td class="border-dark">&nbsp;</td>
                                    <td class="border-dark text-end">₹{{ number_format($sgstAmount, 2) }}</td>
                                    <td class="border-dark">&nbsp;</td>
                                    <td class="border-dark text-end">₹{{ number_format($cgstAmount, 2) }}</td>
                                @endif
                                <td class="border-dark text-end">₹{{ number_format($total, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Bottom Calculations Grid -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered border-dark fs-7 mb-0" style="margin-top: -1px;">
                        <tbody>
                            <tr>
                                <td style="width: 22%; font-weight: bold;" class="border-dark bg-light">SALES PERSON</td>
                                <td style="width: 43%;" class="border-dark fw-semibold">{{ strtoupper($invoice->sales_person ?? 'AKILESH KP') }}</td>
                                <td style="width: 20%; font-weight: bold;" class="border-dark text-center bg-light" rowspan="2">ROUND OFF</td>
                                <td style="width: 15%; font-weight: bold; text-align: right;" class="border-dark" rowspan="2">
                                    @php
                                        $grandTotal = floatval($invoice->total);
                                        $roundedTotal = round($grandTotal);
                                        $roundOff = $roundedTotal - $grandTotal;
                                    @endphp
                                    ₹{{ number_format($roundOff, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;" class="border-dark bg-light">BILL GENERATED/ACCOUNTANT</td>
                                <td class="border-dark fw-semibold">{{ strtoupper($invoice->createdBy?->name ?? 'RAMIZ') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;" class="border-dark bg-light">BANK DETAILS</td>
                                <td class="border-dark font-monospace text-uppercase" style="font-size: 11px;">{{ $invoice->bank_details ?? 'BANK : FEDERAL BANK BRANCH : PUTHANATHANI ACCOUNT NUMBER : 15430200007260 IFSC : FDRL0001543' }}</td>
                                <td style="font-weight: bold;" class="border-dark text-center bg-light fs-6">GRAND TOTAL</td>
                                <td style="font-weight: bold; text-align: right;" class="border-dark fs-5 text-danger">₹{{ number_format($roundedTotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;" class="border-dark bg-light">GRAND TOTAL IN WORDS</td>
                                <td class="border-dark fw-bold text-uppercase fs-7 italic" colspan="3">
                                    {{ numberToWords($roundedTotal) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Terms and conditions -->
                <div class="mt-4 border p-3 rounded bg-light border-secondary-subtle" style="font-size: 11px; line-height: 1.55;">
                    <strong class="text-uppercase text-secondary">Terms and Conditions :</strong>
                    <ol class="ps-3 mb-0 mt-1">
                        <li>There will be no warranty or replacement for physical or external damages like:- lightning, mishandling, electric short circuit, warranty seal broken, cover broken or damages caused by courier service, or Without proper Invoice.</li>
                        <li>After the payment due date, fine at 24% per month will be charged on the amount overdue.</li>
                        <li>RS 500 will be charged per cheque, if it bounced.</li>
                        <li>The cheque has to be given within 5 days of purchase. If the cheque is not given, the account will be blocked by the account section.</li>
                        <li>Items sold will not be taken back or exchanged.</li>
                        <li>It is the responsibility of the customer to check whether the items are damaged or not.</li>
                        <li>Only the warranty as per manufactures warranty policy will be applicable for the items sold.</li>
                        <li>There is no guarantee for Data.</li>
                    </ol>
                </div>

                <!-- Footer signatures -->
                <div class="row align-items-end mt-5 pt-3">
                    <div class="col-12 col-md-7 text-muted" style="font-size: 12px;">
                        Certified that all the particulars shown in the above invoice are true and<br>
                        correct and Recived the item(s) in Good condition
                    </div>
                    <div class="col-12 col-md-5 text-md-end text-muted" style="font-size: 12px;">
                        This is a system generated invoice<br>
                        Hence, no signature is required.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
