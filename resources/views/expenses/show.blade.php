@extends('layouts.app')

@section('title', 'Expense Details')
@section('page-title', 'Expense Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-md-6 mx-auto">
        <div class="card shadow-sm border border-light">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Expense Details</h5>
                <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit Log</a>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="bg-danger-subtle text-danger border border-danger-subtle rounded-circle p-2 d-inline-flex align-items-center justify-content-center mb-2" style="width:60px; height:60px;">
                        <i class="bi bi-cash-stack fs-2"></i>
                    </div>
                    <h3 class="fw-bold text-danger">₹{{ number_format($expense->amount, 2) }}</h3>
                    <span class="fs-8 text-muted text-uppercase font-monospace">Expense Log #{{ $expense->id }}</span>
                </div>

                <hr>

                <div class="mb-3">
                    <small class="text-muted d-block fs-8">Title</small>
                    <span class="fw-bold text-dark fs-6">{{ $expense->title }}</span>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Log Date</small>
                        <span class="fw-medium text-dark">{{ $expense->date ? $expense->date->format('d M Y') : '—' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Category</small>
                        <span class="text-capitalize badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                            {{ str_replace('_', ' ', $expense->category) }}
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    @php
                        $matchedBank = \App\Models\Bank::where('name', $expense->payment_mode)->first();
                        $payMethodDisplay = $matchedBank 
                            ? ($matchedBank->name . ' - ' . $matchedBank->branch . ' - ****' . substr($matchedBank->account_number, -4)) 
                            : ($expense->payment_mode ?? 'Cash');
                    @endphp
                    <span class="fw-medium text-dark font-monospace"><i class="bi bi-credit-card me-1"></i>{{ $payMethodDisplay }}</span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Allocated Project</small>
                    @if($expense->project)
                        <span class="fw-semibold text-primary"><i class="bi bi-kanban"></i> <a href="{{ route('projects.show', $expense->project) }}">{{ $expense->project->name }}</a></span>
                    @else
                        <span class="text-muted font-monospace fs-7">Office Operations</span>
                    @endif
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Recorded By</small>
                    <span class="fw-medium text-dark fs-7">{{ $expense->addedBy->name ?? 'System' }}</span>
                </div>

                @if($expense->description)
                    <div class="mb-3">
                        <small class="text-muted d-block">Description</small>
                        <p class="text-muted fs-7 mb-0 mt-1" style="white-space: pre-wrap;">{{ $expense->description }}</p>
                    </div>
                @endif

                <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3 mt-4">
                    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
