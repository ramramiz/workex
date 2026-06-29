@extends('layouts.app')

@section('title', 'Employees')
@section('page-title', 'Employees')

@section('breadcrumb')
    <li class="breadcrumb-item active">Employees</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Employee List</h5>
        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
            <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus me-1"></i> Add Employee
            </a>
        @endif
    </div>
    
    <!-- Filters -->
    <div class="card-body border-bottom py-3" style="background: var(--body-bg);">
        <form method="GET" action="{{ route('employees.index') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search name, email, code..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="department" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                    <th>Employee</th>
                    <th>Dept & Role</th>
                    <th>Joining Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $emp->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($emp->name) }}" alt="{{ $emp->name }}" class="avatar-circle" style="width: 40px; height: 40px;">
                                <div>
                                    <div class="fw-semibold">{{ $emp->name }}</div>
                                    <div class="text-muted" style="font-size: 12px;">{{ $emp->employee_code }} • {{ $emp->user?->email ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium">{{ $emp->department->name ?? 'N/A' }}</div>
                            <div class="text-muted" style="font-size: 12px;">{{ $emp->user?->role?->name ?? 'N/A' }}</div>
                        </td>
                        <td>{{ $emp->joining_date ? $emp->joining_date->format('d M Y') : 'N/A' }}</td>
                        <td>
                            @if($emp->status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('employees.show', $emp) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i> View Profile
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people" style="font-size: 32px;"></i>
                            <div class="mt-2">No employees found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($employees->hasPages())
        <div class="card-footer border-top" style="background: var(--card-bg);">
            {{ $employees->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     Session Panel — Slide-in overlay from the right
═══════════════════════════════════════════════════════════ --}}
<div id="session-panel-backdrop" onclick="closeSessionPanel()"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9000; backdrop-filter:blur(2px);"
></div>

<div id="session-panel"
    style="display:none; position:fixed; top:0; right:0; height:100vh; width:100%; max-width:500px;
           background:#fff; z-index:9001; box-shadow:-8px 0 32px rgba(0,0,0,0.18);
           display:flex; flex-direction:column; transform:translateX(100%); transition:transform .3s cubic-bezier(.4,0,.2,1);">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 p-4 border-bottom" style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);">
        <div id="sp-avatar-wrap" style="position:relative;">
            <img id="sp-avatar" src="" alt="" style="width:46px;height:46px;border-radius:50%;border:2px solid rgba(255,255,255,.3);object-fit:cover;">
            <span id="sp-status-dot" style="position:absolute;bottom:1px;right:1px;width:11px;height:11px;border-radius:50%;border:2px solid #1e3a5f;background:#6b7280;"></span>
        </div>
        <div class="flex-grow-1">
            <div class="text-white fw-bold" id="sp-name" style="font-size:15px;"></div>
            <div class="text-white-50" id="sp-email" style="font-size:12px;"></div>
        </div>
        <button type="button" onclick="closeSessionPanel()" class="btn btn-sm btn-outline-light" style="border-radius:50%;width:34px;height:34px;padding:0;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- Sub-header: session count + Force Logout All --}}
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom" style="background:#f8fafc;">
        <div>
            <span class="fw-semibold text-dark" style="font-size:13px;">Active Sessions</span>
            <span id="sp-session-count" class="badge bg-primary-subtle text-primary ms-2" style="font-size:11px;">0</span>
        </div>
        <button id="sp-logout-all-btn" onclick="forceLogoutAll()" class="btn btn-sm btn-danger d-flex align-items-center gap-1" style="font-size:12px;">
            <i class="bi bi-box-arrow-right"></i> Force Logout All
        </button>
    </div>

    {{-- Session List --}}
    <div id="sp-session-list" class="flex-grow-1 overflow-auto p-3">
        {{-- Populated by JS --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
    let _sessionPanelUserId  = null;
    let _sessionDestroyAllUrl = null;
    let _csrfToken = '{{ csrf_token() }}';

    function openSessionPanel(btn) {
        const userId      = btn.dataset.userId;
        const sessionsUrl = btn.dataset.sessionsUrl;
        _sessionDestroyAllUrl = btn.dataset.destroyAllUrl;
        _sessionPanelUserId   = userId;

        const panel    = document.getElementById('session-panel');
        const backdrop = document.getElementById('session-panel-backdrop');

        // Show skeleton immediately
        document.getElementById('sp-name').textContent  = 'Loading…';
        document.getElementById('sp-email').textContent = '';
        document.getElementById('sp-avatar').src        = '';
        document.getElementById('sp-session-count').textContent = '…';
        document.getElementById('sp-session-list').innerHTML = `
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <div class="mt-2" style="font-size:13px;">Loading sessions…</div>
            </div>`;

        panel.style.display    = 'flex';
        backdrop.style.display = 'block';
        requestAnimationFrame(() => { panel.style.transform = 'translateX(0)'; });

        fetch(sessionsUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => renderSessionPanel(data))
            .catch(() => {
                document.getElementById('sp-session-list').innerHTML =
                    '<div class="text-danger p-4">Failed to load sessions.</div>';
            });
    }

    function closeSessionPanel() {
        const panel    = document.getElementById('session-panel');
        const backdrop = document.getElementById('session-panel-backdrop');
        panel.style.transform = 'translateX(100%)';
        setTimeout(() => { panel.style.display = 'none'; backdrop.style.display = 'none'; }, 300);
    }

    function renderSessionPanel(data) {
        const user     = data.user;
        const sessions = data.sessions;

        document.getElementById('sp-name').textContent  = user.name;
        document.getElementById('sp-email').textContent = user.email;
        document.getElementById('sp-avatar').src        = user.avatar;
        document.getElementById('sp-session-count').textContent = sessions.length;

        // Status dot
        const dot = document.getElementById('sp-status-dot');
        if (sessions.length > 0) {
            dot.style.background = '#22c55e';
        } else {
            dot.style.background = '#6b7280';
        }

        const list = document.getElementById('sp-session-list');

        if (sessions.length === 0) {
            list.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-shield-check" style="font-size:40px; opacity:.4;"></i>
                    <div class="mt-3 fw-medium">No active sessions</div>
                    <div style="font-size:12px;">This employee is not currently logged in.</div>
                </div>`;
            return;
        }

        list.innerHTML = sessions.map((s, i) => {
            const deviceIcon = s.device === 'Mobile' ? 'bi-phone'
                             : s.device === 'Tablet' ? 'bi-tablet'
                             : 'bi-display';
            const browserIcon = { Chrome:'bi-browser-chrome', Firefox:'bi-browser-firefox',
                                   Edge:'bi-browser-edge', Safari:'bi-browser-safari' }[s.browser] || 'bi-globe';
            const isCurrent = s.is_current ? `<span class="badge bg-success-subtle text-success ms-1" style="font-size:10px;">You</span>` : '';

            return `
            <div class="session-card mb-3 border rounded-3 overflow-hidden" id="session-card-${s.id.substring(0,8)}">
                {{-- Session Header --}}
                <div class="d-flex align-items-center gap-3 p-3" style="background:#f8fafc;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;background:#e0f2fe;color:#0284c7;font-size:18px;flex-shrink:0;">
                        <i class="bi ${deviceIcon}"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-dark d-flex align-items-center gap-1" style="font-size:13px;">
                            ${s.device} — ${s.browser} ${isCurrent}
                        </div>
                        <div class="text-muted" style="font-size:11px;">${s.os}</div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" style="font-size:11px;white-space:nowrap;"
                        onclick="forceLogoutSession('${s.id}')" title="Force Logout This Session">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </div>

                {{-- Session Details Grid --}}
                <div class="p-3 pt-2" style="background:#fff;">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-geo-alt text-danger" style="margin-top:2px;"></i>
                                <div>
                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">IP Address</div>
                                    <div class="fw-medium" style="font-size:12px;word-break:break-all;">${s.ip_address}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-clock text-warning" style="margin-top:2px;"></i>
                                <div>
                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Last Active</div>
                                    <div class="fw-medium" style="font-size:12px;">${s.last_activity}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi ${browserIcon} text-primary" style="margin-top:2px;"></i>
                                <div>
                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Browser</div>
                                    <div class="fw-medium" style="font-size:12px;">${s.browser}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-laptop text-success" style="margin-top:2px;"></i>
                                <div>
                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">OS</div>
                                    <div class="fw-medium" style="font-size:12px;">${s.os}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    function forceLogoutSession(sessionId) {
        if (!confirm('Force logout this specific session?')) return;

        const shortId = sessionId.substring(0, 8);
        const card = document.getElementById('session-card-' + shortId);
        if (card) {
            card.style.opacity = '0.5';
            card.style.pointerEvents = 'none';
        }

        const url = `/users/${_sessionPanelUserId}/sessions/${sessionId}`;

        fetch(url, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': _csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (card) card.remove();
                const countEl = document.getElementById('sp-session-count');
                const newCount = Math.max(0, parseInt(countEl.textContent) - 1);
                countEl.textContent = newCount;
                if (newCount === 0) {
                    document.getElementById('sp-status-dot').style.background = '#6b7280';
                    document.getElementById('sp-session-list').innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check" style="font-size:40px;opacity:.4;"></i>
                            <div class="mt-3 fw-medium">No active sessions</div>
                        </div>`;
                }
            } else {
                alert(data.message || 'Failed to terminate session.');
                if (card) { card.style.opacity = '1'; card.style.pointerEvents = ''; }
            }
        })
        .catch(() => {
            alert('Network error. Please try again.');
            if (card) { card.style.opacity = '1'; card.style.pointerEvents = ''; }
        });
    }

    function forceLogoutAll() {
        if (!confirm('Force logout ALL sessions for this employee? They will be signed out immediately.')) return;

        const btn = document.getElementById('sp-logout-all-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Logging out…';

        fetch(_sessionDestroyAllUrl, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': _csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('sp-session-count').textContent = 0;
                document.getElementById('sp-status-dot').style.background = '#6b7280';
                document.getElementById('sp-session-list').innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-shield-check" style="font-size:40px;opacity:.4;"></i>
                        <div class="mt-3 fw-medium">All sessions terminated</div>
                        <div style="font-size:12px;">${data.message}</div>
                    </div>`;
                btn.innerHTML = '<i class="bi bi-check"></i> Done';
            } else {
                alert(data.message || 'Failed.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-box-arrow-right"></i> Force Logout All';
            }
        })
        .catch(() => {
            alert('Network error.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-right"></i> Force Logout All';
        });
    }
</script>

<!-- Roles & Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-labelledby="permissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; background-color: var(--card-bg, #ffffff);">
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; padding: 20px; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div>
                    <h5 class="modal-title fw-bold" id="permissionsModalLabel">Roles & Direct Permissions</h5>
                    <p class="text-white-50 mb-0" id="permissionsModalUser" style="font-size: 13px;"></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="permissionsForm">
                @csrf
                <div class="modal-body p-4" style="max-height: 62vh; overflow-y: auto;">
                    <!-- Role Select -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em;">Assign User Role</label>
                        <select name="role_id" id="permRoleSelect" class="form-select" style="border-radius: 10px; border: 1.5px solid #cbd5e1;">
                            <!-- Dynamically loaded -->
                        </select>
                    </div>

                    <!-- Direct Permissions Label -->
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <label class="fw-bold text-secondary mb-0" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em;">Direct Permission Overrides</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2" style="font-size: 11px; border-radius: 6px;" onclick="toggleAllCheckboxes(true)">Select All</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs py-1 px-2" style="font-size: 11px; border-radius: 6px;" onclick="toggleAllCheckboxes(false)">Deselect All</button>
                        </div>
                    </div>

                    <!-- Permissions Grid grouped by Module -->
                    <div class="row g-3" id="permissionsGrid" style="padding-right: 5px;">
                        <!-- Dynamically loaded grouped permissions -->
                    </div>
                </div>
                <div class="modal-footer border-top p-3" style="background-color: #f8fafc; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-secondary btn-sm" style="border-radius: 8px;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="savePermissionsBtn" class="btn btn-primary btn-sm px-4" style="border-radius: 8px;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentEmployeeId = null;
    let allRoles = [];
    const permissionsModal = new bootstrap.Modal(document.getElementById('permissionsModal'));

    function openPermissionsModal(employeeId, employeeName) {
        currentEmployeeId = employeeId;
        document.getElementById('permissionsModalUser').textContent = `Manage system access for: ${employeeName}`;
        
        const grid = document.getElementById('permissionsGrid');
        const roleSelect = document.getElementById('permRoleSelect');
        grid.innerHTML = '<div class="col-12 text-center py-5"><span class="spinner-border spinner-border-sm text-primary"></span> Loading permissions...</div>';
        roleSelect.innerHTML = '<option value="">Loading...</option>';

        permissionsModal.show();

        fetch(`/employees/${employeeId}/permissions?_=${Date.now()}`)
            .then(r => r.json())
            .then(data => {
                allRoles = data.roles;
                roleSelect.innerHTML = '';
                data.roles.forEach(role => {
                    const selected = role.id === data.current_role_id ? 'selected' : '';
                    roleSelect.innerHTML += `<option value="${role.id}" ${selected}>${role.name}</option>`;
                });

                grid.innerHTML = '';
                Object.keys(data.permissions).forEach(moduleName => {
                    let moduleHtml = `
                        <div class="col-md-6">
                            <div class="card border-0 shadow-xs mb-2 h-100" style="border-radius: 10px; background-color: #f8fafc; border: 1px solid #e2e8f0 !important;">
                                <div class="card-header bg-light border-0 py-2 fw-bold text-dark d-flex align-items-center justify-content-between" style="font-size: 12.5px; border-top-left-radius: 10px; border-top-right-radius: 10px; background-color: #edf2f7 !important;">
                                    <span>${moduleName}</span>
                                    <input type="checkbox" class="form-check-input module-group-check" data-module="${moduleName}" style="width: 15px; height: 15px; cursor: pointer;" onclick="toggleModuleGroup(this, '${moduleName}')">
                                </div>
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex flex-column gap-2">
                    `;

                    data.permissions[moduleName].forEach(perm => {
                        const checked = perm.checked ? 'checked' : '';
                        moduleHtml += `
                            <div class="form-check d-flex align-items-center gap-2 mb-0">
                                <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="${perm.id}" id="perm_${perm.id}" data-module="${moduleName}" ${checked} style="cursor: pointer;" onchange="updateModuleGroupCheckboxes()">
                                <label class="form-check-label text-dark fs-7" for="perm_${perm.id}" style="cursor: pointer; line-height: 1.2;">
                                    ${perm.name}
                                </label>
                            </div>
                        `;
                    });

                    moduleHtml += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    grid.innerHTML += moduleHtml;
                });

                updateModuleGroupCheckboxes();
            })
            .catch(err => {
                grid.innerHTML = '<div class="col-12 text-center text-danger py-5"><i class="bi bi-exclamation-triangle-fill"></i> Failed to load permissions.</div>';
            });
    }

    function toggleModuleGroup(groupCheckbox, moduleName) {
        const checkboxes = document.querySelectorAll(`.perm-checkbox[data-module="${moduleName}"]`);
        checkboxes.forEach(cb => {
            cb.checked = groupCheckbox.checked;
        });
    }

    function updateModuleGroupCheckboxes() {
        const modules = {};
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            const mod = cb.getAttribute('data-module');
            if (!modules[mod]) {
                modules[mod] = { total: 0, checked: 0 };
            }
            modules[mod].total++;
            if (cb.checked) {
                modules[mod].checked++;
            }
        });

        Object.keys(modules).forEach(mod => {
            const headerCb = document.querySelector(`.module-group-check[data-module="${mod}"]`);
            if (headerCb) {
                headerCb.checked = (modules[mod].total === modules[mod].checked && modules[mod].total > 0);
            }
        });
    }

    function toggleAllCheckboxes(checked) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            cb.checked = checked;
        });
        document.querySelectorAll('.module-group-check').forEach(cb => {
            cb.checked = checked;
        });
    }

    document.getElementById('permRoleSelect').addEventListener('change', function() {
        const selectedRoleId = parseInt(this.value);
        const role = allRoles.find(r => r.id === selectedRoleId);
        if (role && role.permissions) {
            const rolePermIds = role.permissions.map(p => p.id);
            document.querySelectorAll('.perm-checkbox').forEach(cb => {
                const permId = parseInt(cb.value);
                cb.checked = rolePermIds.includes(permId);
            });
            updateModuleGroupCheckboxes();
        }
    });

    document.getElementById('permissionsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('savePermissionsBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

        const formData = new FormData(this);
        
        fetch(`/employees/${currentEmployeeId}/permissions`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': _csrfToken
            }
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
            if (data.success) {
                permissionsModal.hide();
                const alertContainer = document.createElement('div');
                alertContainer.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow-lg';
                alertContainer.style.zIndex = '9999';
                alertContainer.innerHTML = `
                    <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.body.appendChild(alertContainer);
                setTimeout(() => {
                    alertContainer.remove();
                    window.location.reload();
                }, 1500);
            } else {
                alert(data.message || 'Something went wrong.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
            alert('Failed to save permissions. Please check connection and try again.');
        });
    });
</script>
@endpush
