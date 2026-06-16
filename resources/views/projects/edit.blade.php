@extends('layouts.app')

@section('title', 'Edit Project')
@section('page-title', 'Edit Project')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Project: {{ $project->name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('projects.update', $project) }}">
                    @csrf
                    @method('PUT')

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Project Identity</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Project Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $project->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Project Type</label>
                            <select name="project_type" class="form-select @error('project_type') is-invalid @enderror">
                                <option value="web" {{ old('project_type', $project->project_type) === 'web' ? 'selected' : '' }}>Web Application</option>
                                <option value="mobile" {{ old('project_type', $project->project_type) === 'mobile' ? 'selected' : '' }}>Mobile Application</option>
                                <option value="desktop" {{ old('project_type', $project->project_type) === 'desktop' ? 'selected' : '' }}>Desktop Software</option>
                                <option value="other" {{ old('project_type', $project->project_type) === 'other' ? 'selected' : '' }}>Other Services</option>
                            </select>
                            @error('project_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Project Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="not_started" {{ old('status', $project->status) === 'not_started' ? 'selected' : '' }}>Not Started</option>
                                <option value="planning" {{ old('status', $project->status) === 'planning' ? 'selected' : '' }}>Planning</option>
                                <option value="design" {{ old('status', $project->status) === 'design' ? 'selected' : '' }}>Design</option>
                                <option value="development" {{ old('status', $project->status) === 'development' ? 'selected' : '' }}>Development</option>
                                <option value="testing" {{ old('status', $project->status) === 'testing' ? 'selected' : '' }}>Testing</option>
                                <option value="client_review" {{ old('status', $project->status) === 'client_review' ? 'selected' : '' }}>Client Review</option>
                                <option value="rework" {{ old('status', $project->status) === 'rework' ? 'selected' : '' }}>Rework</option>
                                <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="delivered" {{ old('status', $project->status) === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="on_hold" {{ old('status', $project->status) === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="cancelled" {{ old('status', $project->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Project Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $project->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                    <option value="{{ $c->id }}" {{ old('client_id', $project->client_id) == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
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
                                    <option value="{{ $tl->id }}" {{ old('team_leader_id', $project->team_leader_id) == $tl->id ? 'selected' : '' }}>{{ $tl->name }}</option>
                                @endforeach
                                <option value="new_team_leader" class="text-primary fw-bold">+ Add New Team Leader...</option>
                            </select>
                            @error('team_leader_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Financials, Schedule & Technologies</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Project Budget (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="budget" class="form-control @error('budget') is-invalid @enderror" value="{{ old('budget', $project->budget) }}">
                            </div>
                            @error('budget')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="low" {{ old('priority', $project->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', $project->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority', $project->priority) === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ old('priority', $project->priority) === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}">
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Deadline / Due Date</label>
                            <input type="date" name="deadline" class="form-control @error('deadline') is-invalid @enderror" value="{{ old('deadline', $project->deadline ? $project->deadline->format('Y-m-d') : '') }}">
                            @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Technologies Used <span class="text-muted">(Comma separated tags)</span></label>
                            @php
                                $techs = is_array($project->technologies) ? implode(', ', $project->technologies) : '';
                            @endphp
                            <input type="text" name="technologies" class="form-control @error('technologies') is-invalid @enderror" value="{{ old('technologies', $techs) }}" placeholder="e.g. PHP, Laravel, MySQL, Bootstrap">
                            @error('technologies')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Assigned Developers / Team Members</h6>
                    <div class="mb-4">
                        <div class="row g-2">
                            @php
                                $assignedMemberIds = $project->members->pluck('id')->toArray();
                            @endphp
                            @foreach($employees as $emp)
                                <div class="col-12 col-md-4">
                                    <div class="form-check border rounded p-2">
                                        <input class="form-check-input ms-1" type="checkbox" name="members[]" value="{{ $emp->id }}" id="chk-emp-{{ $emp->id }}" {{ in_array($emp->id, $assignedMemberIds) ? 'checked' : '' }}>
                                        <label class="form-check-label ms-2 text-dark fs-7" for="chk-emp-{{ $emp->id }}">
                                            {{ $emp->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Project</button>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dropdowns selectors
    const clientSelect = document.getElementById('project_client_select');
    const tlSelect = document.getElementById('project_team_leader_select');

    let lastClientVal = clientSelect.value;
    let lastTlVal = tlSelect.value;

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
});
</script>
@endpush
