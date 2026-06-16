@extends('layouts.app')
@section('title', 'Accounts Dashboard')
@section('page-title', 'Accounts Dashboard')
@section('content')
<div class="page-header"><div><h1 class="page-title">Accounts Dashboard</h1><p class="page-subtitle">{{ now()->format('l, d F Y') }}</p></div><a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Invoice</a></div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card text-center"><div style="font-size:28px;font-weight:800;color:#f59e0b;">{{ $stats['pending_invoices'] }}</div><div style="font-size:13px;color:#64748b;">Pending Invoices</div></div></div>
    <div class="col-md-3"><div class="stat-card text-center"><div style="font-size:28px;font-weight:800;color:#ef4444;">{{ $stats['overdue_invoices'] }}</div><div style="font-size:13px;color:#64748b;">Overdue</div></div></div>
    <div class="col-md-3"><div class="stat-card text-center"><div style="font-size:28px;font-weight:800;color:#ef4444;">₹{{ number_format($stats['total_pending_amount'], 0) }}</div><div style="font-size:13px;color:#64748b;">Pending Amount</div></div></div>
    <div class="col-md-3"><div class="stat-card text-center"><div style="font-size:28px;font-weight:800;color:#10b981;">₹{{ number_format($stats['this_month_income'], 0) }}</div><div style="font-size:13px;color:#64748b;">This Month Income</div></div></div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between"><span>Pending Invoices</span><a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">View All</a></div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Invoice #</th><th>Client</th><th>Project</th><th>Amount</th><th>Due Date</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($pendingInvoices as $inv)
            <tr>
                <td style="font-size:13px;font-weight:600;">{{ $inv->invoice_number }}</td>
                <td style="font-size:13px;">{{ $inv->client?->company_name }}</td>
                <td style="font-size:13px;color:#64748b;">{{ Str::limit($inv->project?->name, 20) }}</td>
                <td style="font-size:13px;font-weight:600;">₹{{ number_format($inv->balance_amount, 0) }}</td>
                <td style="font-size:13px;{{ $inv->is_overdue ? 'color:#ef4444;' : 'color:#64748b;' }}">{{ $inv->due_date?->format('d M Y') ?? 'N/A' }}</td>
                <td><span class="badge bg-warning-subtle text-warning" style="font-size:10px;">{{ ucfirst($inv->status) }}</span></td>
                <td><a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary" style="font-size:11px;">View</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">No pending invoices</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
