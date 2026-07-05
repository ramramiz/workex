<div class="list-group list-group-flush" id="settingsMenu">
    <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.index') ? 'active' : '' }} d-flex align-items-center gap-2">
        <i class="bi bi-sliders"></i> General Settings
    </a>
    <a href="{{ route('settings.holidays.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.holidays*') ? 'active' : '' }} d-flex align-items-center gap-2">
        <i class="bi bi-calendar-check"></i> Holiday Marking
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
    @if(auth()->user()->isAdminOrAbove() || auth()->user()->isTeamLeader())
    <a href="{{ route('hosting-providers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('hosting-providers*') ? 'active' : '' }} d-flex align-items-center gap-2">
        <i class="bi bi-server"></i> Hosting Providers
    </a>
    <a href="{{ route('domain-registrations.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('domain-registrations*') ? 'active' : '' }} d-flex align-items-center gap-2">
        <i class="bi bi-globe"></i> Domain Registrations
    </a>
    @endif
    <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('users*') ? 'active' : '' }} d-flex align-items-center gap-2">
        <i class="bi bi-people"></i> User Logins
    </a>
    <a href="{{ route('settings.discontinued-projects') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.discontinued-projects*') ? 'active' : '' }} d-flex align-items-center gap-2">
        <i class="bi bi-folder-x"></i> Discontinued Projects
    </a>
</div>
