@extends('layouts.app')

@section('title', 'Lead Details')
@section('page-title', 'Lead Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
    <li class="breadcrumb-item active">{{ $lead->client_name }}</li>
@endsection

@section('topnav-middle')
    @include('leads.status_nav')
@endsection

@push('styles')
<style>
    .hover-shadow-sm {
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .hover-shadow-sm:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border-color: var(--primary) !important;
    }
    .fs-7.5 {
        font-size: 0.8rem;
    }
</style>
@endpush

@section('content')
@php
    $executives = \App\Models\User::where('status', 'active')
        ->whereHas('role', fn($q) => $q->whereIn('slug', ['admin', 'super-admin', 'employee', 'team-leader']))
        ->get();
@endphp

<div class="row g-4">
    <!-- Left Column: Lead Profile -->
    <div class="col-12 col-lg-5">
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Lead Profile</h5>
                <div class="d-flex align-items-center gap-1.5">
                    <button type="button" class="btn btn-outline-success btn-sm" title="Log Call" 
                        data-bs-toggle="modal" data-bs-target="#logCallModal" 
                        data-bs-action="{{ route('leads.calls.store', $lead) }}"
                        data-bs-client-name="{{ $lead->client_name }}"
                        data-bs-client-phone="{{ $lead->client_phone ?? '—' }}"
                        data-bs-calls-count="{{ $lead->calls->count() }}"
                        data-bs-last-contacted-at="{{ $lead->latestCall ? $lead->latestCall->call_date_time->format('d M Y, h:i A') : '' }}"
                        data-bs-first-remarks="{{ $lead->calls->sortBy('id')->first() ? e($lead->calls->sortBy('id')->first()->remarks) : '' }}">
                        <i class="bi bi-telephone-outbound"></i>
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm" title="Schedule Follow-up" 
                        data-bs-toggle="modal" data-bs-target="#scheduleFollowUpModal" 
                        data-bs-action="{{ route('leads.follow-up', $lead) }}">
                        <i class="bi bi-clock"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" title="Book Appointment" 
                        data-bs-toggle="modal" data-bs-target="#bookAppointmentModal" 
                        data-bs-action="{{ route('leads.appointments.store', $lead) }}">
                        <i class="bi bi-calendar-check"></i>
                    </button>

                    @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
                        <a href="{{ route('leads.edit', $lead) }}" class="btn btn-outline-primary btn-sm ms-2"><i class="bi bi-pencil"></i> Edit</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="mb-4 text-center pb-3 border-bottom border-light">
                    <span class="fs-4 fw-bold text-dark d-block">{{ $lead->client_name }}</span>
                    @if($lead->business_type)
                        <span class="badge bg-light text-secondary border font-monospace mt-1">{{ $lead->business_type }}</span>
                    @endif
                    @if($lead->location)
                        <small class="text-muted d-block mt-1"><i class="bi bi-geo-alt-fill me-1 text-danger"></i>{{ $lead->location }}</small>
                    @endif
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <small class="text-muted d-block fs-8">Source of Lead</small>
                        <span class="fw-semibold text-dark">{{ ucfirst($lead->source ?? 'Other') }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block fs-8">Est. Budget</small>
                        <span class="fw-semibold text-success">₹{{ $lead->estimated_budget ? number_format($lead->estimated_budget, 2) : 'N/A' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block fs-8">Email Address</small>
                        <span class="text-dark">{{ $lead->client_email ?? '—' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block fs-8">Mobile Number</small>
                        <span class="text-dark">{{ $lead->client_phone ?? '—' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block fs-8">Assigned Staff</small>
                        <span class="fw-medium text-primary">{{ $lead->assignedTo->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block fs-8">Next Follow Up</small>
                        <span class="fw-semibold {{ $lead->follow_up_date && \Carbon\Carbon::parse($lead->follow_up_date)->isPast() && $lead->status !== 'converted' ? 'text-danger' : 'text-dark' }}">
                            {{ $lead->follow_up_date ? $lead->follow_up_date->format('d M Y') : '—' }}
                        </span>
                    </div>
                </div>

                <div class="mb-4">
                    <small class="text-muted d-block fs-8 mb-2">Lead Stage</small>
                    @if($lead->status === 'new')
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-2">New Lead</span>
                    @elseif($lead->status === 'interested')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-2">Interested</span>
                    @elseif($lead->status === 'not_interested')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-2">Not Interested</span>
                    @elseif($lead->status === 'call_back_later')
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-2">Call Back Later</span>
                    @elseif($lead->status === 'follow_up_required')
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-2">Follow-up Required</span>
                    @elseif($lead->status === 'converted')
                        <span class="badge bg-success text-white px-3 py-2 rounded-2">Converted</span>
                    @elseif($lead->status === 'closed')
                        <span class="badge bg-dark text-white px-3 py-2 rounded-2">Closed</span>
                    @else
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-2">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</span>
                    @endif
                </div>

                <div class="mb-4 bg-light border rounded-3 p-3">
                    <small class="text-muted d-block fw-semibold mb-1">Requirement Details</small>
                    <div class="fs-7 text-dark" style="white-space: pre-wrap;">{{ $lead->requirement }}</div>
                </div>

                @if($lead->status !== 'converted' && (auth()->user()->isAdminOrAbove() || auth()->user()->isHR()))
                    <hr class="my-4">
                    <div class="d-grid">
                        <form method="POST" action="{{ route('leads.convert', $lead) }}">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2 py-2">
                                <i class="bi bi-arrow-right-circle"></i> Convert to Quotation & Project
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- Associated Quotations -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-text text-secondary me-2"></i>Quotations ({{ $lead->quotations->count() }})</h6></div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($lead->quotations as $quo)
                        <a href="{{ route('quotations.show', $quo) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-3">
                            <div>
                                <div class="fw-semibold text-primary">₹{{ number_format($quo->grand_total ?? 0, 2) }}</div>
                                <small class="text-muted">Quoted on: {{ $quo->created_at->format('d M Y') }}</small>
                            </div>
                            <span class="badge bg-{{ $quo->status === 'approved' ? 'success' : ($quo->status === 'sent' ? 'primary' : 'warning') }}-subtle text-{{ $quo->status === 'approved' ? 'success' : ($quo->status === 'sent' ? 'primary' : 'warning') }} border border-{{ $quo->status === 'approved' ? 'success' : ($quo->status === 'sent' ? 'primary' : 'warning') }}-subtle text-capitalize">
                                {{ $quo->status }}
                            </span>
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted fs-7">No quotations generated yet for this lead.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Timeline / Forms / Requirements -->
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap p-0 border-bottom">
                <ul class="nav nav-tabs card-header-tabs m-0 border-bottom-0" id="leadTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-4 py-3 fw-semibold border-0" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">Timeline & History</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-3 fw-semibold border-0" id="action-tab" data-bs-toggle="tab" data-bs-target="#action" type="button" role="tab">Log Activity</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-4 py-3 fw-semibold border-0" id="requirements-tab" data-bs-toggle="tab" data-bs-target="#requirements" type="button" role="tab">Customer Needs</button>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2 py-2 pe-3">
                    <button type="button" class="btn btn-outline-success btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Log Call" 
                        data-bs-toggle="modal" data-bs-target="#logCallModal" 
                        data-bs-action="{{ route('leads.calls.store', $lead) }}"
                        data-bs-client-name="{{ $lead->client_name }}"
                        data-bs-client-phone="{{ $lead->client_phone ?? '—' }}"
                        data-bs-calls-count="{{ $lead->calls->count() }}"
                        data-bs-last-contacted-at="{{ $lead->latestCall ? $lead->latestCall->call_date_time->format('d M Y, h:i A') : '' }}"
                        data-bs-first-remarks="{{ $lead->calls->sortBy('id')->first() ? e($lead->calls->sortBy('id')->first()->remarks) : '' }}">
                        <i class="bi bi-telephone-outbound"></i>
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Schedule Follow-up" 
                        data-bs-toggle="modal" data-bs-target="#scheduleFollowUpModal" 
                        data-bs-action="{{ route('leads.follow-up', $lead) }}">
                        <i class="bi bi-clock"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Book Appointment" 
                        data-bs-toggle="modal" data-bs-target="#bookAppointmentModal" 
                        data-bs-action="{{ route('leads.appointments.store', $lead) }}">
                        <i class="bi bi-calendar-check"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="leadTabsContent">
                    <!-- Tab 1: Timeline & History -->
                    <div class="tab-pane fade show active" id="history" role="tabpanel">
                        <!-- Calls History -->
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-telephone-inbound-fill me-2 text-success"></i>Call Logs History</h6>
                        <div class="mb-4">
                            @forelse($lead->calls as $call)
                                <div class="bg-light rounded-3 p-3 mb-3 border">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary text-white">{{ $call->status }}</span>
                                            <small class="text-muted">{{ $call->call_date_time->format('d M Y, h:i A') }}</small>
                                        </div>
                                        <small class="text-primary font-monospace" style="font-size:11px;">Caller: {{ $call->telecaller->name }}</small>
                                    </div>
                                    @if($call->customer_response)
                                        <div class="text-dark fs-7 mb-1"><strong>Response:</strong> {{ $call->customer_response }}</div>
                                    @endif
                                    @if($call->remarks)
                                        <div class="text-secondary fs-8"><strong>Remarks:</strong> {{ $call->remarks }}</div>
                                    @endif
                                    @if($call->next_action)
                                        <div class="text-info fs-8 mt-1"><strong>Next Action:</strong> {{ $call->next_action }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted fs-7 mb-4">No calls logged yet.</p>
                            @endforelse
                        </div>

                        <!-- Appointments List -->
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-calendar-check-fill me-2 text-primary"></i>Scheduled Appointments</h6>
                        <div class="mb-4">
                            @forelse($lead->appointments as $app)
                                <div class="bg-light rounded-3 p-3 mb-3 border">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $app->type }}</span>
                                            <small class="text-muted">{{ $app->meeting_date_time->format('d M Y, h:i A') }}</small>
                                        </div>
                                        <span class="badge bg-{{ $app->status === 'scheduled' ? 'warning' : ($app->status === 'completed' ? 'success' : 'danger') }}-subtle text-{{ $app->status === 'scheduled' ? 'warning' : ($app->status === 'completed' ? 'success' : 'danger') }}">
                                            {{ ucfirst($app->status) }}
                                        </span>
                                    </div>
                                    <div class="text-dark fs-7 mb-1"><strong>Sales Executive:</strong> {{ $app->salesExecutive->name }}</div>
                                    @if($app->notes)
                                        <div class="text-secondary fs-8"><strong>Notes:</strong> {{ $app->notes }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted fs-7 mb-4">No appointments scheduled.</p>
                            @endforelse
                        </div>

                        <!-- General Follow-up Timeline -->
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history me-2 text-warning"></i>Interaction Notes & Follow-ups</h6>
                        <div class="timeline-logs">
                            @forelse($lead->followUps as $fu)
                                <div class="d-flex gap-3 mb-4 pb-3 border-bottom border-light">
                                    <div class="flex-shrink-0">
                                        <img src="{{ $fu->user->avatar_url }}" alt="" class="avatar-circle" style="width:36px; height:36px;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="fw-semibold fs-7">{{ $fu->user->name }}</div>
                                            <small class="text-muted" style="font-size:11px;">{{ $fu->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1 text-muted fs-7 mt-1">{{ $fu->note }}</p>
                                        @if($fu->next_follow_up)
                                            <span class="badge bg-light text-dark border font-monospace fs-8" style="font-size:10px;">
                                                Next: {{ \Carbon\Carbon::parse($fu->next_follow_up)->format('d M Y') }}
                                                @if($fu->follow_up_time)
                                                    • {{ Carbon\Carbon::parse($fu->follow_up_time)->format('h:i A') }}
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-chat-left-text" style="font-size: 24px;"></i>
                                    <div class="mt-2 fs-7">No follow-ups recorded yet.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tab 2: Log Activity -->
                    <div class="tab-pane fade" id="action" role="tabpanel">
                        <div class="row g-3 py-2">
                            <div class="col-12 col-md-4">
                                <div class="card h-100 border text-center p-4 hover-shadow-sm transition-all" style="cursor: pointer;" 
                                     data-bs-toggle="modal" data-bs-target="#logCallModal" 
                                     data-bs-action="{{ route('leads.calls.store', $lead) }}"
                                     data-bs-client-name="{{ $lead->client_name }}"
                                     data-bs-client-phone="{{ $lead->client_phone ?? '—' }}"
                                     data-bs-calls-count="{{ $lead->calls->count() }}"
                                     data-bs-last-contacted-at="{{ $lead->latestCall ? $lead->latestCall->call_date_time->format('d M Y, h:i A') : '' }}"
                                     data-bs-first-remarks="{{ $lead->calls->sortBy('id')->first() ? e($lead->calls->sortBy('id')->first()->remarks) : '' }}">
                                    <div class="fs-1 text-success mb-3">
                                        <i class="bi bi-telephone-outbound"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2">Log Call</h6>
                                    <p class="text-secondary fs-7.5 mb-0">Record call details, update lead stage, and document customer response.</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card h-100 border text-center p-4 hover-shadow-sm transition-all" style="cursor: pointer;" 
                                     data-bs-toggle="modal" data-bs-target="#scheduleFollowUpModal" 
                                     data-bs-action="{{ route('leads.follow-up', $lead) }}">
                                    <div class="fs-1 text-warning mb-3">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2">Schedule Follow-up</h6>
                                    <p class="text-secondary fs-7.5 mb-0">Schedule the next follow-up date and time with reminder notes.</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card h-100 border text-center p-4 hover-shadow-sm transition-all" style="cursor: pointer;" 
                                     data-bs-toggle="modal" data-bs-target="#bookAppointmentModal" 
                                     data-bs-action="{{ route('leads.appointments.store', $lead) }}">
                                    <div class="fs-1 text-primary mb-3">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2">Book Appointment</h6>
                                    <p class="text-secondary fs-7.5 mb-0">Book a formal meeting or demo and assign it to a sales executive.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Customer Requirements -->
                    <div class="tab-pane fade" id="requirements" role="tabpanel">
                        <form method="POST" action="{{ route('leads.requirements.update', $lead) }}" class="row g-3">
                            @csrf
                            <div class="col-12 col-md-8">
                                <label class="form-label fs-7 fw-semibold text-secondary">Service Required</label>
                                <input type="text" name="service_required" class="form-control form-control-sm" value="{{ $lead->service_required }}" placeholder="e.g. Custom Web App Development or SEO Campaign">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fs-7 fw-semibold text-secondary">Budget (INR)</label>
                                <input type="number" step="0.01" name="estimated_budget" class="form-control form-control-sm" value="{{ $lead->estimated_budget }}" placeholder="0.00">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fs-7 fw-semibold text-secondary">Preferred Project Date</label>
                                <input type="date" name="preferred_date" class="form-control form-control-sm" value="{{ $lead->preferred_date ? $lead->preferred_date->toDateString() : '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fs-7 fw-semibold text-secondary">Company / Business Details</label>
                                <textarea name="company_details" class="form-control form-control-sm" rows="3" placeholder="Describe the company's business type, scale, or industry details...">{{ $lead->company_details }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fs-7 fw-semibold text-secondary">Requirement Details & Notes</label>
                                <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Collect extra notes, custom needs, or client specifications...">{{ $lead->notes }}</textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Save Requirements</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('leads.modals')
@endsection
