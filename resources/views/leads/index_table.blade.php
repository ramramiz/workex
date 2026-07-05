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
                        <div class="fw-semibold text-dark">{{ $lead->client_name }}</div>
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
                                data-bs-action="{{ route('leads.calls.store', $lead) }}"
                                data-bs-client-name="{{ $lead->client_name }}"
                                data-bs-client-phone="{{ $lead->client_phone ?? '—' }}"
                                data-bs-calls-count="{{ $lead->calls->count() }}"
                                data-bs-last-contacted-at="{{ $lead->latestCall ? $lead->latestCall->call_date_time->format('d M Y, h:i A') : '' }}"
                                data-bs-first-remarks="{{ $lead->calls->sortBy('id')->first() ? e($lead->calls->sortBy('id')->first()->remarks) : '' }}">
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
