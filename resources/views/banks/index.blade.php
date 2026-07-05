@extends('layouts.app')

@section('title', 'Bank Accounts')
@section('page-title', 'Bank Accounts')

@section('breadcrumb')
    <li class="breadcrumb-item active">Banks</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Company Bank Accounts</h5>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBankModal" style="border-radius: 8px;">
        <i class="bi bi-plus-circle me-1"></i> Add Bank Account
    </button>
</div>

{{-- Bank List Table --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead class="table-light">
                <tr>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Bank Name</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Account Holder</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Account Number</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">IFSC / Branch</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Opening Balance</th>
                    <th class="py-3 text-secondary" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Status</th>
                    <th class="py-3 text-secondary text-end" style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banks as $bank)
                    <tr>
                        <td class="fw-semibold text-dark">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                    <i class="bi bi-bank"></i>
                                </div>
                                <span>{{ $bank->name }}</span>
                            </div>
                        </td>
                        <td>{{ $bank->account_name ?? '—' }}</td>
                        <td class="font-monospace text-muted">{{ $bank->account_number ?? '—' }}</td>
                        <td>
                            @if($bank->ifsc_code)
                                <span class="badge bg-light text-secondary border font-monospace">{{ $bank->ifsc_code }}</span>
                            @endif
                            <small class="text-muted d-block mt-0.5">{{ $bank->branch ?? '—' }}</small>
                        </td>
                        <td class="fw-medium text-dark">₹{{ number_format($bank->opening_balance, 2) }}</td>
                        <td>
                            @if($bank->status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('banks.show', $bank) }}" class="btn btn-outline-info btn-sm" 
                                   style="border-radius: 6px;" title="View Statement">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" class="btn btn-outline-primary btn-sm btn-edit-bank" 
                                        style="border-radius: 6px;"
                                        data-id="{{ $bank->id }}"
                                        data-name="{{ $bank->name }}"
                                        data-account_name="{{ $bank->account_name }}"
                                        data-account_number="{{ $bank->account_number }}"
                                        data-ifsc_code="{{ $bank->ifsc_code }}"
                                        data-branch="{{ $bank->branch }}"
                                        data-opening_balance="{{ $bank->opening_balance }}"
                                        data-status="{{ $bank->status }}"
                                        onclick="openEditModal(this)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route('banks.destroy', $bank) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this bank account?')">
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
                            <i class="bi bi-bank" style="font-size: 36px;"></i>
                            <div class="mt-2">No bank accounts registered.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($banks->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $banks->links() }}
        </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════
     Add Bank Modal
══════════════════════════════════════════════ --}}
<div class="modal fade" id="addBankModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-bank me-2"></i>Add Bank Account</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('banks.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. HDFC Bank" required style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Account Holder Name</label>
                        <input type="text" name="account_name" class="form-control" placeholder="e.g. Company Private Limited" style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Account Number</label>
                        <input type="text" name="account_number" class="form-control" placeholder="e.g. 5010023847586" style="border-radius:8px;">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">IFSC Code</label>
                            <input type="text" name="ifsc_code" class="form-control" placeholder="e.g. HDFC0001234" style="border-radius:8px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Branch Name</label>
                            <input type="text" name="branch" class="form-control" placeholder="e.g. Connaught Place" style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Opening Balance</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">₹</span>
                            <input type="number" step="0.01" min="0" name="opening_balance" class="form-control" placeholder="0.00" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Save Bank</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     Edit Bank Modal
══════════════════════════════════════════════ --}}
<div class="modal fade" id="editBankModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Bank Account</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBankForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-name" class="form-control" required style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Account Holder Name</label>
                        <input type="text" name="account_name" id="edit-account_name" class="form-control" style="border-radius:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Account Number</label>
                        <input type="text" name="account_number" id="edit-account_number" class="form-control" style="border-radius:8px;">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">IFSC Code</label>
                            <input type="text" name="ifsc_code" id="edit-ifsc_code" class="form-control" style="border-radius:8px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-secondary">Branch Name</label>
                            <input type="text" name="branch" id="edit-branch" class="form-control" style="border-radius:8px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Opening Balance</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">₹</span>
                            <input type="number" step="0.01" min="0" name="opening_balance" id="edit-opening_balance" class="form-control" placeholder="0.00" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-medium text-secondary">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit-status" class="form-select" required style="border-radius:8px;">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Update Bank</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditModal(button) {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const account_name = button.dataset.account_name;
        const account_number = button.dataset.account_number;
        const ifsc_code = button.dataset.ifsc_code;
        const branch = button.dataset.branch;
        const opening_balance = button.dataset.opening_balance;
        const status = button.dataset.status;

        // Set inputs
        document.getElementById('edit-name').value = name || '';
        document.getElementById('edit-account_name').value = account_name || '';
        document.getElementById('edit-account_number').value = account_number || '';
        document.getElementById('edit-ifsc_code').value = ifsc_code || '';
        document.getElementById('edit-branch').value = branch || '';
        document.getElementById('edit-opening_balance').value = opening_balance || '0.00';
        document.getElementById('edit-status').value = status || 'active';

        // Set action url
        document.getElementById('editBankForm').action = "/banks/" + id;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editBankModal'));
        modal.show();
    }
</script>
@endpush
