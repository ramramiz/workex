<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip — {{ $slip->employee->name }} — {{ date('F Y', mktime(0, 0, 0, $slip->month, 1, $slip->year)) }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }
        .payslip-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            padding: 40px;
            margin: 40px auto;
            max-width: 800px;
        }
        .payslip-header {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        .text-gradient {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .payslip-title {
            letter-spacing: 0.05em;
            font-weight: 800;
        }
        .table-summary {
            background-color: #f8fafc;
            border-radius: 12px;
            padding: 20px;
        }
        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 20px 0;
        }
        @media print {
            body {
                background: white;
            }
            .payslip-card {
                border: none;
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="text-center mt-4 no-print">
        <button class="btn btn-primary px-4 py-2" onclick="window.print()" style="border-radius: 10px; font-weight: 700;">
            <i class="bi bi-printer me-2"></i> Print / Save as PDF
        </button>
    </div>

    <div class="payslip-card">
        <!-- Payslip Header -->
        <div class="payslip-header d-flex justify-content-between align-items-center flex-wrap gap-4">
            <div class="d-flex align-items-center gap-3">
                @if($companyLogo)
                    <div class="border rounded p-1 bg-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; overflow: hidden; flex-shrink: 0;">
                        <img src="{{ asset('storage/' . $companyLogo) }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                @else
                    <div class="bg-primary text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="bi bi-lightning-charge-fill fs-4"></i>
                    </div>
                @endif
                <div>
                    <h4 class="mb-0 fw-bold text-dark">{{ $companyName }}</h4>
                    <span class="text-muted fs-7">{{ $companyAddress }}</span>
                </div>
            </div>
            <div class="text-md-end">
                <h5 class="payslip-title text-gradient text-uppercase mb-1">Payslip</h5>
                <span class="fw-bold text-dark fs-5">
                    {{ date('F Y', mktime(0, 0, 0, $slip->month, 1, $slip->year)) }}
                </span>
            </div>
        </div>

        <!-- Employee Info Section -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <span class="text-muted text-uppercase tracking-wider fs-8 fw-bold">Employee Details</span>
                <div class="mt-2">
                    <h5 class="fw-bold text-dark mb-1">{{ $slip->employee->name }}</h5>
                    <div class="text-muted fs-7">Code: <strong class="text-dark">{{ $slip->employee->employee_code }}</strong></div>
                    <div class="text-muted fs-7">Email: <strong class="text-dark">{{ $slip->employee->email }}</strong></div>
                </div>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <span class="text-muted text-uppercase tracking-wider fs-8 fw-bold">Department / Position</span>
                <div class="mt-2">
                    <h6 class="fw-bold text-dark mb-1">{{ $slip->employee->designation->name ?? 'Employee' }}</h6>
                    <div class="text-muted fs-7">Dept: <strong class="text-dark">{{ $slip->employee->department->name ?? 'N/A' }}</strong></div>
                    <div class="text-muted fs-7">Payment Date: <strong class="text-dark">{{ $slip->payment_date->format('d M Y') }}</strong></div>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Earnings and Deductions Table -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6">
                <h6 class="fw-bold text-success mb-3"><i class="bi bi-plus-circle me-1"></i> Earnings</h6>
                <table class="table table-borderless align-middle mb-0 fs-7">
                    <tbody>
                        <tr>
                            <td class="text-muted py-2 ps-0">Basic Salary Structure</td>
                            <td class="text-end py-2 pe-0 fw-semibold">₹{{ number_format($slip->basic_salary, 2) }}</td>
                        </tr>
                        @if($slip->allowances > 0)
                            <tr>
                                <td class="text-muted py-2 ps-0">Allowances / Bonus</td>
                                <td class="text-end py-2 pe-0 fw-semibold text-success">+₹{{ number_format($slip->allowances, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="col-12 col-md-6">
                <h6 class="fw-bold text-danger mb-3"><i class="bi bi-dash-circle me-1"></i> Deductions</h6>
                <table class="table table-borderless align-middle mb-0 fs-7">
                    <tbody>
                        @if($slip->deductions > 0)
                            <tr>
                                <td class="text-muted py-2 ps-0">Deductions / Penalties</td>
                                <td class="text-end py-2 pe-0 fw-semibold text-danger">-₹{{ number_format($slip->deductions, 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="text-muted py-2 ps-0">No deductions recorded</td>
                                <td class="text-end py-2 pe-0 fw-semibold">—</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grand Total Summary Card -->
        <div class="table-summary mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold text-muted mb-1 text-uppercase tracking-wider fs-8">Net Salary Disbursed</h6>
                    <div class="text-muted fs-8">Paid via: <strong class="text-dark">{{ ucwords(str_replace('_', ' ', $slip->payment_method)) }}</strong></div>
                </div>
                <div class="text-end">
                    <h2 class="fw-extrabold text-primary mb-0">₹{{ number_format($slip->net_salary, 2) }}</h2>
                </div>
            </div>
        </div>

        <!-- Remarks -->
        @if($slip->remarks)
            <div class="p-3 border rounded-3 bg-light fs-7">
                <span class="fw-bold text-secondary d-block mb-1">Remarks:</span>
                <span class="text-muted">{{ $slip->remarks }}</span>
            </div>
        @endif

        <div class="text-center mt-5 text-muted fs-8">
            <p>This is a computer-generated payslip and does not require a physical signature.</p>
            <p class="mb-0">&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</p>
        </div>
    </div>
</div>

</body>
</html>
