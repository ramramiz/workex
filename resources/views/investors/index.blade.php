@extends('layouts.app')

@section('title', 'Investors')
@section('page-title', 'Investors')

@section('breadcrumb')
    <li class="breadcrumb-item active">Investors</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Company Investors</h5>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInvestorModal" style="border-radius: 8px;">
        <i class="bi bi-plus-circle me-1"></i> Add Investor
    </button>
</div>

{{-- Investor List Table --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead class="table-light">
                <tr>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Investor Name</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Email</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Phone</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Opening Balance</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Status</th>
                    <th class="py-3 text-secondary text-end" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($investors as $investor)
                    <tr>
                        <td class="fw-semibold text-dark">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <span class="d-block">{{ $investor->name }}</span>
                                    @if($investor->description)
                                        <small class="text-muted fw-normal d-block text-truncate" style="max-width: 250px;">{{ $investor->description }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $investor->email ?? '—' }}</td>
                        <td>{{ $investor->phone ?? '—' }}</td>
                        <td class="fw-medium text-dark">₹{{ number_format($investor->opening_balance, 2) }}</td>
                        <td>
                            @if($investor->status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('investors.show', $investor) }}" class="btn btn-outline-info btn-sm" 
                                   style="border-radius: 6px;" title="View Statement">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" class="btn btn-outline-primary btn-sm btn-edit-investor" 
                                        style="border-radius: 6px;"
                                        data-id="{{ $investor->id }}"
                                        data-name="{{ $investor->name }}"
                                        data-email="{{ $investor->email }}"
                                        data-phone="{{ $investor->phone }}"
                                        data-description="{{ $investor->description }}"
                                        data-opening_balance="{{ $investor->opening_balance }}"
                                        data-status="{{ $investor->status }}"
                                        onclick="openEditModal(this)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('investors.destroy', $investor) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this investor?')">
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
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people" style="font-size: 36px;"></i>
                            <div class="mt-2">No investors registered.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($investors->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $investors->links() }}
        </div>
    @endif
</div>

{{-- Add Investor Modal --}}
<div class="modal fade" id="addInvestorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Add Investor</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('investors.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Investor Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. john@example.com" style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. +91 9999999999" style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Opening Balance</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">₹</span>
                            <input type="number" step="0.01" min="0" name="opening_balance" class="form-control" placeholder="0.00" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Description / Remarks</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief note about the investor..." style="border-radius:8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Save Investor</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Investor Modal --}}
<div class="modal fade" id="editInvestorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Investor</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editInvestorForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Investor Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-name" class="form-control" required style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Email Address</label>
                        <input type="email" name="email" id="edit-email" class="form-control" style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Phone Number</label>
                        <input type="text" name="phone" id="edit-phone" class="form-control" style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Opening Balance</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">₹</span>
                            <input type="number" step="0.01" min="0" name="opening_balance" id="edit-opening_balance" class="form-control" placeholder="0.00" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Description / Remarks</label>
                        <textarea name="description" id="edit-description" class="form-control" rows="3" style="border-radius:8px;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit-status" class="form-select" required style="border-radius:8px;">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Update Investor</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openEditModal(button) {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const email = button.getAttribute('data-email');
        const phone = button.getAttribute('data-phone');
        const description = button.getAttribute('data-description');
        const opening_balance = button.getAttribute('data-opening_balance');
        const status = button.getAttribute('data-status');

        document.getElementById('edit-name').value = name;
        document.getElementById('edit-email').value = email || '';
        document.getElementById('edit-phone').value = phone || '';
        document.getElementById('edit-description').value = description || '';
        document.getElementById('edit-opening_balance').value = opening_balance || '0.00';
        document.getElementById('edit-status').value = status;

        const form = document.getElementById('editInvestorForm');
        form.action = `/investors/${id}`;

        const modal = new bootstrap.Modal(document.getElementById('editInvestorModal'));
        modal.show();
    }
</script>
@endpush
@endsection
