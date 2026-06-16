@extends('layouts.app')

@section('title', 'Leads & Enquiries')
@section('page-title', 'Leads & Enquiries')

@section('breadcrumb')
    <li class="breadcrumb-item active">Leads</li>
@endsection

@section('topnav-middle')
    @include('leads.status_nav')
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h5 class="mb-0 me-3">Leads</h5>
            <ul class="nav nav-pills card-header-pills flex-wrap gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ !request()->routeIs('lead-rooms.index') ? 'active' : '' }} py-1 px-3" href="{{ route('leads.index') }}">Pipeline</a>
                </li>
                @if(!auth()->user()->isTelecaller())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('lead-rooms.index') ? 'active' : '' }} py-1 px-3 bg-secondary-subtle text-secondary" style="font-size: 13px; font-weight: 500;" href="{{ route('lead-rooms.index') }}"><i class="bi bi-door-open me-1"></i>Manage Rooms</a>
                </li>
                @endif
            </ul>
        </div>
        @if(!auth()->user()->isTelecaller())
            <div class="d-inline-flex gap-2">
                <a href="{{ route('leads.import.form') }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i> Import Leads
                </a>
                <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Add Lead
                </a>
            </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('leads.index') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by client or requirement..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="room_id" class="form-select form-select-sm">
                    <option value="">All Rooms</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New Lead</option>
                    <option value="following_up" {{ request('status') === 'following_up' ? 'selected' : '' }}>Following Up</option>
                    <option value="interested" {{ request('status') === 'interested' ? 'selected' : '' }}>Interested</option>
                    <option value="not_interested" {{ request('status') === 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                    <option value="call_back_later" {{ request('status') === 'call_back_later' ? 'selected' : '' }}>Call Back Later</option>
                    <option value="follow_up_required" {{ request('status') === 'follow_up_required' ? 'selected' : '' }}>Follow-up Required</option>
                    <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Contact Number</th>
                    <th>Next Follow Up</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $lead->client_name }}</div>
                            @if($lead->room)
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-0.5 px-2 mt-1" style="font-size: 10px;">
                                    <i class="bi bi-door-open me-1"></i>{{ $lead->room->name }}
                                </span>
                            @endif
                            @if($lead->client_email)
                                <small class="text-muted d-block" style="font-size: 11px;">{{ $lead->client_email }}</small>
                            @endif
                        </td>
                        <td>
                            @if($lead->client_phone)
                                <a href="tel:{{ $lead->client_phone }}" class="text-dark font-monospace fw-bold text-decoration-none d-inline-flex align-items-center gap-1.5" style="font-size: 15px; transition: color 0.15s ease;" onmouseover="this.style.color='#16a34a'" onmouseout="this.style.color=''">
                                    <span class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle" style="width: 26px; height: 26px;">
                                        <i class="bi bi-telephone-fill" style="font-size: 11px;"></i>
                                    </span>
                                    {{ $lead->client_phone }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            @if($lead->follow_up_date)
                                <span class="{{ \Carbon\Carbon::parse($lead->follow_up_date)->isPast() && $lead->status !== 'converted' && $lead->status !== 'lost' ? 'text-danger fw-semibold' : '' }}">
                                    {{ \Carbon\Carbon::parse($lead->follow_up_date)->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($lead->status === 'new')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">New Lead</span>
                            @elseif($lead->status === 'following_up')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Following Up</span>
                            @elseif($lead->status === 'interested')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Interested</span>
                            @elseif($lead->status === 'not_interested')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Not Interested</span>
                            @elseif($lead->status === 'call_back_later')
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Call Back Later</span>
                            @elseif($lead->status === 'follow_up_required')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Follow-up Required</span>
                            @elseif($lead->status === 'converted')
                                <span class="badge bg-success text-white">Converted</span>
                            @elseif($lead->status === 'closed')
                                <span class="badge bg-dark text-white">Closed</span>
                            @else
                                <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <button type="button" class="btn btn-outline-success btn-sm" title="Log Call" 
                                    data-bs-toggle="modal" data-bs-target="#logCallModal" 
                                    data-bs-action="{{ route('leads.calls.store', $lead) }}">
                                    <i class="bi bi-telephone-outbound"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" title="Book Appointment" 
                                    data-bs-toggle="modal" data-bs-target="#bookAppointmentModal" 
                                    data-bs-action="{{ route('leads.appointments.store', $lead) }}">
                                    <i class="bi bi-calendar-check"></i>
                                </button>

                                <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(!auth()->user()->isTelecaller())
                                    <a href="{{ route('leads.edit', $lead) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($lead->status !== 'converted')
                                        <form method="POST" action="{{ route('leads.convert', $lead) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm" title="Convert to Quotation/Project">
                                                <i class="bi bi-arrow-right-short"></i> Convert
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('leads.destroy', $lead) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-funnel" style="font-size: 32px;"></i>
                            <div class="mt-2">No leads found in this pipeline.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($leads->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $leads->withQueryString()->links() }}
        </div>
    @endif
</div>
@include('leads.modals')
@endsection
