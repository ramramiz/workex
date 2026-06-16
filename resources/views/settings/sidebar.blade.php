<div class="list-group list-group-flush" id="settingsMenu">
    <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.index') ? 'active' : '' }} d-flex align-items-center gap-2">
        <i class="bi bi-sliders"></i> General Settings
    </a>
    <a href="{{ route('departments.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('departments*') ? 'active' : '' }} d-flex align-items-center gap-2">
        <i class="bi bi-diagram-3"></i> Departments
    </a>
    <a href="{{ route('designations.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('designations*') ? 'active' : '' }} d-flex align-items-center gap-2">
        <i class="bi bi-award"></i> Designations
    </a>
    @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
    <a href="{{ route('employees.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('employees*') ? 'active' : '' }} d-flex align-items-center gap-2">
        <i class="bi bi-people-fill"></i> Employee Settings
    </a>
    @endif
    <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('users*') ? 'active' : '' }} d-flex align-items-center gap-2">
        <i class="bi bi-people"></i> User Logins
    </a>
</div>
