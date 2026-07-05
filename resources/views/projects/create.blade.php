@extends('layouts.app')

@section('title', 'Add Project')
@section('page-title', 'Add Project')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item active">Add Project</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create Project Board</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data">
                    @csrf

                    @if(request('quotation_id'))
                        <input type="hidden" name="quotation_id" value="{{ request('quotation_id') }}">
                    @endif

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Project Identity</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Project Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. E-Commerce Development">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Project Type</label>
                            <select name="project_type" id="project_type_select" class="form-select @error('project_type') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Choose Project Type --</option>
                                <option value="new_type" class="text-primary fw-bold">+ Add New Type...</option>
                                @php
                                    $defaultTypes = [
                                        'web' => 'Web Application',
                                        'mobile' => 'Mobile Application',
                                        'desktop' => 'Desktop Software',
                                        'other' => 'Other Services'
                                    ];
                                    $allTypes = [];
                                    foreach($defaultTypes as $val => $label) {
                                        $allTypes[$val] = $label;
                                    }
                                    if (isset($projectTypes)) {
                                        foreach($projectTypes as $type) {
                                            if (!empty($type) && !isset($allTypes[$type])) {
                                                $allTypes[$type] = ucwords(str_replace('_', ' ', $type));
                                            }
                                        }
                                    }
                                @endphp
                                @foreach($allTypes as $val => $label)
                                    <option value="{{ $val }}" {{ old('project_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('project_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Project Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Provide a brief summary of project requirements and goals...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Project Logo</label>
                            <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Project URL (Optional)</label>
                            <input type="text" name="url" class="form-control @error('url') is-invalid @enderror" placeholder="e.g. https://myproject.com" value="{{ old('url') }}">
                            @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Domain Provider</label>
                            <select name="domain_registration_id" class="form-select @error('domain_registration_id') is-invalid @enderror">
                                <option value="">-- Choose Domain Provider --</option>
                                @foreach($domainRegistrations as $dr)
                                    <option value="{{ $dr->id }}" data-renewal="{{ $dr->renewal_date ? $dr->renewal_date->format('Y-m-d') : '' }}" {{ old('domain_registration_id') == $dr->id ? 'selected' : '' }}>
                                        {{ $dr->name }}{{ $dr->username ? ' - ' . $dr->username : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('domain_registration_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Domain Valid Till</label>
                            <input type="date" name="domain_valid_till" class="form-control @error('domain_valid_till') is-invalid @enderror" value="{{ old('domain_valid_till') }}">
                            @error('domain_valid_till')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Hosting Provider</label>
                            <select name="hosting_provider_id" class="form-select @error('hosting_provider_id') is-invalid @enderror">
                                <option value="">-- Choose Hosting Provider --</option>
                                @foreach($hostingProviders as $hp)
                                    <option value="{{ $hp->id }}" data-renewal="{{ $hp->renewal_date ? $hp->renewal_date->format('Y-m-d') : '' }}" {{ old('hosting_provider_id') == $hp->id ? 'selected' : '' }}>
                                        {{ $hp->name }}{{ $hp->username ? ' - ' . $hp->username : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('hosting_provider_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Hosting Valid Till</label>
                            <input type="date" name="hosting_valid_till" class="form-control @error('hosting_valid_till') is-invalid @enderror" value="{{ old('hosting_valid_till') }}">
                            @error('hosting_valid_till')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Client & Leader Alignment</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Select Client</label>
                            <select name="client_id" id="project_client_select" class="form-select @error('client_id') is-invalid @enderror">
                                <option value="">-- Choose Client --</option>
                                <option value="">-- Internal Project (No client linked) --</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                @endforeach
                                <option value="new_client" class="text-primary fw-bold">+ Add New Client...</option>
                            </select>
                            @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Team Leader / Project Manager</label>
                            <select name="team_leader_id" id="project_team_leader_select" class="form-select @error('team_leader_id') is-invalid @enderror">
                                <option value="">-- Choose Team Leader / Project Manager --</option>
                                @foreach($teamLeaders as $tl)
                                    <option value="{{ $tl->id }}" {{ old('team_leader_id') == $tl->id ? 'selected' : '' }}>{{ $tl->name }}</option>
                                @endforeach
                                <option value="new_team_leader" class="text-primary fw-bold">+ Add New Team Leader...</option>
                            </select>
                            @error('team_leader_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Financials & Schedule</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Project Budget (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="budget" class="form-control @error('budget') is-invalid @enderror" value="{{ old('budget') }}" placeholder="0.00">
                            </div>
                            @error('budget')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', date('Y-m-d')) }}">
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Deadline / Due Date</label>
                            <input type="date" name="deadline" class="form-control @error('deadline') is-invalid @enderror" value="{{ old('deadline') }}">
                            @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Technologies Used <span class="text-muted">(Comma separated tags)</span></label>
                            <input type="text" name="technologies" class="form-control @error('technologies') is-invalid @enderror" value="{{ old('technologies') }}" placeholder="e.g. PHP, Laravel, MySQL, Bootstrap">
                            @error('technologies')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2 mt-4">Project AMC Contract (Optional)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label">AMC Start Date</label>
                            <input type="date" name="amc_start_date" id="amc_start_date" class="form-control @error('amc_start_date') is-invalid @enderror" value="{{ old('amc_start_date') }}">
                            @error('amc_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Billing Frequency <span class="text-danger">*</span></label>
                            <select name="amc_frequency" id="amc_frequency" class="form-select @error('amc_frequency') is-invalid @enderror" required>
                                <option value="annually" {{ old('amc_frequency', 'annually') === 'annually' ? 'selected' : '' }}>Annually</option>
                                <option value="semi-annually" {{ old('amc_frequency') === 'semi-annually' ? 'selected' : '' }}>Semi-Annually</option>
                                <option value="quarterly" {{ old('amc_frequency') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="monthly" {{ old('amc_frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                            @error('amc_frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">AMC Value (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="amc_amount" class="form-control @error('amc_amount') is-invalid @enderror" value="{{ old('amc_amount') }}" placeholder="0.00">
                            </div>
                            @error('amc_amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">AMC Due Date</label>
                            <input type="date" name="amc_end_date" id="amc_due_date" class="form-control @error('amc_end_date') is-invalid @enderror" value="{{ old('amc_end_date') }}" disabled>
                            @error('amc_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Contract Status <span class="text-danger">*</span></label>
                            <select name="amc_status" class="form-select @error('amc_status') is-invalid @enderror" required>
                                <option value="active" {{ old('amc_status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending_renewal" {{ old('amc_status') === 'pending_renewal' ? 'selected' : '' }}>Pending Renewal</option>
                                <option value="expired" {{ old('amc_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                            @error('amc_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks / Description</label>
                            <textarea name="amc_remarks" class="form-control @error('amc_remarks') is-invalid @enderror" rows="3" placeholder="Contract conditions, notes...">{{ old('amc_remarks') }}</textarea>
                            @error('amc_remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientModalLabel"><i class="bi bi-building me-2 text-primary"></i>Add New Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickClientForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control" placeholder="e.g. Acme Corporation" required>
                        <div class="invalid-feedback" id="client_company_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Contact Person <span class="text-danger">*</span></label>
                        <input type="text" name="contact_person" class="form-control" placeholder="e.g. Robert Smith" required>
                        <div class="invalid-feedback" id="client_contact_person_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. robert@acme.com" required>
                        <div class="invalid-feedback" id="client_email_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="e.g. 9876543210">
                        <div class="invalid-feedback" id="client_phone_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveClientBtn">Save Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Team Leader Modal -->
<div class="modal fade" id="addTeamLeaderModal" tabindex="-1" aria-labelledby="addTeamLeaderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTeamLeaderModalLabel"><i class="bi bi-people me-2 text-primary"></i>Add New Team Leader</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickTeamLeaderForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
                        <div class="invalid-feedback" id="tl_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. john@company.com" required>
                        <div class="invalid-feedback" id="tl_email_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
                        <div class="invalid-feedback" id="tl_password_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Re-type password" required>
                        <div class="invalid-feedback" id="tl_password_confirmation_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveTLBtn">Save Team Leader</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Project Type Modal -->
<div class="modal fade" id="addProjectTypeModal" tabindex="-1" aria-labelledby="addProjectTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProjectTypeModalLabel"><i class="bi bi-tag me-2 text-primary"></i>Add Custom Project Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickProjectTypeForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Project Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="new_project_type" id="new_project_type" class="form-control" placeholder="e.g. API Integration" required>
                        <div class="invalid-feedback" id="new_project_type_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveProjectTypeBtn">Add Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // AMC Due Date dynamic enabling and calculation
    const amcStartInput = document.getElementById('amc_start_date');
    const amcFreqSelect = document.getElementById('amc_frequency');
    const amcDueInput = document.getElementById('amc_due_date');

    function checkEnableAmcDue() {
        if (amcStartInput && amcFreqSelect && amcDueInput) {
            if (amcStartInput.value && amcFreqSelect.value) {
                amcDueInput.disabled = false;
                
                // Auto-calculate default due date if it is empty
                if (!amcDueInput.value) {
                    const startVal = new Date(amcStartInput.value);
                    if (!isNaN(startVal.getTime())) {
                        let endVal = new Date(startVal);
                        const freq = amcFreqSelect.value;
                        
                        if (freq === 'annually') {
                            endVal.setFullYear(endVal.getFullYear() + 1);
                        } else if (freq === 'semi-annually') {
                            endVal.setMonth(endVal.getMonth() + 6);
                        } else if (freq === 'quarterly') {
                            endVal.setMonth(endVal.getMonth() + 3);
                        } else if (freq === 'monthly') {
                            endVal.setMonth(endVal.getMonth() + 1);
                        }
                        
                        endVal.setDate(endVal.getDate() - 1);
                        
                        const yyyy = endVal.getFullYear();
                        const mm = String(endVal.getMonth() + 1).padStart(2, '0');
                        const dd = String(endVal.getDate()).padStart(2, '0');
                        amcDueInput.value = `${yyyy}-${mm}-${dd}`;
                    }
                }
            } else {
                amcDueInput.disabled = true;
            }
        }
    }

    if (amcStartInput && amcFreqSelect) {
        amcStartInput.addEventListener('change', checkEnableAmcDue);
        amcFreqSelect.addEventListener('change', function() {
            // Force recalculate if frequency changes and start date is set
            if (amcStartInput.value) {
                amcDueInput.value = '';
            }
            checkEnableAmcDue();
        });
        checkEnableAmcDue();
    }

    // Dropdowns selectors
    const clientSelect = document.getElementById('project_client_select');
    const tlSelect = document.getElementById('project_team_leader_select');

    let lastClientVal = clientSelect.value;
    let lastTlVal = tlSelect.value;

    const projectTypeSelect = document.getElementById('project_type_select');
    let lastTypeVal = projectTypeSelect ? projectTypeSelect.value : '';

    if (projectTypeSelect) {
        projectTypeSelect.addEventListener('change', function() {
            if (this.value === 'new_type') {
                this.value = lastTypeVal; // Reset to previous selection
                const addProjectTypeModal = new bootstrap.Modal(document.getElementById('addProjectTypeModal'));
                addProjectTypeModal.show();
            } else {
                lastTypeVal = this.value;
            }
        });
    }

    const quickProjectTypeForm = document.getElementById('quickProjectTypeForm');
    if (quickProjectTypeForm) {
        quickProjectTypeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            resetValidation(quickProjectTypeForm);
            const newVal = document.getElementById('new_project_type').value.trim();
            
            if (newVal.length > 0) {
                const select = document.getElementById('project_type_select');
                if (select) {
                    const newOpt = new Option(newVal, newVal, true, true);
                    if (select.options.length > 2) {
                        select.add(newOpt, select.options[2]);
                    } else {
                        select.add(newOpt);
                    }
                    select.value = newVal;
                    lastTypeVal = newVal;
                }
                
                const modalEl = document.getElementById('addProjectTypeModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                quickProjectTypeForm.reset();
            }
        });
    }

    // Monitor Client dropdown change
    clientSelect.addEventListener('change', function() {
        if (this.value === 'new_client') {
            this.value = lastClientVal; // Reset to previous selection
            const addClientModal = new bootstrap.Modal(document.getElementById('addClientModal'));
            addClientModal.show();
        } else {
            lastClientVal = this.value;
        }
    });

    // Monitor Team Leader dropdown change
    tlSelect.addEventListener('change', function() {
        if (this.value === 'new_team_leader') {
            this.value = lastTlVal; // Reset to previous selection
            const addTeamLeaderModal = new bootstrap.Modal(document.getElementById('addTeamLeaderModal'));
            addTeamLeaderModal.show();
        } else {
            lastTlVal = this.value;
        }
    });

    // Reset validation errors
    function resetValidation(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    // Show validation errors
    function showErrors(form, errors, errorPrefix) {
        for (const field in errors) {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
            }
            const feedback = form.querySelector(`#${errorPrefix}_${field}_error`);
            if (feedback) {
                feedback.textContent = errors[field][0];
            }
        }
    }

    // Submit Client Form via AJAX
    const clientForm = document.getElementById('quickClientForm');
    clientForm.addEventListener('submit', function(e) {
        e.preventDefault();
        resetValidation(clientForm);
        const saveBtn = document.getElementById('saveClientBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        const formData = new FormData(clientForm);

        fetch("{{ route('clients.quick-store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(res => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Client';

            if (res.status === 422) {
                showErrors(clientForm, res.body.errors, 'client');
            } else if (res.body.success) {
                // Success! Add option to client dropdown
                const client = res.body.client;
                const newOpt = new Option(client.company_name, client.id, true, true);
                
                // Insert option before the "+ Add New Client..." option
                const newClientOpt = clientSelect.querySelector('option[value="new_client"]');
                clientSelect.insertBefore(newOpt, newClientOpt);

                // Update tracked value
                lastClientVal = client.id;
                clientSelect.value = client.id;

                // Close Modal
                const modalEl = document.getElementById('addClientModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                clientForm.reset();
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Client';
            alert('Something went wrong. Please try again.');
            console.error(err);
        });
    });

    // Submit Team Leader Form via AJAX
    const tlForm = document.getElementById('quickTeamLeaderForm');
    tlForm.addEventListener('submit', function(e) {
        e.preventDefault();
        resetValidation(tlForm);
        const saveBtn = document.getElementById('saveTLBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        const formData = new FormData(tlForm);

        fetch("{{ route('users.quick-store-team-leader') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(res => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Team Leader';

            if (res.status === 422) {
                showErrors(tlForm, res.body.errors, 'tl');
            } else if (res.body.success) {
                // Success! Add option to team leader dropdown
                const tl = res.body.team_leader;
                const newOpt = new Option(tl.name, tl.id, true, true);
                
                // Insert option before the "+ Add New Team Leader..." option
                const newTlOpt = tlSelect.querySelector('option[value="new_team_leader"]');
                tlSelect.insertBefore(newOpt, newTlOpt);

                // Update tracked value
                lastTlVal = tl.id;
                tlSelect.value = tl.id;

                // Close Modal
                const modalEl = document.getElementById('addTeamLeaderModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                tlForm.reset();
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Team Leader';
            alert('Something went wrong. Please try again.');
            console.error(err);
        });
    });

    // Auto-update renewal/validity dates based on selected provider
    const hostingProviderSelect = document.querySelector('select[name="hosting_provider_id"]');
    const hostingValidTillInput = document.querySelector('input[name="hosting_valid_till"]');
    const domainRegistrationSelect = document.querySelector('select[name="domain_registration_id"]');
    const domainValidTillInput = document.querySelector('input[name="domain_valid_till"]');

    function updateHostingValidity(isInitial = false) {
        const selectedOption = hostingProviderSelect.options[hostingProviderSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const renewalDate = selectedOption.getAttribute('data-renewal');
            if (!isInitial || !hostingValidTillInput.value) {
                hostingValidTillInput.value = renewalDate || '';
            }
            hostingValidTillInput.readOnly = true;
            hostingValidTillInput.style.pointerEvents = 'none';
            hostingValidTillInput.style.backgroundColor = '#e9ecef';
        } else {
            if (!isInitial) {
                hostingValidTillInput.value = '';
            }
            hostingValidTillInput.readOnly = false;
            hostingValidTillInput.style.pointerEvents = 'auto';
            hostingValidTillInput.style.backgroundColor = '';
        }
    }

    function updateDomainValidity(isInitial = false) {
        const selectedOption = domainRegistrationSelect.options[domainRegistrationSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const renewalDate = selectedOption.getAttribute('data-renewal');
            if (!isInitial || !domainValidTillInput.value) {
                domainValidTillInput.value = renewalDate || '';
            }
        } else {
            if (!isInitial) {
                domainValidTillInput.value = '';
            }
        }
        domainValidTillInput.readOnly = false;
        domainValidTillInput.style.pointerEvents = 'auto';
        domainValidTillInput.style.backgroundColor = '';
    }

    if (hostingProviderSelect) {
        hostingProviderSelect.addEventListener('change', () => updateHostingValidity(false));
        updateHostingValidity(true);
    }

    if (domainRegistrationSelect) {
        domainRegistrationSelect.addEventListener('change', () => updateDomainValidity(false));
        updateDomainValidity(true);
    }
});
</script>

@endpush
