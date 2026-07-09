@extends('layouts.app')

@section('title', 'Log Expenses')
@section('page-title', 'Log Expenses')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
    <li class="breadcrumb-item active">Log</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-wallet-fill text-primary me-2"></i>File New Expenditures</h5>
                    <span class="badge bg-light text-dark border fw-semibold">Multi-Row Entry Mode</span>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('expenses.store') }}" id="multi-expense-form">
                    @csrf
                    
                    <!-- Shared General Metadata -->
                    <div class="row g-4 mb-4 pb-4 border-bottom">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark">Project Allocation <span class="text-muted">(Optional)</span></label>
                            <select name="project_id" class="form-select select-search @error('project_id') is-invalid @enderror" style="height: 42px; border-radius: 8px;">
                                <option value="">-- Office Expense (No Project linked) --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} ({{ $p->client?->company_name ?? 'Internal Project' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text text-muted fs-8">Select which client project this expense belongs to, or leave empty for general office expenditures.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" style="height: 42px; border-radius: 8px;" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text text-muted fs-8">Select the calendar date on which the expense transaction occurred.</div>
                        </div>
                    </div>

                    <!-- Interactive Rows Section -->
                    <div class="table-responsive mb-4">
                        <table class="table align-middle border-0" id="expense-table" style="min-width: 900px;">
                            <thead>
                                <tr class="text-muted font-monospace uppercase-title" style="border-bottom: 2px solid #f1f5f9; font-size: 11px; letter-spacing: 0.5px;">
                                    <th style="width: 25%; font-weight: 700;">Expense</th>
                                    <th style="width: 35%; font-weight: 700;">Description</th>
                                    <th style="width: 15%; font-weight: 700;">Amount</th>
                                    <th style="width: 15%; font-weight: 700;">Type</th>
                                    <th style="width: 10%; font-weight: 700;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="expense-rows-container">
                                <!-- JavaScript will inject rows here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Controls & Summary -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 bg-light p-3 rounded-3 border">
                        <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" onclick="addExpenseRow()">
                            <i class="bi bi-plus-lg me-1"></i> Add Expense Item
                        </button>
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-muted fw-medium fs-7">Cumulative Total:</span>
                            <span class="fs-5 fw-bold text-dark" id="total-expenses-amount">₹0.00</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center justify-content-end gap-3 border-top pt-4">
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 8px; font-weight: 600; height: 42px; display: inline-flex; align-items: center;">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px; font-weight: 600; height: 42px; display: inline-flex; align-items: center;">
                            <i class="bi bi-check2-circle me-2"></i> File Expenses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #expense-table input.form-control, 
    #expense-table select.form-select {
        height: 40px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-size: 13.5px;
        transition: all 0.2s ease;
    }
    #expense-table input.form-control:focus, 
    #expense-table select.form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .custom-title-container {
        animation: fadeIn 0.2s ease-out;
    }
    .expense-select-wrapper {
        position: relative;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
    let rowCount = 0;

    document.addEventListener('DOMContentLoaded', function() {
        // Start with one row
        addExpenseRow();
    });

    function addExpenseRow() {
        rowCount++;
        const tbody = document.getElementById('expense-rows-container');
        const tr = document.createElement('tr');
        tr.id = `expense-row-${rowCount}`;
        tr.style.borderBottom = '1px solid #f1f5f9';
        
        tr.innerHTML = `
            <td>
                <div class="expense-select-wrapper">
                    <select name="expenses[${rowCount}][title]" class="form-select expense-title-select" required onchange="handleExpenseTitleChange(this, ${rowCount})">
                        <option value="">Select Expense</option>
                        <option value="add_new">+ Add New Expense</option>
                        <option value="Staff Incentive">Staff Incentive</option>
                        <option value="FOOD">FOOD</option>
                        <option value="TRAVEL ALLOWANCE">TRAVEL ALLOWANCE</option>
                        <option value="FREIGHT CHARGES">FREIGHT CHARGES</option>
                        <option value="SALARY">SALARY</option>
                        <option value="OFFICE SUPPLIES">OFFICE SUPPLIES</option>
                        <option value="HOSTING & SERVERS">HOSTING & SERVERS</option>
                        <option value="MARKETING">MARKETING</option>
                        <option value="OTHER">OTHER</option>
                    </select>
                    <div class="mt-2 custom-title-container d-none" id="custom-title-container-${rowCount}">
                        <div class="input-group input-group-sm">
                            <input type="text" name="expenses[${rowCount}][custom_title]" class="form-control" placeholder="Enter custom expense name">
                            <button type="button" class="btn btn-outline-secondary" onclick="cancelCustomTitle(${rowCount})" title="Back to list">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <input type="text" name="expenses[${rowCount}][description]" class="form-control" placeholder="Optional details or specifications">
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted" style="border-radius: 8px 0 0 8px; border-right: none; font-size: 13.5px;">₹</span>
                    <input type="number" step="0.01" name="expenses[${rowCount}][amount]" class="form-control text-end expense-amount-input" value="0" required min="0" oninput="calculateTotalAmount()" style="border-radius: 0 8px 8px 0;">
                </div>
            </td>
            <td>
                <select name="expenses[${rowCount}][payment_mode]" class="form-select text-uppercase" required>
                    <option value="Cash">CASH</option>
                    <optgroup label="Bank Accounts">
                        @foreach($banks as $bank)
                            <option value="{{ $bank->name }}">{{ strtoupper($bank->name) }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Investors">
                        @foreach($investors as $investor)
                            <option value="Investor: {{ $investor->name }}">INVESTOR: {{ strtoupper($investor->name) }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger text-white border-0 px-3 fw-semibold shadow-sm" onclick="removeExpenseRow(${rowCount})" style="background-color: #f87171; border-radius: 8px; height: 38px; font-size: 13px;">Delete</button>
            </td>
        `;
        tbody.appendChild(tr);
        calculateTotalAmount();
    }

    function removeExpenseRow(id) {
        const row = document.getElementById(`expense-row-${id}`);
        if (row) {
            row.remove();
        }
        
        // Ensure there is at least one active row
        const container = document.getElementById('expense-rows-container');
        if (container.children.length === 0) {
            addExpenseRow();
        }
        calculateTotalAmount();
    }

    function handleExpenseTitleChange(select, id) {
        const customContainer = document.getElementById(`custom-title-container-${id}`);
        const input = customContainer.querySelector('input');
        if (select.value === 'add_new') {
            customContainer.classList.remove('d-none');
            input.setAttribute('required', 'required');
            input.focus();
        } else {
            customContainer.classList.add('d-none');
            input.removeAttribute('required');
            input.value = '';
        }
    }

    function cancelCustomTitle(id) {
        const select = document.querySelector(`#expense-row-${id} .expense-title-select`);
        const customContainer = document.getElementById(`custom-title-container-${id}`);
        const input = customContainer.querySelector('input');
        
        select.value = '';
        customContainer.classList.add('d-none');
        input.removeAttribute('required');
        input.value = '';
    }

    function calculateTotalAmount() {
        let total = 0;
        document.querySelectorAll('.expense-amount-input').forEach(input => {
            const val = parseFloat(input.value) || 0;
            total += val;
        });
        document.getElementById('total-expenses-amount').textContent = '₹' + total.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
</script>
@endpush
