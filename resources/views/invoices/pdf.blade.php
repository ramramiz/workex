<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            color: #1e293b; 
            font-size: 10px; 
            line-height: 1.4; 
            margin: 0; 
            padding: 0; 
        }
        .invoice-box { 
            max-width: 800px; 
            margin: auto; 
            padding: 20px; 
        }
        table { 
            width: 100%; 
            line-height: inherit; 
            text-align: left; 
            border-collapse: collapse; 
        }
        table td { 
            padding: 6px; 
            vertical-align: top; 
        }
        .text-right { 
            text-align: right; 
        }
        .text-center { 
            text-align: center; 
        }
        .border-bottom {
            border-bottom: 1px solid #cbd5e1;
        }
        .border-all {
            border: 1px solid #cbd5e1;
        }
        .bg-light { 
            background: #f8fafc; 
        }
        .font-bold {
            font-weight: bold;
        }
        .font-monospace {
            font-family: Courier, monospace;
        }
        .heading-title {
            font-size: 22px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .item-table { 
            margin-top: 20px;
            margin-bottom: 20px; 
        }
        .item-table th { 
            border: 1px solid #94a3b8;
            padding: 6px 4px;
            font-weight: bold;
            background: #f1f5f9;
            text-align: center;
            font-size: 9px;
            color: #1e293b;
        }
        .item-table td { 
            border: 1px solid #cbd5e1;
            padding: 8px 6px;
        }
        .totals-table { 
            width: 280px; 
            margin-left: auto;
            margin-top: 10px; 
        }
        .totals-table td { 
            padding: 4px 6px; 
        }
        .grand-total { 
            font-size: 12px; 
            font-weight: bold; 
            color: #0f172a; 
            border-top: 1px solid #94a3b8; 
            border-bottom: 1px solid #94a3b8;
        }
        .balance-row { 
            font-size: 12px; 
            font-weight: bold; 
            color: #ef4444; 
        }
        .terms-box { 
            margin-top: 30px; 
            border-top: 1px dashed #cbd5e1; 
            padding-top: 10px; 
            page-break-inside: avoid; 
        }
        .terms-title { 
            font-size: 9px; 
            text-transform: uppercase; 
            color: #64748b; 
            font-weight: bold; 
            margin-bottom: 5px; 
        }
        .terms-content { 
            font-size: 9px; 
            color: #64748b; 
            white-space: pre-wrap; 
            font-family: Courier, monospace;
        }
        .signatory-box {
            text-align: right;
            margin-top: 30px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
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

        $supplierCompany = $invoice->company ?? null;
        
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
            $igstAmount = $taxAmount;
            $cgstAmount = 0;
            $sgstAmount = 0;
        } else {
            $igstAmount = 0;
            $cgstAmount = $taxAmount / 2;
            $sgstAmount = $taxAmount / 2;
        }

        // Proportionate discount factor
        $discountRatio = $subtotal > 0 ? ($discount / $subtotal) : 0;
    @endphp

    <div class="invoice-box">
        <!-- Header -->
        <table style="margin-bottom: 15px;">
            <tr>
                <td style="width: 55%; padding: 0;">
                    <span style="font-size: 18px; font-weight: bold; color: #1e1b4b; text-transform: uppercase;">{{ $supplierCompany?->name ?? 'Techsoul' }}</span><br>
                    <span style="color: #475569; font-size: 9.5px; line-height: 1.3; display: block; margin-top: 3px;">
                        {{ $supplierAddress }}<br>
                        Email: {{ $supplierCompany?->email ?? 'billing@company.com' }} | Phone: {{ $supplierCompany?->phone ?? '+91-9999999999' }}
                    </span>
                    <span style="font-size: 9.5px; font-weight: bold; color: #1e293b; font-family: Courier, monospace; display: block; margin-top: 6px;">
                        GSTIN: {{ $supplierCompany?->gst ?? '32ADNPO8730B1ZO' }}<br>
                        State: {{ $supplierState }} (Code: {{ $supplierStateCode }})
                    </span>
                </td>
                <td class="text-right" style="width: 45%; padding: 0; vertical-align: middle;">
                    <span class="heading-title">Tax Invoice</span><br>
                    <strong style="font-size: 11px; color: #1e293b; font-family: Courier, monospace;">Invoice No: {{ $invoice->invoice_number }}</strong><br>
                    <span style="color: #475569; font-size: 9.5px; display: block; margin-top: 3px;">
                        Date of Issue: <strong>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '—' }}</strong><br>
                        <span style="color:#ef4444;">Due Date: <strong>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}</strong></span>
                    </span>
                </td>
            </tr>
        </table>

        <hr style="border: 0; border-top: 1.5px solid #4f46e5; margin-bottom: 15px;">

        <!-- Addresses -->
        <table style="margin-bottom: 20px;">
            <tr>
                <td style="width: 55%; padding: 0;">
                    <strong style="color: #475569; font-size: 9px; text-transform: uppercase; display: block; margin-bottom: 4px;">Billed To (Recipient):</strong>
                    @if($invoice->client)
                        <strong style="font-size: 11px; color: #0f172a;">{{ $invoice->client->company_name }}</strong><br>
                        <span style="color: #475569; font-size: 9.5px; line-height: 1.3;">
                            Attn: {{ $invoice->client->contact_person ?? '—' }}<br>
                            {{ $invoice->client->address }}<br>
                            {{ $invoice->client->city }}, {{ $invoice->client->state }} - {{ $invoice->client->pincode }}<br>
                            Email: {{ $invoice->client->email }}
                        </span>
                        <span style="font-size: 9.5px; font-weight: bold; color: #1e293b; font-family: Courier, monospace; display: block; margin-top: 5px;">
                            GSTIN: {{ $invoice->client->gst_number ?? 'URP (Unregistered Person)' }}<br>
                            State: {{ $buyerState }} (Code: {{ $buyerStateCode }})
                        </span>
                    @else
                        <span style="color: #94a3b8;">No Client Linked</span>
                    @endif
                </td>
                <td class="text-right" style="width: 45%; padding: 0;">
                    <strong style="color: #475569; font-size: 9px; text-transform: uppercase; display: block; margin-bottom: 4px;">Place of Supply:</strong>
                    <strong style="font-size: 11px; color: #1e293b; font-family: Courier, monospace;">{{ $buyerState }} (Code: {{ $buyerStateCode }})</strong><br>
                    <span style="color: #475569; font-size: 9.5px;">
                        Supply Type: {{ $isInterState ? 'Inter-State (IGST)' : 'Intra-State (CGST + SGST)' }}
                    </span>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="item-table">
            <thead>
                <tr>
                    <th rowspan="2" style="text-align: left; width: 35%;">Service/Goods Description</th>
                    <th rowspan="2" style="width: 10%;">SAC</th>
                    <th rowspan="2" style="width: 6%;">Qty</th>
                    <th rowspan="2" style="width: 11%; text-align: right;">Rate</th>
                    <th rowspan="2" style="width: 12%; text-align: right;">Taxable Val</th>
                    @if($isInterState)
                        <th colspan="2" style="width: 14%;">IGST</th>
                    @else
                        <th colspan="2" style="width: 13%;">CGST</th>
                        <th colspan="2" style="width: 13%;">SGST</th>
                    @endif
                    <th rowspan="2" style="width: 12%; text-align: right;">Total</th>
                </tr>
                <tr>
                    @if($isInterState)
                        <th style="width: 5%; font-size: 8px;">Rate</th>
                        <th style="width: 9%; font-size: 8px; text-align: right;">Amount</th>
                    @else
                        <th style="width: 4%; font-size: 8px;">Rate</th>
                        <th style="width: 9%; font-size: 8px; text-align: right;">Amount</th>
                        <th style="width: 4%; font-size: 8px;">Rate</th>
                        <th style="width: 9%; font-size: 8px; text-align: right;">Amount</th>
                    @endif
                </tr>
            </thead>
            <tbody>
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
                    @endphp
                    <tr>
                        <td style="text-align: left;"><strong>{{ $item['name'] ?? '' }}</strong></td>
                        <td class="text-center font-monospace">{{ $item['hsn_sac'] ?? '998313' }}</td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-right font-monospace">Rs. {{ number_format($price, 2) }}</td>
                        <td class="text-right font-monospace">Rs. {{ number_format($rowTaxable, 2) }}</td>
                        @if($isInterState)
                            <td class="text-center font-monospace">{{ $taxPercent }}%</td>
                            <td class="text-right font-monospace">Rs. {{ number_format($rowIgstAmount, 2) }}</td>
                        @else
                            <td class="text-center font-monospace">{{ $taxPercent / 2 }}%</td>
                            <td class="text-right font-monospace">Rs. {{ number_format($rowCgstAmount, 2) }}</td>
                            <td class="text-center font-monospace">{{ $taxPercent / 2 }}%</td>
                            <td class="text-right font-monospace">Rs. {{ number_format($rowSgstAmount, 2) }}</td>
                        @endif
                        <td class="text-right font-bold font-monospace">Rs. {{ number_format($rowTotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isInterState ? 8 : 10 }}" style="text-align: center; color: #94a3b8;">No items billed.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totals & GST Summary Breakdown -->
        <table style="margin-top: 10px;">
            <tr>
                <td style="width: 55%; padding: 0;">
                    <!-- GST Tax Summary Breakdown -->
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; padding: 10px; border-radius: 4px; margin-right: 15px;">
                        <strong style="font-size: 9px; text-transform: uppercase; color: #475569; display: block; margin-bottom: 6px;">GST Tax Summary Breakdown</strong>
                        <table style="font-size: 8.5px; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #e2e8f0; font-family: Courier, monospace;">
                                    <th style="border: 1px solid #cbd5e1; padding: 3px; text-align: center;">SAC</th>
                                    <th style="border: 1px solid #cbd5e1; padding: 3px; text-align: right;">Taxable Val</th>
                                    @if($isInterState)
                                        <th style="border: 1px solid #cbd5e1; padding: 3px; text-align: center;">IGST</th>
                                        <th style="border: 1px solid #cbd5e1; padding: 3px; text-align: right;">Amount</th>
                                    @else
                                        <th style="border: 1px solid #cbd5e1; padding: 3px; text-align: center;">CGST</th>
                                        <th style="border: 1px solid #cbd5e1; padding: 3px; text-align: right;">Amount</th>
                                        <th style="border: 1px solid #cbd5e1; padding: 3px; text-align: center;">SGST</th>
                                        <th style="border: 1px solid #cbd5e1; padding: 3px; text-align: right;">Amount</th>
                                    @endif
                                    <th style="border: 1px solid #cbd5e1; padding: 3px; text-align: right;">Total Tax</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="font-family: Courier, monospace;">
                                    <td style="border: 1px solid #cbd5e1; padding: 3px; text-align: center;">998313</td>
                                    <td style="border: 1px solid #cbd5e1; padding: 3px; text-align: right;">Rs. {{ number_format($subtotal - $discount, 2) }}</td>
                                    @if($isInterState)
                                        <td style="border: 1px solid #cbd5e1; padding: 3px; text-align: center;">{{ $taxPercent }}%</td>
                                        <td style="border: 1px solid #cbd5e1; padding: 3px; text-align: right;">Rs. {{ number_format($igstAmount, 2) }}</td>
                                    @else
                                        <td style="border: 1px solid #cbd5e1; padding: 3px; text-align: center;">{{ $taxPercent / 2 }}%</td>
                                        <td style="border: 1px solid #cbd5e1; padding: 3px; text-align: right;">Rs. {{ number_format($cgstAmount, 2) }}</td>
                                        <td style="border: 1px solid #cbd5e1; padding: 3px; text-align: center;">{{ $taxPercent / 2 }}%</td>
                                        <td style="border: 1px solid #cbd5e1; padding: 3px; text-align: right;">Rs. {{ number_format($sgstAmount, 2) }}</td>
                                    @endif
                                    <td style="border: 1px solid #cbd5e1; padding: 3px; text-align: right; font-weight: bold;">Rs. {{ number_format($taxAmount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
                <td style="width: 45%; padding: 0;">
                    <table class="totals-table" style="width: 100%;">
                        <tr>
                            <td style="color:#64748b; font-size: 9.5px;">Subtotal (Taxable):</td>
                            <td class="text-right font-monospace" style="font-size: 9.5px;">Rs. {{ number_format($subtotal, 2) }}</td>
                        </tr>
                        @if($discount > 0)
                            <tr>
                                <td style="color:#64748b; font-size: 9.5px;">Discount:</td>
                                <td class="text-right font-monospace" style="color: #ef4444; font-size: 9.5px;">- Rs. {{ number_format($discount, 2) }}</td>
                            </tr>
                        @endif
                        @if($isInterState)
                            <tr>
                                <td style="color:#64748b; font-size: 9.5px;">IGST ({{ $taxPercent }}%):</td>
                                <td class="text-right font-monospace" style="font-size: 9.5px;">Rs. {{ number_format($igstAmount, 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td style="color:#64748b; font-size: 9.5px;">CGST ({{ $taxPercent / 2 }}%):</td>
                                <td class="text-right font-monospace" style="font-size: 9.5px;">Rs. {{ number_format($cgstAmount, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="color:#64748b; font-size: 9.5px;">SGST ({{ $taxPercent / 2 }}%):</td>
                                <td class="text-right font-monospace" style="font-size: 9.5px;">Rs. {{ number_format($sgstAmount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="grand-total">
                            <td style="padding-top: 6px; padding-bottom: 6px;">Grand Total:</td>
                            <td class="text-right font-monospace" style="padding-top: 6px; padding-bottom: 6px;">Rs. {{ number_format($total, 2) }}</td>
                        </tr>
                        <tr style="color: #10b981; font-size: 9.5px;">
                            <td>Amount Paid:</td>
                            <td class="text-right font-monospace" style="font-size: 9.5px;">Rs. {{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr class="balance-row">
                            <td style="padding-top: 6px;">Balance Due:</td>
                            <td class="text-right font-monospace" style="padding-top: 6px;">Rs. {{ number_format($invoice->balance_amount ?? $total, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Declaration & Signatory -->
        <table style="margin-top: 30px; page-break-inside: avoid;">
            <tr>
                <td style="width: 60%; padding: 0;">
                    @if($invoice->notes)
                        <div class="terms-title">Declaration / Bank details:</div>
                        <div class="terms-content">{{ $invoice->notes }}</div>
                    @endif
                </td>
                <td class="text-right" style="width: 40%; padding: 0; vertical-align: bottom;">
                    <div style="font-size: 9.5px; color: #64748b; margin-bottom: 30px;">For <strong>{{ $supplierCompany?->name ?? 'Techsoul' }}</strong></div>
                    <div style="border-top: 1px dashed #64748b; display: inline-block; width: 150px; text-align: center; padding-top: 4px;">
                        <span style="font-size: 9px; color: #64748b; font-family: Courier, monospace;">Authorized Signatory</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
