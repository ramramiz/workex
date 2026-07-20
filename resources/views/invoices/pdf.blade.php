<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Retail Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15px 20px;
        }
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            color: #000; 
            font-size: 8.5px; 
            line-height: 1.3; 
            margin: 0; 
            padding: 0; 
        }
        .invoice-box { 
            max-width: 800px; 
            margin: auto; 
            padding: 0px; 
        }
        table { 
            width: 100%; 
            line-height: inherit; 
            text-align: left; 
            border-collapse: collapse; 
        }
        table td { 
            padding: 3px; 
            vertical-align: top; 
        }
        .text-right { 
            text-align: right; 
        }
        .text-center { 
            text-align: center; 
        }
        .font-bold {
            font-weight: bold;
        }
        .item-table { 
            margin-top: 15px;
            margin-bottom: 0px; 
            border: 1px solid #000;
        }
        .item-table th { 
            border: 1px solid #000;
            padding: 5px 3px;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
            color: #000;
        }
        .item-table td { 
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            padding: 5px 4px;
        }
        .terms-section {
            margin-top: 15px;
            font-size: 7.5px;
            line-height: 1.4;
        }
        .certified-text {
            width: 55%;
            float: left;
            font-size: 8px;
        }
        .generated-text {
            width: 40%;
            float: right;
            text-align: right;
            font-size: 8px;
        }
        .clear {
            clear: both;
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

    <div class="invoice-box">
        <!-- Header -->
        <table style="width: 100%; border-bottom: 1.5px solid #0E4B8B; padding-bottom: 5px; margin-bottom: 10px;">
            <tr>
                <td style="width: 55%; padding: 0; vertical-align: top;">
                    <div style="font-size: 26px; font-weight: bold; color: #0E4B8B; font-family: Arial, sans-serif; letter-spacing: 0.5px;">{{ strtoupper($supplierCompany?->name ?? 'TECHSOUL') }}</div>
                    <div style="font-size: 7px; font-weight: bold; color: #0E4B8B; margin-top: -3px; letter-spacing: 0.2px;">COMPUTERS | LAPTOPS | PRINTERS | SECURITY SYSTEMS</div>
                    
                    <div style="font-size: 8px; line-height: 1.25; margin-top: 8px; color: #000;">
                        <strong>GSTIN : {{ $supplierCompany?->gst ?? '32ADNPO8730B1ZO' }}</strong><br>
                        {{ strtoupper($supplierAddress) }}<br>
                        MALAPPURAM-KERALA Pin : 676510 Tel: {{ $supplierCompany?->phone ?? '+918891989842' }}<br>
                        email: {{ $supplierCompany?->email ?? 'service@teamtechsoul.com' }}<br>
                        www.teamtechsoul.com
                    </div>
                </td>
                <td style="width: 45%; padding: 0 0 0 15px; vertical-align: top; text-align: left;">
                    <div style="font-size: 16px; font-weight: bold; color: #0E4B8B; letter-spacing: 0.5px; margin-bottom: 5px; text-align: right;">RETAIL INVOICE</div>
                    <table style="width: 100%; margin-bottom: 5px; font-size: 8.5px;">
                        <tr>
                            <td style="padding: 1px 0; color: #000; text-align: left;">Invoice No</td>
                            <td style="font-weight: bold; color: #D32F2F; padding: 1px 0; text-align: right; width: 100px;">{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 1px 0; color: #000; text-align: left;">Date</td>
                            <td style="font-weight: bold; color: #D32F2F; padding: 1px 0; text-align: right;">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : '—' }}</td>
                        </tr>
                    </table>
                    
                    <div style="border-top: 1px solid #ccc; margin-top: 4px; padding-top: 4px; text-align: left;">
                        <span style="font-size: 7.5px; color: #555; font-weight: bold; display: block; text-transform: uppercase;">Customer Details</span>
                        <strong style="font-size: 9px; color: #000; display: block; margin-top: 2px;">{{ strtoupper($invoice->client?->company_name ?? '—') }}</strong>
                        <span style="font-size: 8.5px; color: #000; display: block;">GSTIN : {{ $invoice->client?->gst_number ?? '' }}</span>
                    </div>
                    
                    <div style="border-top: 1px solid #ccc; margin-top: 4px; padding-top: 4px; text-align: left;">
                        <span style="font-size: 7.5px; color: #555; font-weight: bold; display: block; text-transform: uppercase;">Payment Method</span>
                        <strong style="font-size: 9px; color: #000; display: block; margin-top: 2px;">{{ strtoupper($invoice->payment_method ?? 'CREDIT') }}</strong>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="item-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 4%; border: 1px solid #000;">SL. NO</th>
                    <th rowspan="2" style="width: 28%; border: 1px solid #000; text-align: left;">Description of Goods</th>
                    <th rowspan="2" style="width: 9%; border: 1px solid #000;">HSN CODE (GST)</th>
                    <th rowspan="2" style="width: 4%; border: 1px solid #000;">Qty</th>
                    <th rowspan="2" style="width: 8%; border: 1px solid #000; text-align: right;">Unit Price</th>
                    <th rowspan="2" style="width: 9%; border: 1px solid #000; text-align: right;">Amount</th>
                    <th rowspan="2" style="width: 9%; border: 1px solid #000; text-align: right;">Taxable Value</th>
                    @if($isInterState)
                        <th colspan="2" style="border: 1px solid #000;">IGST</th>
                    @else
                        <th colspan="2" style="border: 1px solid #000;">SGST</th>
                        <th colspan="2" style="border: 1px solid #000;">CGST</th>
                    @endif
                    <th rowspan="2" style="width: 9%; border: 1px solid #000; text-align: right;">Grand Total</th>
                </tr>
                <tr>
                    @if($isInterState)
                        <th style="width: 4%; border: 1px solid #000;">Rate</th>
                        <th style="width: 7%; border: 1px solid #000; text-align: right;">Amount</th>
                    @else
                        <th style="width: 3%; border: 1px solid #000;">Rate</th>
                        <th style="width: 6%; border: 1px solid #000; text-align: right;">Amount</th>
                        <th style="width: 3%; border: 1px solid #000;">Rate</th>
                        <th style="width: 6%; border: 1px solid #000; text-align: right;">Amount</th>
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
                        <td class="text-center" style="vertical-align: middle;">{{ $slNo++ }}</td>
                        <td style="text-align: left; font-size: 8px;">{{ strtoupper($item['name'] ?? '') }}</td>
                        <td class="text-center" style="vertical-align: middle;">{{ $item['hsn_sac'] ?? '998313' }}</td>
                        <td class="text-center" style="vertical-align: middle;">{{ $qty }}</td>
                        <td class="text-right" style="vertical-align: middle;">{{ number_format($price, 2) }}</td>
                        <td class="text-right" style="vertical-align: middle;">{{ number_format($rowSubtotal, 2) }}</td>
                        <td class="text-right" style="vertical-align: middle;">{{ number_format($rowTaxable, 2) }}</td>
                        @if($isInterState)
                            <td class="text-center" style="vertical-align: middle;">{{ $taxPercent }}</td>
                            <td class="text-right" style="vertical-align: middle;">{{ number_format($rowIgstAmount, 2) }}</td>
                        @else
                            <td class="text-center" style="vertical-align: middle;">{{ $taxPercent / 2 }}</td>
                            <td class="text-right" style="vertical-align: middle;">{{ number_format($rowSgstAmount, 2) }}</td>
                            <td class="text-center" style="vertical-align: middle;">{{ $taxPercent / 2 }}</td>
                            <td class="text-right" style="vertical-align: middle;">{{ number_format($rowCgstAmount, 2) }}</td>
                        @endif
                        <td class="text-right font-bold" style="vertical-align: middle;">{{ number_format($rowTotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isInterState ? 10 : 12 }}" class="text-center" style="color: #999;">No items billed.</td>
                    </tr>
                @endforelse

                <!-- Filler spacer rows to match image styling of extending vertical lines -->
                @php
                    $itemsCount = count($invoice->items ?? []);
                    $minRows = 8;
                @endphp
                @for($i = $itemsCount; $i < $minRows; $i++)
                    <tr>
                        <td style="height: 18px;">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        @if($isInterState)
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        @else
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        @endif
                        <td>&nbsp;</td>
                    </tr>
                @endfor

                <!-- TOTAL ROW -->
                <tr style="font-weight: bold; background: #fff; border-top: 1px solid #000;">
                    <td style="border: 1px solid #000; padding: 5px; text-align: right;" colspan="3">TOTAL</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $totalQty }}</td>
                    <td style="border: 1px solid #000; padding: 5px;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 5px;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: right;">{{ number_format($subtotal - $discount, 2) }}</td>
                    @if($isInterState)
                        <td style="border: 1px solid #000; padding: 5px;">&nbsp;</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: right;">{{ number_format($igstAmount, 2) }}</td>
                    @else
                        <td style="border: 1px solid #000; padding: 5px;">&nbsp;</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: right;">{{ number_format($sgstAmount, 2) }}</td>
                        <td style="border: 1px solid #000; padding: 5px;">&nbsp;</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: right;">{{ number_format($cgstAmount, 2) }}</td>
                    @endif
                    <td style="border: 1px solid #000; padding: 5px; text-align: right;">{{ number_format($total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Bottom Grid Section -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 0px; font-size: 8px; border: 1px solid #000; border-top: none;">
            <tr>
                <td style="width: 22%; border: 1px solid #000; padding: 4px; font-weight: bold;">SALES PERSON</td>
                <td style="width: 43%; border: 1px solid #000; padding: 4px;">{{ strtoupper($invoice->sales_person ?? 'AKILESH KP') }}</td>
                <td style="width: 20%; border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; background: #fff;" rowspan="2">ROUND OFF</td>
                <td style="width: 15%; border: 1px solid #000; padding: 4px; text-align: right; font-weight: bold;" rowspan="2">
                    @php
                        $grandTotal = floatval($invoice->total);
                        $roundedTotal = round($grandTotal);
                        $roundOff = $roundedTotal - $grandTotal;
                    @endphp
                    {{ number_format($roundOff, 2) }}
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 4px; font-weight: bold;">BILL GENERATED/ACCOUNTANT</td>
                <td style="border: 1px solid #000; padding: 4px;">{{ strtoupper($invoice->createdBy?->name ?? 'RAMIZ') }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 4px; font-weight: bold;">BANK DETAILS</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 7.5px;">{{ strtoupper($invoice->bank_details ?? 'BANK : FEDERAL BANK BRANCH : PUTHANATHANI ACCOUNT NUMBER : 15430200007260 IFSC : FDRL0001543') }}</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; background: #fff; font-size: 9px;">GRAND TOTAL</td>
                <td style="border: 1px solid #000; padding: 4px; text-align: right; font-weight: bold; font-size: 11px; color: #D32F2F;">{{ number_format($roundedTotal, 2) }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 4px; font-weight: bold;">GRAND TOTAL IN WORDS</td>
                <td style="border: 1px solid #000; padding: 4px; font-weight: bold;" colspan="3">
                    {{ strtoupper(numberToWords($roundedTotal)) }}
                </td>
            </tr>
        </table>

        <!-- Terms and conditions -->
        <div class="terms-section">
            <strong>TERMS AND CONDITIONS :</strong> (1) There will be no warranty or replacement for physical or external damages like:- lightning, mishandling, electric short circuit, warranty seal broken, cover broken or damages caused by courier service, or Without proper Invoice (2) After the payment due date, fine at 24% per month will be charged on the amount overdue. (3) RS 500 will be charged per cheque, if it bounced. (4) The cheque has to be given within 5 days of purchase. If the cheque is not given, the account will be blocked by the account section. (5) Items sold will not be taken back or exchanged. (6) It is the responsibility of the customer to check whether the items are damaged or not. (7) Only the warranty as per manufactures warranty policy will be applicable for the items sold. (8) There is no guarantee for Data
        </div>

        <!-- Footer Signatures -->
        <div style="margin-top: 25px;">
            <div class="certified-text">
                Certified that all the particulars shown in the above invoice are true and<br>
                correct and Recived the item(s) in Good condition
            </div>
            <div class="generated-text">
                This is a system generated invoice<br>
                Hence, no signature is required.
            </div>
            <div class="clear"></div>
        </div>
    </div>
</body>
</html>
