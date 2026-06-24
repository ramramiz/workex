@extends('layouts.app')

@section('title', 'Manage Lead Rooms')
@section('page-title', 'Manage Lead Rooms')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
    <li class="breadcrumb-item active">Rooms</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 me-3">Leads</h5>
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item">
                    <a class="nav-link py-1 px-3" href="{{ route('leads.index') }}">Pipeline</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active py-1 px-3" href="{{ route('lead-rooms.index') }}">Manage Rooms</a>
                </li>
            </ul>
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoomModal">
            <i class="bi bi-plus-lg me-1"></i> Create Room
        </button>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Room Name</th>
                    <th>Customer</th>
                    <th>Description</th>
                    <th>Created By</th>
                    <th class="text-center">Assigned Telecallers</th>
                    <th class="text-center">Leads Count</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                <span class="d-inline-block p-1.5 rounded bg-primary-subtle text-primary">
                                    <i class="bi bi-door-open-fill"></i>
                                </span>
                                {{ $room->name }}
                            </div>
                        </td>
                        <td>
                            @if($room->client)
                                <span class="fw-semibold text-dark">{{ $room->client->company_name }}</span>
                                <small class="text-muted d-block" style="font-size: 11px;">{{ $room->client->contact_person }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted fs-7">{{ Str::limit($room->description ?? 'No description provided', 50) }}</span>
                        </td>
                        <td>
                            <span class="fs-7">{{ $room->creator->name ?? 'System' }}</span>
                        </td>
                        <td class="text-center">
                            @if($room->users->isEmpty())
                                <span class="text-muted fs-7">None</span>
                            @else
                                <div class="d-flex justify-content-center flex-wrap gap-1">
                                    @foreach($room->users as $tCaller)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 11px;">
                                            {{ $tCaller->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex flex-column align-items-start gap-1 py-1 text-start">
                                <div class="d-flex align-items-center gap-2" title="Total Leads">
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace px-2" style="font-size: 11px; min-width: 35px; display: inline-block; text-align: center;">
                                        {{ $room->leads_count }}
                                    </span>
                                    <span class="text-secondary fs-8 fw-medium">Total Leads</span>
                                </div>
                                <div class="d-flex align-items-center gap-2" title="Contacted Leads">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2" style="font-size: 11px; min-width: 35px; display: inline-block; text-align: center;">
                                        {{ $room->contacted_leads_count }}
                                    </span>
                                    <span class="text-secondary fs-8 fw-medium">Contacted</span>
                                </div>
                                <div class="d-flex align-items-center gap-2" title="Interested Leads">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2" style="font-size: 11px; min-width: 35px; display: inline-block; text-align: center;">
                                        {{ $room->interested_leads_count }}
                                    </span>
                                    <span class="text-secondary fs-8 fw-medium">Interested</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" title="Assign Telecallers" data-bs-toggle="modal" data-bs-target="#assignModal{{ $room->id }}">
                                    <i class="bi bi-person-gear"></i> Assign
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" title="Edit Room" data-bs-toggle="modal" data-bs-target="#editModal{{ $room->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('lead-rooms.destroy', $room) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this room? Leads inside will be preserved.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Room">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Room Modal -->
                    <div class="modal fade" id="editModal{{ $room->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content border-0 shadow">
                                <form method="POST" action="{{ route('lead-rooms.update', $room) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title">Edit Room: {{ $room->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Customer (Client) <span class="text-danger">*</span></label>
                                            <select name="client_id" class="form-select" required>
                                                <option value="" disabled>Select Customer</option>
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" {{ $room->client_id == $client->id ? 'selected' : '' }}>
                                                        {{ $client->company_name }} ({{ $client->contact_person }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Room Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ old('name', $room->name) }}" required placeholder="e.g. Real Estate Leads">
                                        </div>
                                        <div>
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea name="description" class="form-control" rows="3" placeholder="Explain the purpose of this room...">{{ old('description', $room->description) }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Assign Telecallers Modal -->
                    <div class="modal fade" id="assignModal{{ $room->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content border-0 shadow">
                                <form method="POST" action="{{ route('lead-rooms.assign', $room) }}">
                                    @csrf
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title">Assign Telecallers to {{ $room->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-4">
                                        <p class="text-muted fs-7 mb-3">Select the telecallers who should be granted permission to access leads in this room.</p>
                                        
                                        @if($telecallers->isEmpty())
                                            <div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle me-2"></i>No active telecallers found in the system.</div>
                                        @else
                                            <div class="list-group rounded border">
                                                @foreach($telecallers as $tCaller)
                                                    <label class="list-group-item d-flex align-items-center gap-3 py-2.5 cursor-pointer">
                                                        <input type="checkbox" name="telecaller_ids[]" value="{{ $tCaller->id }}" class="form-check-input mt-0"
                                                            {{ $room->users->contains($tCaller->id) ? 'checked' : '' }}>
                                                        <div>
                                                            <div class="fw-semibold text-dark fs-6">{{ $tCaller->name }}</div>
                                                            <div class="text-muted" style="font-size: 11px;">{{ $tCaller->email }}</div>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" {{ $telecallers->isEmpty() ? 'disabled' : '' }}>Save Assignments</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-door-open" style="font-size: 32px;"></i>
                            <div class="mt-2">No rooms have been created yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Room Modal -->
<div class="modal fade" id="createRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('lead-rooms.store') }}">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title">Create Lead Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Customer (Client) <span class="text-danger">*</span></label>
                        <select name="client_id" class="form-select" required>
                            <option value="" disabled selected>Select Customer</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->company_name }} ({{ $client->contact_person }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Room Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. SaaS Lead Campaign">
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Explain the purpose of this room..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Room</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
