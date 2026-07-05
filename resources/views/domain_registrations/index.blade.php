@extends('layouts.app')

@section('title', 'Domain Registrations')
@section('page-title', 'Domain Registrations')

@section('breadcrumb')
    <li class="breadcrumb-item active">Domain Registrations</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Domain Registrations Credentials</h5>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDomainModal" style="border-radius: 8px;">
        <i class="bi bi-plus-circle me-1"></i> Add Domain Registration
    </button>
</div>

{{-- Domain Registration List Table --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead class="table-light">
                <tr>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Provider Name</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Control Panel URL</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Username</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Password</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Renewal Date</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Notes</th>
                    <th class="py-3 text-secondary text-end" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($domainRegistrations as $dr)
                    <tr>
                        <td class="fw-semibold text-dark">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                    <i class="bi bi-globe"></i>
                                </div>
                                <span>{{ $dr->name }}</span>
                            </div>
                        </td>
                        <td>
                            @if($dr->url)
                                <a href="{{ $dr->url }}" target="_blank" class="text-truncate d-inline-block text-primary" style="max-width: 200px;">
                                    {{ $dr->url }} <i class="bi bi-box-arrow-up-right fs-9"></i>
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="font-monospace text-muted">{{ $dr->username ?? '—' }}</td>
                        <td>
                            @if($dr->password)
                                <div class="input-group input-group-sm" style="max-width: 160px;">
                                    <input type="password" class="form-control border-end-0 bg-transparent font-monospace p-1" value="{{ $dr->password }}" readonly style="font-size: 11px; border-radius: 4px 0 0 4px;">
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePasswordVisibility(this)" style="border-radius: 0 4px 4px 0;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($dr->renewal_date)
                                <span class="fw-medium text-dark">{{ $dr->renewal_date->format('d M Y') }}</span>
                                @php
                                    $drDaysLeft = now()->diffInDays($dr->renewal_date, false);
                                    $drBadge = $drDaysLeft < 30 ? 'danger' : ($drDaysLeft < 90 ? 'warning' : 'success');
                                @endphp
                                <span class="badge bg-{{ $drBadge }}-subtle text-{{ $drBadge }} border border-{{ $drBadge }}-subtle fs-9 d-block mt-1" style="width: fit-content;">
                                    {{ $drDaysLeft }} days left
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-truncate d-inline-block text-muted" style="max-width: 250px;" title="{{ $dr->notes }}">
                                {{ $dr->notes ?? '—' }}
                            </small>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        style="border-radius: 6px;"
                                        data-id="{{ $dr->id }}"
                                        data-name="{{ $dr->name }}"
                                        data-url="{{ $dr->url }}"
                                        data-username="{{ $dr->username }}"
                                        data-password="{{ $dr->password }}"
                                        data-renewal_date="{{ $dr->renewal_date ? $dr->renewal_date->format('Y-m-d') : '' }}"
                                        data-notes="{{ $dr->notes }}"
                                        onclick="openEditModal(this)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('domain-registrations.destroy', $dr) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this domain registration?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius: 6px;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-globe" style="font-size: 36px;"></i>
                            <div class="mt-2">No domain registrations registered.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($domainRegistrations->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $domainRegistrations->links() }}
        </div>
    @endif
</div>

{{-- Add Domain Registration Modal --}}
<div class="modal fade" id="addDomainModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-globe me-2"></i>Add Domain Registration</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('domain-registrations.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Domain Provider Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Godaddy, Namecheap" required style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Control Panel URL</label>
                        <input type="url" name="url" class="form-control" placeholder="e.g. https://godaddy.com" style="border-radius:8px;">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="e.g. admin_domain" style="border-radius:8px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Password</label>
                            <input type="text" name="password" class="form-control" placeholder="e.g. strong_pass_123" style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Renewal Date</label>
                        <input type="date" name="renewal_date" class="form-control" style="border-radius:8px;">
                    </div>
                    <div>
                        <label class="form-label fw-medium text-secondary">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional details..." style="border-radius:8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Save Provider</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Domain Registration Modal --}}
<div class="modal fade" id="editDomainModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Domain Registration</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDomainForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Domain Provider Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-name" class="form-control" required style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Control Panel URL</label>
                        <input type="url" name="url" id="edit-url" class="form-control" style="border-radius:8px;">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Username</label>
                            <input type="text" name="username" id="edit-username" class="form-control" style="border-radius:8px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Password</label>
                            <input type="text" name="password" id="edit-password" class="form-control" style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Renewal Date</label>
                        <input type="date" name="renewal_date" id="edit-renewal_date" class="form-control" style="border-radius:8px;">
                    </div>
                    <div>
                        <label class="form-label fw-medium text-secondary">Notes</label>
                        <textarea name="notes" id="edit-notes" class="form-control" rows="3" style="border-radius:8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Update Provider</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(button) {
        const input = button.parentElement.querySelector('input');
        const icon = button.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    function openEditModal(button) {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const url = button.dataset.url;
        const username = button.dataset.username;
        const password = button.dataset.password;
        const renewal_date = button.dataset.renewal_date;
        const notes = button.dataset.notes;

        document.getElementById('edit-name').value = name || '';
        document.getElementById('edit-url').value = url || '';
        document.getElementById('edit-username').value = username || '';
        document.getElementById('edit-password').value = password || '';
        document.getElementById('edit-renewal_date').value = renewal_date || '';
        document.getElementById('edit-notes').value = notes || '';

        document.getElementById('editDomainForm').action = "/domain-registrations/" + id;

        const modal = new bootstrap.Modal(document.getElementById('editDomainModal'));
        modal.show();
    }
</script>
@endpush
