@extends('layouts.app')

@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('breadcrumb')
    <li class="breadcrumb-item active">Expenses</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Office & Project Expenditures</h5>
        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
            <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-wallet-fill me-1"></i> Log Expense
            </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('expenses.index') }}" class="row g-3">
            <div class="col-12 col-md-5">
                <select name="project" class="form-select form-select-sm">
                    <option value="">All Projects (Office Expense)</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    <option value="hosting" {{ request('category') === 'hosting' ? 'selected' : '' }}>Hosting & Servers</option>
                    <option value="marketing" {{ request('category') === 'marketing' ? 'selected' : '' }}>Marketing & Sales</option>
                    <option value="office_supplies" {{ request('category') === 'office_supplies' ? 'selected' : '' }}>Office Supplies</option>
                    <option value="travel" {{ request('category') === 'travel' ? 'selected' : '' }}>Travel</option>
                    <option value="salary" {{ request('category') === 'salary' ? 'selected' : '' }}>Salaries</option>
                    <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Expense Details</th>
                    <th>Linked Project</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $exp)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $exp->title }}</div>
                            <small class="text-muted fs-8">{{ Str::limit($exp->description, 40) }}</small>
                        </td>
                        <td>
                            @if($exp->project)
                                <span class="fw-semibold text-dark">{{ $exp->project->name }}</span>
                            @else
                                <span class="text-muted fs-8 font-monospace">General / Office</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-capitalize badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                {{ str_replace('_', ' ', $exp->category) }}
                            </span>
                        </td>
                        <td>{{ $exp->date ? $exp->date->format('d M Y') : '—' }}</td>
                        <td>
                            <span class="fw-bold text-danger">₹{{ number_format($exp->amount, 2) }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('expenses.show', $exp) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('expenses.edit', $exp) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('expenses.destroy', $exp) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-cash-stack" style="font-size: 32px;"></i>
                            <div class="mt-2">No expenses logged.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($expenses->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $expenses->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
