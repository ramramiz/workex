<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Disbursed Receipt</title>
    <style>
        body {
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            padding: 32px;
            border-bottom: 2px dashed #f1f5f9;
            background-color: #ffffff;
        }
        .company-logo {
            max-height: 50px;
            margin-bottom: 12px;
            display: block;
        }
        .company-name {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .receipt-title {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #4f46e5;
            margin-top: 4px;
        }
        .body {
            padding: 32px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 12px;
        }
        .intro-text {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
            background-color: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .details-table td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .details-label {
            color: #64748b;
            font-weight: 600;
            width: 40%;
        }
        .details-value {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }
        .ledger-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #ffffff;
            margin-bottom: 28px;
            overflow: hidden;
        }
        .ledger-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background-color: #f8fafc;
            padding: 10px 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
        }
        .ledger-row {
            padding: 12px 16px;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
            overflow: auto;
        }
        .ledger-row:last-child {
            border-bottom: none;
        }
        .ledger-label {
            float: left;
            color: #64748b;
        }
        .ledger-val {
            float: right;
            font-weight: 600;
        }
        .ledger-val.earnings {
            color: #16a34a;
        }
        .ledger-val.deductions {
            color: #dc2626;
        }
        .summary-row {
            background-color: #1e293b;
            color: #ffffff;
            padding: 18px 16px;
            overflow: auto;
        }
        .summary-label {
            float: left;
            font-weight: 700;
            font-size: 15px;
            line-height: 28px;
        }
        .summary-val {
            float: right;
            font-weight: 800;
            font-size: 20px;
            color: #38bdf8;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff !important;
            padding: 14px 28px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }
        .footer {
            padding: 32px;
            background-color: #fafafa;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
        }
        .footer-logo {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="container">
        <!-- Email Header -->
        <div class="header">
            @if($companyLogo && file_exists(storage_path('app/public/' . $companyLogo)))
                <img class="company-logo" src="{{ $message->embed(storage_path('app/public/' . $companyLogo)) }}" alt="{{ $companyName }}" height="32" style="height: 32px; max-height: 32px; width: auto; max-width: 150px; display: block; margin-bottom: 12px; object-fit: contain;">
            @endif
            <h2 class="company-name">{{ $companyName }}</h2>
            <div class="receipt-title">Salary Disbursal Receipt</div>
        </div>

        <!-- Email Body -->
        <div class="body">
            <h3 class="greeting">Hello {{ $slip->employee->name }},</h3>
            <p class="intro-text">
                We are pleased to inform you that your salary for <strong>{{ date('F Y', mktime(0, 0, 0, $slip->month, 1, $slip->year)) }}</strong> has been disbursed successfully. Below is your payment breakdown and transaction receipt details.
            </p>

            <!-- Disbursal Details -->
            <table class="details-table">
                <tr>
                    <td class="details-label">Employee ID</td>
                    <td class="details-value">{{ $slip->employee->employee_code }}</td>
                </tr>
                <tr>
                    <td class="details-label">Pay Period</td>
                    <td class="details-value">{{ date('F Y', mktime(0, 0, 0, $slip->month, 1, $slip->year)) }}</td>
                </tr>
                <tr>
                    <td class="details-label">Disbursal Date</td>
                    <td class="details-value">{{ $slip->payment_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td class="details-label">Payment Method</td>
                    <td class="details-value">{{ ucwords(str_replace('_', ' ', $slip->payment_method)) }}</td>
                </tr>
            </table>

            <!-- Ledger Breakdown -->
            @php
                $month = $slip->month;
                $year = $slip->year;
                $cycle = $slip->cycle ?? 1;
                $company = $slip->employee->company;
                
                $totalDaysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
                $startDay = 1;
                $endDay = $totalDaysInMonth;

                if ($company && $company->salary_cycle === 'twice_monthly') {
                    if ($cycle === 1) {
                        $startDay = 1;
                        $endDay = 15;
                    } else {
                        $startDay = 16;
                        $endDay = $totalDaysInMonth;
                    }
                }

                $term1Leaves = \App\Models\Leave::where('user_id', $slip->employee->user_id)
                    ->whereMonth('from_date', $month)->whereYear('from_date', $year)
                    ->whereDay('from_date', '<=', 15)
                    ->where('status', 'approved')
                    ->sum('total_days');

                $term2Leaves = \App\Models\Leave::where('user_id', $slip->employee->user_id)
                    ->whereMonth('from_date', $month)->whereYear('from_date', $year)
                    ->whereDay('from_date', '>', 15)
                    ->where('status', 'approved')
                    ->sum('total_days');

                $term1CL = min(1.0, $term1Leaves);
                $term1LOP = $term1Leaves - $term1CL;

                $term2CL = min(1.0 - $term1CL, $term2Leaves);
                $term2LOP = $term2Leaves - $term2CL;

                $totalCL = $term1CL + $term2CL;
                $totalLOP = $term1LOP + $term2LOP;

                if ($company && $company->salary_cycle === 'twice_monthly') {
                    $lopDays = ($cycle === 1) ? $term1LOP : $term2LOP;
                    $clDays = ($cycle === 1) ? $term1CL : $term2CL;
                } else {
                    $lopDays = $totalLOP;
                    $clDays = $totalCL;
                }

                $leavesCount = $lopDays;

                $customAllowances = [];
                if ($slip->remarks && strpos($slip->remarks, '| Allowances: ') !== false) {
                    $parts = explode('| Allowances: ', $slip->remarks);
                    $allowStr = end($parts);
                    if (strpos($allowStr, '| Deductions: ') !== false) {
                        $allowParts = explode('| Deductions: ', $allowStr);
                        $allowStr = $allowParts[0];
                    }
                    $lines = explode(';', $allowStr);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        if (strpos($line, ':') !== false) {
                            list($label, $amountStr) = explode(':', $line, 2);
                            $amountVal = filter_var($amountStr, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                            $customAllowances[] = [
                                'label' => trim($label),
                                'amount' => (float) $amountVal
                            ];
                        }
                    }
                }

                $customDeductions = [];
                if ($slip->remarks && strpos($slip->remarks, '| Deductions: ') !== false) {
                    $parts = explode('| Deductions: ', $slip->remarks);
                    $deductStr = end($parts);
                    $lines = explode(';', $deductStr);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        if (strpos($line, ':') !== false) {
                            list($label, $amountStr) = explode(':', $line, 2);
                            $amountVal = filter_var($amountStr, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                            $customDeductions[] = [
                                'label' => trim($label),
                                'amount' => (float) $amountVal
                            ];
                        }
                    }
                }
            @endphp
            <div class="ledger-box">
                <div class="ledger-title">Salary Breakdown Ledger</div>
                
                <div class="ledger-row">
                    <span class="ledger-label">Basic Salary Structure</span>
                    <span class="ledger-val">₹{{ number_format($slip->basic_salary, 2) }}</span>
                </div>
                
                @if(count($customAllowances) > 0)
                    @foreach($customAllowances as $allowance)
                        <div class="ledger-row">
                            <span class="ledger-label">{{ $allowance['label'] }}</span>
                            <span class="ledger-val earnings">+₹{{ number_format($allowance['amount'], 2) }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="ledger-row">
                        <span class="ledger-label">Allowances & Bonuses</span>
                        <span class="ledger-val earnings">
                            @if($slip->allowances > 0)
                                +₹{{ number_format($slip->allowances, 2) }}
                            @else
                                ₹0.00
                            @endif
                        </span>
                    </div>
                @endif
                
                <div class="ledger-row">
                    <span class="ledger-label">
                        @if($slip->employee->salary_type !== 'hourly' && $lopDays > 0)
                            Leave Deduction / LOP ({{ $lopDays }} {{ $lopDays == 1 ? 'day' : 'days' }})
                        @else
                            Leave Deduction / LOP
                        @endif
                    </span>
                    <span class="ledger-val deductions">
                        @if($slip->employee->salary_type !== 'hourly' && $lopDays > 0)
                            @php
                                $dailyRate = $slip->employee->salary / 26;
                                $lopAmt = $lopDays * $dailyRate;
                            @endphp
                            -₹{{ number_format($lopAmt, 2) }}
                        @else
                            ₹0.00
                        @endif
                    </span>
                </div>

                @if($clDays > 0)
                    <div class="ledger-row">
                        <span class="ledger-label">Casual Leave (CL) ({{ $clDays }} {{ $clDays == 1 ? 'day' : 'days' }})</span>
                        <span class="ledger-val" style="color: #16a34a;">₹0.00 <span style="font-weight: normal; font-size: 11px; color: #64748b;">(No Deduction)</span></span>
                    </div>
                @endif

                @if(count($customDeductions) > 0)
                    @foreach($customDeductions as $deduction)
                        <div class="ledger-row">
                            <span class="ledger-label">{{ $deduction['label'] }}</span>
                            <span class="ledger-val deductions">-₹{{ number_format($deduction['amount'], 2) }}</span>
                        </div>
                    @endforeach
                @endif

                @if(count($customDeductions) === 0)
                    @php
                        $lopAmt = 0;
                        if ($slip->employee->salary_type !== 'hourly' && $lopDays > 0) {
                            $dailyRate = $slip->employee->salary / 26;
                            $lopAmt = $lopDays * $dailyRate;
                        }
                        $otherDeduct = max(0, $slip->deductions - $lopAmt);
                    @endphp
                    @if($otherDeduct > 0)
                        <div class="ledger-row">
                            <span class="ledger-label">Other Deductions</span>
                            <span class="ledger-val deductions">-₹{{ number_format($otherDeduct, 2) }}</span>
                        </div>
                    @endif
                @endif

                <!-- Total Summary Row -->
                <div class="summary-row">
                    <span class="summary-label">Net Disbursed Amount</span>
                    <span class="summary-val">₹{{ number_format($slip->net_salary, 2) }}</span>
                </div>
            </div>

            <!-- Monthly Leave Summary (2 Terms) -->
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; background-color: #f8fafc; padding: 18px; margin-bottom: 28px;">
                <h4 style="margin-top: 0; margin-bottom: 12px; font-size: 14px; font-weight: 700; color: #0f172a;">Monthly Leave Summary (2 Terms)</h4>
                
                <table style="width: 100%; font-size: 13px; line-height: 1.5; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0; text-align: left;">
                            <th style="padding: 6px 0; color: #475569;">Term</th>
                            <th style="padding: 6px 0; text-align: center; color: #475569;">Total Leaves</th>
                            <th style="padding: 6px 0; text-align: center; color: #475569;">Casual Leaves</th>
                            <th style="padding: 6px 0; text-align: center; color: #475569;">Deduction Leaves (LOP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 8px 0; font-weight: 600; color: #334155;">1st Term (1st - 15th)</td>
                            <td style="padding: 8px 0; text-align: center; font-weight: 600; color: #334155;">{{ $term1Leaves }}</td>
                            <td style="padding: 8px 0; text-align: center; color: #16a34a; font-weight: 600;">{{ $term1CL }}</td>
                            <td style="padding: 8px 0; text-align: center; color: #dc2626; font-weight: 600;">{{ $term1LOP }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 8px 0; font-weight: 600; color: #334155;">2nd Term (16th - End)</td>
                            <td style="padding: 8px 0; text-align: center; font-weight: 600; color: #334155;">{{ $term2Leaves }}</td>
                            <td style="padding: 8px 0; text-align: center; color: #16a34a; font-weight: 600;">{{ $term2CL }}</td>
                            <td style="padding: 8px 0; text-align: center; color: #dc2626; font-weight: 600;">{{ $term2LOP }}</td>
                        </tr>
                        <tr style="background-color: #f1f5f9; font-weight: bold;">
                            <td style="padding: 8px; color: #0f172a;">Month Total</td>
                            <td style="padding: 8px; text-align: center; color: #0f172a;">{{ $term1Leaves + $term2Leaves }}</td>
                            <td style="padding: 8px; text-align: center; color: #16a34a;">{{ $totalCL }}</td>
                            <td style="padding: 8px; text-align: center; color: #dc2626;">{{ $totalLOP }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- View online CTA -->
            <div class="btn-container">
                <a href="{{ route('admin.payroll.show', $slip) }}" class="btn" target="_blank">View Online Payslip</a>
            </div>
        </div>

        <!-- Email Footer -->
        <div class="footer">
            <div class="footer-logo">{{ $companyName }}</div>
            <div>{!! nl2br(e($companyAddress)) !!}</div>
            <div style="margin-top: 8px;">
                Phone: {{ $companyPhone }} | Email: {{ $companyEmail }}
            </div>
            <p style="margin-top: 24px; font-size: 11px; color: #94a3b8; line-height: 1.4;">
                This is a computer-generated transactional receipt and does not require a physical signature. If you have any questions regarding your payroll, please contact the HR department.
            </p>
            <p style="margin-top: 8px; font-size: 11px;">
                &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
            </p>
        </div>
    </div>
</div>

</body>
</html>
