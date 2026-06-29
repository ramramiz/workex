@extends('layouts.app')

@section('title', 'Global Alerts Management')
@section('page-title', 'Global Alerts')

@section('breadcrumb')
    <li class="breadcrumb-item active">Global Alerts</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <!-- Header Card -->
    <div class="card border shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1 fw-bold text-dark">Global Alerts Management</h4>
                <p class="text-secondary mb-0">Create and manage interruptive notifications sent to active users.</p>
            </div>
            <a href="{{ route('admin.alerts.create') }}" class="btn btn-warning fw-bold d-flex align-items-center gap-2 px-4 py-2.5 text-dark" style="border-radius: 10px;">
                <i class="bi bi-bell-fill"></i> Create New Alert
            </a>
        </div>
    </div>

    <!-- Alerts List -->
    <div class="card border shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 py-3" style="font-size: 13px; font-weight: 600;">Alert Details</th>
                            <th class="py-3" style="font-size: 13px; font-weight: 600;">Sent By</th>
                            <th class="py-3" style="font-size: 13px; font-weight: 600;">Confirmed Status</th>
                            <th class="py-3" style="font-size: 13px; font-weight: 600;">Date Created</th>
                            <th class="pe-4 py-3 text-end" style="font-size: 13px; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alerts as $alert)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark mb-1" style="font-size: 15px;">{{ $alert->heading }}</div>
                                            <div class="text-secondary" style="font-size: 13px; max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                {{ $alert->title }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="fw-semibold text-dark">{{ $alert->creator->name }}</span>
                                </td>
                                <td class="py-3">
                                    @php
                                        $total = $alert->users()->count();
                                        $confirmed = $alert->users()->whereNotNull('confirmed_at')->count();
                                    @endphp
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                        {{ $confirmed }} / {{ $total }} Confirmed
                                    </span>
                                </td>
                                <td class="py-3 text-secondary" style="font-size: 13px;">
                                    {{ $alert->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <form method="POST" action="{{ route('admin.alerts.destroy', $alert) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this alert? It will be removed for all users.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm border-0 rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Delete Alert">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-bell-slash text-muted opacity-50" style="font-size: 40px;"></i>
                                    <h5 class="fw-bold text-dark mt-3 mb-1">No Alerts Sent</h5>
                                    <p class="text-secondary mb-0">Sent alerts will appear here. Click "Create New Alert" to broadcast one.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($alerts->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $alerts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
