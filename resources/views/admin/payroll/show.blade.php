<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip — {{ $slip->employee->name }} — {{ date('F Y', mktime(0, 0, 0, $slip->month, 1, $slip->year)) }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
        }
        .payslip-container {
            max-width: 850px;
            margin: 40px auto;
        }
        .payslip-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            padding: 48px;
            position: relative;
            overflow: hidden;
        }
        /* Top border accent line */
        .payslip-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #4f46e5, #06b6d4);
        }
        .payslip-header {
            border-bottom: 2px dashed #e2e8f0;
            padding-bottom: 24px;
            margin-bottom: 28px;
        }
        .company-logo-container {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .company-logo-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .company-details {
            font-size: 0.8rem;
            color: #64748b;
            line-height: 1.5;
        }
        .company-details i {
            color: #4f46e5;
        }
        .payslip-title-container {
            text-align: right;
        }
        .payslip-label {
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #4f46e5;
            margin-bottom: 2px;
        }
        .payslip-period {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 9999px;
            text-transform: uppercase;
            margin-top: 8px;
        }
        .status-badge.paid {
            background-color: #dcfce7;
            color: #15803d;
        }
        .status-badge.pending {
            background-color: #fef3c7;
            color: #b45309;
        }
        /* Info Grid Styling */
        .info-grid {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 28px;
        }
        .info-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
        }
        .info-value-highlight {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }
        .info-label-inline {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .info-value-inline {
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
        }
        /* Ledger Tables Styling */
        .ledger-header {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 10px 16px;
            border-radius: 8px 8px 0 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .ledger-header.earnings {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            border-bottom: none;
        }
        .ledger-header.deductions {
            background-color: #fff1f2;
            color: #991b1b;
            border: 1px solid #fecdd3;
            border-bottom: none;
        }
        .ledger-table {
            border: 1px solid #e2e8f0;
            border-radius: 0 0 8px 8px;
            overflow: hidden;
            background: #fff;
            margin-bottom: 28px;
        }
        .ledger-table table {
            margin-bottom: 0;
            font-size: 0.85rem;
        }
        .ledger-table th, .ledger-table td {
            padding: 12px 16px;
        }
        .ledger-table tr:not(:last-child) {
            border-bottom: 1px solid #f1f5f9;
        }
        .ledger-table td.amount {
            font-weight: 600;
            text-align: right;
        }
        .ledger-table tr.total-row {
            background-color: #f8fafc;
            font-weight: 700;
            border-top: 2px solid #e2e8f0;
        }
        .ledger-table tr.total-row td {
            color: #0f172a;
        }
        /* Summary Box */
        .summary-box {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #fff;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .summary-box .net-label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .summary-box .net-amount {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #38bdf8;
        }
        .summary-box .payment-info {
            font-size: 0.8rem;
            color: #cbd5e1;
            margin-top: 4px;
        }
        .summary-box .payment-info strong {
            color: #fff;
        }
        /* Remarks Card */
        .remarks-card {
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            background-color: #fafafa;
            padding: 16px;
            margin-bottom: 28px;
            font-size: 0.8rem;
        }
        .remarks-title {
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
            display: block;
        }
        .remarks-content {
            color: #64748b;
            line-height: 1.5;
        }
        /* Footer note */
        .payslip-footer {
            text-align: center;
            color: #64748b;
            font-size: 0.75rem;
            margin-top: 40px;
            line-height: 1.6;
        }
        .payslip-footer p {
            margin-bottom: 4px;
        }
        
        @media print {
            body {
                background: white !important;
                color: #000 !important;
            }
            .payslip-container {
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .payslip-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .payslip-card::before {
                display: none !important;
            }
            .no-print {
                display: none !important;
            }
            .ledger-header {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .ledger-header.earnings {
                background-color: #ecfdf5 !important;
                color: #065f46 !important;
                border: 1px solid #a7f3d0 !important;
            }
            .ledger-header.deductions {
                background-color: #fff1f2 !important;
                color: #991b1b !important;
                border: 1px solid #fecdd3 !important;
            }
            .summary-box {
                background: #0f172a !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .summary-box .net-amount {
                color: #38bdf8 !important;
            }
            .status-badge.paid {
                background-color: #dcfce7 !important;
                color: #15803d !important;
            }
            .status-badge.pending {
                background-color: #fef3c7 !important;
                color: #b45309 !important;
            }
            .info-grid {
                background-color: #f8fafc !important;
                border: 1px solid #e2e8f0 !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<div class="container payslip-container">
    <div class="text-center mb-4 mt-4 no-print">
        <button class="btn btn-dark px-4 py-2" onclick="window.print()" style="border-radius: 8px; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <i class="bi bi-printer me-2"></i> Print / Save as PDF
        </button>
    </div>

    <div class="payslip-card">
        <!-- Payslip Header -->
        <div class="payslip-header d-flex justify-content-between align-items-start flex-wrap gap-4">
            <div class="d-flex align-items-start gap-3">
                <div class="company-logo-container">
                    @if($companyLogo)
                        <img src="{{ asset('storage/' . $companyLogo) }}" alt="{{ $companyName }}">
                    @else
                        <i class="bi bi-building fs-3 text-secondary"></i>
                    @endif
                </div>
                <div>
                    <h4 class="mb-1 fw-bold text-dark" style="letter-spacing: -0.01em;">{{ $companyName }}</h4>
                    <div class="company-details">
                        <div class="d-flex align-items-start gap-1">
                            <i class="bi bi-geo-alt mt-0.5 text-secondary"></i>
                            <div>{!! nl2br(e($companyAddress)) !!}</div>
                        </div>
                        <div class="mt-1">
                            <span class="me-3"><i class="bi bi-telephone me-1"></i> {{ $companyPhone }}</span>
                            <span><i class="bi bi-envelope me-1"></i> {{ $companyEmail }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="payslip-title-container">
                <div class="payslip-label">Payslip</div>
                <div class="payslip-period">
                    {{ date('F Y', mktime(0, 0, 0, $slip->month, 1, $slip->year)) }}
                    @if(($slip->cycle ?? 1) > 1 || ($slip->employee->company->salary_cycle ?? '') === 'twice_monthly')
                        <span class="fs-6 fw-semibold text-secondary d-block mt-0.5" style="letter-spacing: normal; text-transform: none; font-size: 0.8rem;">
                            Cycle {{ $slip->cycle ?? 1 }} ({{ ($slip->cycle ?? 1) == 1 ? '1st - 15th' : '16th - End' }})
                        </span>
                    @endif
                </div>
                <div>
                    @if(($slip->status ?? 'paid') == 'paid')
                        <span class="status-badge paid">
                            <i class="bi bi-check-circle-fill"></i> Paid
                        </span>
                    @else
                        <span class="status-badge pending">
                            <i class="bi bi-clock-fill"></i> Pending
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Employee Info Section -->
        <div class="info-grid">
            <div class="row g-3">
                <div class="col-6 col-sm-3">
                    <div class="info-label">Employee Name</div>
                    <div class="info-value-highlight">{{ $slip->employee->name }}</div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="info-label">Employee Code</div>
                    <div class="info-value">{{ $slip->employee->employee_code }}</div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="info-label">Department</div>
                    <div class="info-value">{{ $slip->employee->department->name ?? 'N/A' }}</div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="info-label">Designation</div>
                    <div class="info-value">{{ $slip->employee->designation->name ?? 'Employee' }}</div>
                </div>
                
                <div class="col-12 mt-3 pt-3 border-top d-flex justify-content-between flex-wrap gap-3">
                    <div>
                        <span class="info-label-inline me-2">Email:</span>
                        <span class="info-value-inline text-dark">{{ $slip->employee->email }}</span>
                    </div>
                    @if($slip->employee->joining_date)
                        <div>
                            <span class="info-label-inline me-2">Joining Date:</span>
                            <span class="info-value-inline text-dark">{{ $slip->employee->joining_date->format('d M Y') }}</span>
                        </div>
                    @endif
                    @if($slip->employee->work_type)
                        <div>
                            <span class="info-label-inline me-2">Work Type:</span>
                            <span class="info-value-inline text-dark">{{ ucwords(str_replace('_', ' ', $slip->employee->work_type)) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ledger Table Grid -->
        @php
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
        <div class="row g-4">
            <!-- Earnings -->
            <div class="col-12 col-md-6">
                <div class="ledger-header earnings">
                    <i class="bi bi-plus-circle-fill"></i> Earnings
                </div>
                <div class="ledger-table">
                    <table class="table align-middle">
                        <tbody>
                            <tr>
                                <td class="text-muted">Basic Salary</td>
                                <td class="amount">₹{{ number_format($slip->basic_salary, 2) }}</td>
                            </tr>
                            @if(count($customAllowances) > 0)
                                @foreach($customAllowances as $allowance)
                                    <tr>
                                        <td class="text-muted">{{ $allowance['label'] }}</td>
                                        <td class="amount text-success">+₹{{ number_format($allowance['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="text-muted">Allowances & Bonuses</td>
                                    <td class="amount text-success">
                                        @if($slip->allowances > 0)
                                            +₹{{ number_format($slip->allowances, 2) }}
                                        @else
                                            ₹0.00
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            <tr class="total-row">
                                <td>Total Earnings (A)</td>
                                <td class="amount">₹{{ number_format($slip->basic_salary + $slip->allowances, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Deductions -->
            <div class="col-12 col-md-6">
                <div class="ledger-header deductions">
                    <i class="bi bi-dash-circle-fill"></i> Deductions
                </div>
                <div class="ledger-table">
                    <table class="table align-middle">
                        <tbody>
                            <tr>
                                <td class="text-muted">
                                    @if($slip->employee->salary_type !== 'hourly' && isset($leavesCount) && $leavesCount > 0)
                                        Leave Deduction / LOP ({{ $leavesCount }} {{ $leavesCount == 1 ? 'day' : 'days' }})
                                    @else
                                        Leave Deduction / LOP
                                    @endif
                                </td>
                                <td class="amount text-danger">
                                    @if($slip->employee->salary_type !== 'hourly' && isset($leavesCount) && $leavesCount > 0)
                                        @php
                                            $dailyRate = $slip->employee->salary / 26;
                                            $lopAmt = $leavesCount * $dailyRate;
                                        @endphp
                                        -₹{{ number_format($lopAmt, 2) }}
                                    @else
                                        ₹0.00
                                    @endif
                                </td>
                            </tr>
                            @if(count($customDeductions) > 0)
                                @foreach($customDeductions as $deduction)
                                    <tr>
                                        <td class="text-muted">{{ $deduction['label'] }}</td>
                                        <td class="amount text-danger">-₹{{ number_format($deduction['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @if(count($customDeductions) === 0)
                                @php
                                    $lopAmt = 0;
                                    if ($slip->employee->salary_type !== 'hourly' && isset($leavesCount) && $leavesCount > 0) {
                                        $dailyRate = $slip->employee->salary / 26;
                                        $lopAmt = $leavesCount * $dailyRate;
                                    }
                                    $otherDeduct = max(0, $slip->deductions - $lopAmt);
                                @endphp
                                @if($otherDeduct > 0)
                                    <tr>
                                        <td class="text-muted">Other Deductions</td>
                                        <td class="amount text-danger">-₹{{ number_format($otherDeduct, 2) }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="text-muted text-opacity-25" style="color: transparent;">Other Deductions</td>
                                        <td class="amount text-opacity-25" style="color: transparent;">₹0.00</td>
                                    </tr>
                                @endif
                            @endif
                            <tr class="total-row">
                                <td>Total Deductions (B)</td>
                                <td class="amount">₹{{ number_format($slip->deductions, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Net Salary Summary Box -->
        <div class="summary-box">
            <div class="row align-items-center">
                <div class="col-12 col-sm-6 text-center text-sm-start">
                    <div class="net-label">Net Salary Disbursed</div>
                    <div class="payment-info">Paid via <strong>{{ ucwords(str_replace('_', ' ', $slip->payment_method)) }}</strong> on <strong>{{ $slip->payment_date->format('d M Y') }}</strong></div>
                </div>
                <div class="col-12 col-sm-6 text-center text-sm-end mt-3 mt-sm-0">
                    <div class="net-amount">₹{{ number_format($slip->net_salary, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Remarks -->
        @if($slip->remarks)
            <div class="remarks-card">
                <span class="remarks-title"><i class="bi bi-chat-left-text-fill me-1"></i> Remarks</span>
                <div class="remarks-content">{{ $slip->remarks }}</div>
            </div>
        @endif

        <!-- Footer -->
        <div class="payslip-footer">
            <p class="fw-medium">This is a computer-generated payslip and does not require a physical signature.</p>
            <p>For any queries, please reach out to HR at <span class="text-dark fw-medium">{{ $companyEmail }}</span> or call <span class="text-dark fw-medium">{{ $companyPhone }}</span>.</p>
            <p class="mt-2 text-muted">&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</p>
        </div>
    </div>
</div>

</body>
</html>
