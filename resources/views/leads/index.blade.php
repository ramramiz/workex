@extends('layouts.app')

@section('title', 'Leads & Enquiries')
@section('page-title', isset($room) ? $room->name : (isset($title) ? $title : 'Leads & Enquiries'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
    @if(isset($room))
        <li class="breadcrumb-item active">{{ $room->name }}</li>
    @elseif(isset($title))
        <li class="breadcrumb-item active">{{ $title }}</li>
    @else
        <li class="breadcrumb-item active">List</li>
    @endif
@endsection

@section('content')
<div class="container-fluid px-0">
    <!-- MAIN BODY -->
    @if(isset($room))
        <!-- STATE A: Specific Room leads view -->
        <div class="card border shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white py-3.5 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                        <i class="bi bi-arrow-left me-1"></i> Back to Rooms
                    </a>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-door-open-fill text-warning me-2"></i>{{ $room->name }}</h5>
                        @if($room->client)
                            <small class="text-secondary d-block mt-0.5">{{ $room->client->company_name }}</small>
                        @else
                            <small class="text-secondary d-block mt-0.5">General calling room</small>
                        @endif
                    </div>
                </div>

                <div class="d-inline-flex gap-2">
                    <a href="{{ route('leads.import.form') }}" class="btn btn-outline-success btn-sm rounded-pill">
                        <i class="bi bi-file-earmark-excel me-1"></i> Import Leads
                    </a>
                    <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm rounded-pill">
                        <i class="bi bi-plus-lg me-1"></i> Add Lead
                    </a>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="px-4 py-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3" style="background-color: #f8f9fa !important;">
                <ul class="nav nav-pills" id="leadWorkTabs" style="gap: 8px;">
                    <!-- 1. Today Follow-ups -->
                    <li class="nav-item">
                        <a class="nav-link {{ ($tab ?? 'today_follow_up') === 'today_follow_up' ? 'active bg-warning text-dark' : 'bg-white text-secondary border border-light-subtle' }} fw-bold px-4 py-2 d-flex align-items-center gap-2" 
                           href="{{ route('leads.index', ['room_id' => $room->id, 'tab' => 'today_follow_up']) }}"
                           style="border-radius: 20px; transition: all 0.2s ease; font-size: 13px;">
                            @if(($todayFollowUpCount ?? 0) > 0)
                                <i class="bi bi-bell-fill text-danger animate-pulse"></i>
                            @else
                                <i class="bi bi-calendar-check"></i>
                            @endif
                            Today Follow-ups
                            <span class="badge {{ ($tab ?? 'today_follow_up') === 'today_follow_up' ? 'bg-dark text-white' : 'bg-secondary-subtle text-secondary' }} rounded-pill" style="font-size: 11px;">
                                {{ $todayFollowUpCount ?? 0 }}
                            </span>
                        </a>
                    </li>

                    <!-- 2. Interested Leads -->
                    <li class="nav-item">
                        <a class="nav-link {{ ($tab ?? 'today_follow_up') === 'interested' ? 'active bg-warning text-dark' : 'bg-white text-secondary border border-light-subtle' }} fw-bold px-4 py-2 d-flex align-items-center gap-2" 
                           href="{{ route('leads.index', ['room_id' => $room->id, 'tab' => 'interested']) }}"
                           style="border-radius: 20px; transition: all 0.2s ease; font-size: 13px;">
                            <i class="bi bi-star-fill text-success"></i>
                            Interested
                            <span class="badge {{ ($tab ?? 'today_follow_up') === 'interested' ? 'bg-dark text-white' : 'bg-secondary-subtle text-secondary' }} rounded-pill" style="font-size: 11px;">
                                {{ $interestedCount ?? 0 }}
                            </span>
                        </a>
                    </li>

                    <!-- 3. Not Connected Leads -->
                    <li class="nav-item">
                        <a class="nav-link {{ ($tab ?? 'today_follow_up') === 'not_connected' ? 'active bg-warning text-dark' : 'bg-white text-secondary border border-light-subtle' }} fw-bold px-4 py-2 d-flex align-items-center gap-2" 
                           href="{{ route('leads.index', ['room_id' => $room->id, 'tab' => 'not_connected']) }}"
                           style="border-radius: 20px; transition: all 0.2s ease; font-size: 13px;">
                            <i class="bi bi-telephone-x"></i>
                            Not Connected Leads
                            <span class="badge {{ ($tab ?? 'today_follow_up') === 'not_connected' ? 'bg-dark text-white' : 'bg-secondary-subtle text-secondary' }} rounded-pill" style="font-size: 11px;">
                                {{ $notConnectedCount ?? 0 }}
                            </span>
                        </a>
                    </li>

                    <!-- 4. All Contacts -->
                    <li class="nav-item">
                        <a class="nav-link {{ ($tab ?? 'today_follow_up') === 'all_contacts' ? 'active bg-warning text-dark' : 'bg-white text-secondary border border-light-subtle' }} fw-bold px-4 py-2 d-flex align-items-center gap-2" 
                           href="{{ route('leads.index', ['room_id' => $room->id, 'tab' => 'all_contacts']) }}"
                           style="border-radius: 20px; transition: all 0.2s ease; font-size: 13px;">
                            <i class="bi bi-people-fill"></i>
                            All Contacts
                            <span class="badge {{ ($tab ?? 'today_follow_up') === 'all_contacts' ? 'bg-dark text-white' : 'bg-secondary-subtle text-secondary' }} rounded-pill" style="font-size: 11px;">
                                {{ $allContactsCount ?? 0 }}
                            </span>
                        </a>
                    </li>
                </ul>

                @if($tab === 'interested' && $leads->count() > 0)
                    <a href="{{ route('leads.start-work.interested-leads.export') }}" class="btn btn-success btn-sm fw-bold d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill">
                        <i class="bi bi-file-earmark-spreadsheet-fill"></i> Export XLS
                    </a>
                @endif
            </div>

            <!-- Leads Table -->
            @include('leads.index_table')
        </div>

    @elseif(isset($type))
        <!-- STATE B: Global filter view (e.g. all Interested leads across system) -->
        <div class="card border shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white py-3.5 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                        <i class="bi bi-arrow-left me-1"></i> Back to Rooms
                    </a>
                    <h5 class="mb-0 fw-bold text-dark">{{ $title }}</h5>
                </div>

                @if($type === 'interested' && $leads->count() > 0)
                    <a href="{{ route('leads.start-work.interested-leads.export') }}" class="btn btn-success btn-sm fw-bold d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill">
                        <i class="bi bi-file-earmark-spreadsheet-fill"></i> Export XLS
                    </a>
                @endif
            </div>

            <!-- Leads Table -->
            @include('leads.index_table')
        </div>

    @else
        <!-- STATE C: Default list (Grouped Customers OR Telecallers selection view) -->
        <div class="card border shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white py-3.5 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="mb-0 me-3 fw-bold text-dark">Leads Pipeline</h5>
                    <ul class="nav nav-pills card-header-pills flex-wrap gap-1">
                        <li class="nav-item">
                            <a class="nav-link {{ request('view') !== 'telecaller' ? 'active bg-warning text-dark' : 'bg-secondary-subtle text-secondary' }} py-1 px-3 fw-bold" style="font-size: 13px; border-radius: 20px;" href="{{ route('leads.index', ['view' => 'customer']) }}">By Customer</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('view') === 'telecaller' ? 'active bg-warning text-dark' : 'bg-secondary-subtle text-secondary' }} py-1 px-3 fw-bold" style="font-size: 13px; border-radius: 20px;" href="{{ route('leads.index', ['view' => 'telecaller']) }}">By Telecaller</a>
                        </li>
                    </ul>
                </div>
                <div class="d-inline-flex gap-2">
                    <a href="{{ route('lead-rooms.index') }}" class="btn btn-outline-warning btn-sm rounded-pill text-dark fw-bold">
                        <i class="bi bi-door-open me-1"></i> Manage Rooms
                    </a>
                    <a href="{{ route('leads.import.form') }}" class="btn btn-outline-success btn-sm rounded-pill">
                        <i class="bi bi-file-earmark-excel me-1"></i> Import Leads
                    </a>
                    <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm rounded-pill">
                        <i class="bi bi-plus-lg me-1"></i> Add Lead
                    </a>
                </div>
            </div>

            <div class="card-body bg-light-subtle p-4">
                @if(request('view') === 'telecaller')
                    <div class="text-center pb-4">
                        <h4 class="fw-bold text-dark mb-1">Telecallers Work Sessions</h4>
                        <p class="text-secondary fs-7 mb-0">Select a telecaller to view their calling sessions and reports.</p>
                    </div>

                    <div class="row g-4 justify-content-center">
                        @forelse($telecallers as $telecaller)
                            @php
                                $collapseId = 'collapseTelecaller_' . $telecaller->id;
                            @endphp
                            <div class="col-12 col-lg-10">
                                <!-- Telecaller Card -->
                                <div class="card border shadow-sm mb-1" style="border-radius: 16px; overflow: hidden; border-color: rgba(99, 102, 241, 0.15) !important;">
                                    <!-- Clickable Telecaller Card Header -->
                                    <div class="card-header bg-white p-4 cursor-pointer d-flex align-items-center justify-content-between flex-wrap gap-3 hover-client-header collapsed" 
                                         data-bs-toggle="collapse" 
                                         data-bs-target="#{{ $collapseId }}" 
                                         aria-expanded="false" 
                                         aria-controls="{{ $collapseId }}"
                                         style="transition: all 0.2s ease;">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $telecaller->avatar_url }}" class="rounded-circle" style="width: 52px; height: 52px; object-fit: cover;" alt="{{ $telecaller->name }}">
                                            <div>
                                                <h5 class="fw-bold text-dark mb-0" style="font-size: 18px;">{{ $telecaller->name }}</h5>
                                                <small class="text-secondary d-flex align-items-center gap-2 mt-1" style="font-size: 13px;">
                                                    <span><i class="bi bi-envelope"></i> {{ $telecaller->email }}</span>
                                                    @if($telecaller->employee)
                                                        <span>| <i class="bi bi-card-text"></i> {{ $telecaller->employee->employee_code }}</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="badge bg-primary text-white rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 12px;">
                                                {{ $telecaller->leadRoomWorkSessions->count() }} {{ Str::plural('Session', $telecaller->leadRoomWorkSessions->count()) }}
                                            </span>
                                            <i class="bi bi-chevron-down fs-5 text-secondary collapse-indicator"></i>
                                        </div>
                                    </div>

                                    <!-- Sessions Under Telecaller (Collapsed by Default) -->
                                    <div id="{{ $collapseId }}" class="collapse bg-light-subtle border-top">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table align-middle mb-0">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th class="ps-4">Room</th>
                                                            <th>Started At</th>
                                                            <th>Ended At</th>
                                                            <th>Duration</th>
                                                            <th>Calls Logged</th>
                                                            <th>Converted</th>
                                                            <th>Status</th>
                                                            <th class="text-end pe-4">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($telecaller->leadRoomWorkSessions as $session)
                                                            @php
                                                                $durationText = 'N/A';
                                                                if ($session->total_seconds) {
                                                                    $hours = floor($session->total_seconds / 3600);
                                                                    $minutes = floor(($session->total_seconds % 3600) / 60);
                                                                    $seconds = $session->total_seconds % 60;
                                                                    
                                                                    $durationText = '';
                                                                    if ($hours > 0) $durationText .= $hours . 'h ';
                                                                    if ($minutes > 0) $durationText .= $minutes . 'm ';
                                                                    if ($seconds > 0 && $hours == 0) $durationText .= $seconds . 's';
                                                                    $durationText = trim($durationText) ?: '0s';
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td class="ps-4">
                                                                    <span class="fw-semibold text-dark">{{ $session->room ? $session->room->name : 'Today\'s Follow-ups' }}</span>
                                                                </td>
                                                                <td>{{ $session->started_at ? $session->started_at->format('d M Y, h:i A') : '—' }}</td>
                                                                <td>{{ $session->ended_at ? $session->ended_at->format('d M Y, h:i A') : ($session->status === 'active' ? 'Active now' : '—') }}</td>
                                                                <td>{{ $durationText }}</td>
                                                                <td><span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">{{ $session->calls_count ?? 0 }} calls</span></td>
                                                                <td><span class="badge bg-success-subtle text-success px-2.5 py-1">{{ $session->converted_count ?? 0 }} converted</span></td>
                                                                <td>
                                                                    @if($session->status === 'approved')
                                                                        <span class="badge bg-success text-white">Approved</span>
                                                                    @elseif($session->status === 'pending')
                                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                                    @elseif($session->status === 'rejected')
                                                                        <span class="badge bg-danger text-white">Rejected</span>
                                                                    @elseif($session->status === 'active')
                                                                        <span class="badge bg-info text-white">Active</span>
                                                                    @else
                                                                        <span class="badge bg-secondary text-white">{{ ucfirst($session->status) }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-end pe-4">
                                                                    <div class="d-inline-flex gap-2">
                                                                        <a href="{{ route('leads.start-work.summary', [$session->lead_room_id ?: 0, $session->id]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1">
                                                                            <i class="bi bi-file-earmark-bar-graph me-1"></i> View Report
                                                                        </a>
                                                                        @if(auth()->user()->isAdminOrAbove())
                                                                            <a href="{{ route('leads.start-work.download-report', $session->id) }}" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1">
                                                                                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="8" class="text-center py-4 text-muted">
                                                                    No work sessions recorded for this telecaller.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 col-lg-8">
                                <div class="card border p-5 text-center bg-white" style="border-radius: 16px;">
                                    <div class="fs-1 text-muted mb-3">
                                        <i class="bi bi-people text-secondary opacity-50" style="font-size: 48px;"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2">No Telecallers Found</h5>
                                    <p class="text-secondary mb-0">There are no active employees assigned with the telecaller role.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                @else
                    <div class="text-center pb-4">
                        <h4 class="fw-bold text-dark mb-1">Assigned Rooms by Customer</h4>
                        <p class="text-secondary fs-7 mb-0">Select a room under a customer to view and manage its leads pipeline.</p>
                    </div>

                    <div class="row g-4 justify-content-center">
                        @php
                            $groupedRooms = $rooms->groupBy(function($room) {
                                return $room->client ? $room->client->id : 0;
                            });
                        @endphp

                        @forelse($groupedRooms as $clientId => $clientRooms)
                            @php
                                $firstRoom = $clientRooms->first();
                                $clientName = $firstRoom->client ? $firstRoom->client->company_name : 'General / Direct Calling';
                                $contactPerson = $firstRoom->client ? $firstRoom->client->contact_person : 'System';
                                $clientEmail = $firstRoom->client ? $firstRoom->client->email : '';
                                $clientPhone = $firstRoom->client ? $firstRoom->client->phone : '';
                                $collapseId = 'collapseClient_' . $clientId;
                            @endphp
                            <div class="col-12 col-lg-10">
                                <!-- Customer Card -->
                                <div class="card border shadow-sm mb-1" style="border-radius: 16px; overflow: hidden; border-color: rgba(99, 102, 241, 0.15) !important;">
                                    <!-- Clickable Customer Card Header -->
                                    <div class="card-header bg-white p-4 cursor-pointer d-flex align-items-center justify-content-between flex-wrap gap-3 hover-client-header collapsed" 
                                         data-bs-toggle="collapse" 
                                         data-bs-target="#{{ $collapseId }}" 
                                         aria-expanded="false" 
                                         aria-controls="{{ $collapseId }}"
                                         style="transition: all 0.2s ease;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; min-width: 52px;">
                                                <i class="bi bi-person-fill fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <h5 class="fw-bold text-dark mb-0" style="font-size: 18px;">{{ $clientName }}</h5>
                                                    @if($firstRoom->client)
                                                        <button type="button" class="btn btn-outline-success btn-sm px-2.5 py-1 text-success d-inline-flex align-items-center gap-1" 
                                                                style="font-size: 11px; border-radius: 20px; font-weight: 600; line-height: 1;" 
                                                                onclick="event.stopPropagation(); openExportModal({{ json_encode($clientId) }}, {{ json_encode($clientName) }}, {{ json_encode($clientRooms->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->values()) }})">
                                                            <i class="bi bi-file-earmark-arrow-down"></i> Export Numbers
                                                        </button>
                                                    @endif
                                                </div>
                                                @if($firstRoom->client)
                                                    <small class="text-secondary d-flex align-items-center gap-2 mt-1" style="font-size: 13px;">
                                                        <span><i class="bi bi-person-circle"></i> {{ $contactPerson }}</span>
                                                        @if($clientPhone)<span>| <i class="bi bi-telephone"></i> {{ $clientPhone }}</span>@endif
                                                        @if($clientEmail)<span>| <i class="bi bi-envelope"></i> {{ $clientEmail }}</span>@endif
                                                    </small>
                                                @else
                                                    <small class="text-secondary d-block mt-1" style="font-size: 13px;">Rooms not associated with a specific customer</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="badge bg-primary text-white rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 12px;">
                                                {{ $clientRooms->count() }} {{ Str::plural('Room', $clientRooms->count()) }}
                                            </span>
                                            <i class="bi bi-chevron-down fs-5 text-secondary collapse-indicator"></i>
                                        </div>
                                    </div>

                                    <!-- Rooms Under Customer (Collapsed by Default) -->
                                    <div id="{{ $collapseId }}" class="collapse bg-light-subtle border-top">
                                        <div class="card-body p-4">
                                            <div class="row g-3">
                                                @foreach($clientRooms as $room)
                                                    <div class="col-12 col-md-6 col-xxl-4">
                                                        <div class="card h-100 border shadow-xs hover-shadow-sm transition-all" style="border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s;">
                                                            <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                                                                <div>
                                                                    <div class="d-flex align-items-center gap-2.5 mb-2.5">
                                                                        <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px;">
                                                                            <i class="bi bi-door-open-fill fs-5"></i>
                                                                        </div>
                                                                        <div>
                                                                            <h6 class="fw-bold text-dark mb-0" style="font-size: 14.5px;">{{ $room->name }}</h6>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex flex-wrap gap-1.5 mb-3">
                                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 9.5px; border-radius: 6px; padding: 3.5px 7px;">
                                                                            Total: {{ $room->leads_count }}
                                                                        </span>
                                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 9.5px; border-radius: 6px; padding: 3.5px 7px;">
                                                                            Contacted: {{ $room->contacted_leads_count ?? 0 }}
                                                                        </span>
                                                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9.5px; border-radius: 6px; padding: 3.5px 7px;">
                                                                            Interested: {{ $room->interested_leads_count ?? 0 }}
                                                                        </span>
                                                                    </div>
                                                                    <p class="text-secondary fs-8 mb-3">{{ Str::limit($room->description ?? 'No description provided for this room.', 90) }}</p>
                                                                </div>
                                                                <div class="mt-auto">
                                                                    <a href="{{ route('leads.index', ['room_id' => $room->id]) }}" class="btn btn-warning w-100 fw-bold btn-sm d-flex align-items-center justify-content-center gap-1.5 py-2 text-dark" style="border-radius: 8px;">
                                                                        Select Room <i class="bi bi-arrow-right-short"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 col-lg-8">
                                <div class="card border p-5 text-center bg-white" style="border-radius: 16px;">
                                    <div class="fs-1 text-muted mb-3">
                                        <i class="bi bi-door-closed text-secondary opacity-50" style="font-size: 48px;"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2">No Customers or Rooms Found</h5>
                                    <p class="text-secondary mb-0">Please assign rooms to telecallers or create them under Customers first.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@include('leads.modals')

<style>
    .hover-shadow-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05) !important;
    }
    .hover-client-header:hover {
        background-color: #f8fafc !important;
    }
    .hover-client-header:not(.collapsed) .collapse-indicator {
        transform: rotate(180deg);
    }
    .collapse-indicator {
        transition: transform 0.25s ease;
    }
    .hover-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06) !important;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes bellRing {
        0%, 100% { transform: rotate(0); }
        20%, 60% { transform: rotate(12deg); }
        40%, 80% { transform: rotate(-12deg); }
    }
    .animate-bell {
        display: inline-block;
        animation: bellRing 3s ease infinite;
    }
</style>
@endsection
