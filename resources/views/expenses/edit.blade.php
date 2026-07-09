@extends('layouts.app')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Expense Claim</h5>
                    <span class="badge bg-light text-secondary border fw-semibold">ID: #{{ $expense->id }}</span>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('expenses.update', $expense) }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- Shared General Metadata -->
                    <div class="row g-4 mb-4 pb-4 border-bottom">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark">Project Allocation <span class="text-muted">(Optional)</span></label>
                            <select name="project_id" class="form-select select-search @error('project_id') is-invalid @enderror" style="height: 42px; border-radius: 8px;">
                                <option value="">-- Office Expense (No Project linked) --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ old('project_id', $expense->project_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} ({{ $p->client?->company_name ?? 'Internal Project' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" style="height: 42px; border-radius: 8px;" value="{{ old('date', $expense->date ? $expense->date->format('Y-m-d') : '') }}" required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <!-- Row-based Design Layout matching the table format -->
                    <div class="table-responsive mb-4">
                        <table class="table align-middle border-0" style="min-width: 900px;">
                            <thead>
                                <tr class="text-muted font-monospace uppercase-title" style="border-bottom: 2px solid #f1f5f9; font-size: 11px; letter-spacing: 0.5px;">
                                    <th style="width: 25%; font-weight: 700;">Expense</th>
                                    <th style="width: 40%; font-weight: 700;">Description</th>
                                    <th style="width: 20%; font-weight: 700;">Amount</th>
                                    <th style="width: 15%; font-weight: 700;">Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td>
                                        <div class="expense-select-wrapper">
                                            @php
                                                $predefinedOptions = [
                                                    'Staff Incentive', 'FOOD', 'TRAVEL ALLOWANCE', 
                                                    'FREIGHT CHARGES', 'SALARY', 'OFFICE SUPPLIES', 
                                                    'HOSTING & SERVERS', 'MARKETING', 'OTHER'
                                                ];
                                                $isPredefined = in_array($expense->title, $predefinedOptions);
                                            @endphp
                                            <select name="title" class="form-select expense-title-select" id="expense-title-select" required onchange="handleExpenseTitleChange(this)">
                                                <option value="">Select Expense</option>
                                                <option value="add_new" {{ !$isPredefined && !empty($expense->title) ? 'selected' : '' }}>+ Add New Expense</option>
                                                @foreach($predefinedOptions as $opt)
                                                    <option value="{{ $opt }}" {{ $expense->title === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                            <div class="mt-2 custom-title-container {{ $isPredefined || empty($expense->title) ? 'd-none' : '' }}" id="custom-title-container">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="custom_title" id="custom-title-input" class="form-control" placeholder="Enter custom expense name" value="{{ !$isPredefined ? $expense->title : '' }}" {{ !$isPredefined ? 'required' : '' }}>
                                                    <button type="button" class="btn btn-outline-secondary" onclick="cancelCustomTitle()" title="Back to list">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="description" class="form-control" placeholder="Optional details or specifications" value="{{ old('description', $expense->description) }}">
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted" style="border-radius: 8px 0 0 8px; border-right: none; font-size: 13.5px;">₹</span>
                                            <input type="number" step="0.01" name="amount" class="form-control text-end" value="{{ old('amount', $expense->amount) }}" required min="0" style="border-radius: 0 8px 8px 0;">
                                        </div>
                                    </td>
                                    <td>
                                        <select name="payment_mode" class="form-select text-uppercase" required>
                                            <option value="Cash" {{ old('payment_mode', $expense->payment_mode) === 'Cash' ? 'selected' : '' }}>CASH</option>
                                            <optgroup label="Bank Accounts">
                                                @foreach($banks as $bank)
                                                    <option value="{{ $bank->name }}" {{ old('payment_mode', $expense->payment_mode) === $bank->name ? 'selected' : '' }}>{{ strtoupper($bank->name) }}</option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="Investors">
                                                @foreach($investors as $investor)
                                                    <option value="Investor: {{ $investor->name }}" {{ old('payment_mode', $expense->payment_mode) === 'Investor: ' . $investor->name ? 'selected' : '' }}>INVESTOR: {{ strtoupper($investor->name) }}</option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden old category input for controller requirement -->
                    <input type="hidden" name="category" value="{{ $expense->category }}">

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center justify-content-end gap-3 border-top pt-4">
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 8px; font-weight: 600; height: 42px; display: inline-flex; align-items: center;">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px; font-weight: 600; height: 42px; display: inline-flex; align-items: center;">
                            <i class="bi bi-check2-circle me-2"></i> Update Expense
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
    .form-control, .form-select {
        height: 40px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-size: 13.5px;
        transition: all 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .custom-title-container {
        animation: fadeIn 0.2s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
    function handleExpenseTitleChange(select) {
        const customContainer = document.getElementById('custom-title-container');
        const input = document.getElementById('custom-title-input');
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

    function cancelCustomTitle() {
        const select = document.getElementById('expense-title-select');
        const customContainer = document.getElementById('custom-title-container');
        const input = document.getElementById('custom-title-input');
        
        select.value = '';
        customContainer.classList.add('d-none');
        input.removeAttribute('required');
        input.value = '';
    }
</script>
@endpush
