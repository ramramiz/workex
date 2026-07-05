@php
    $user = auth()->user();
    
    // Scoped counts (Pending vs Completed)
    $pendingBugsCount = \App\Models\Bug::whereNotIn('status', ['completed', 'approved', 'cleared', 'closed'])->count();
    $completedBugsCount = \App\Models\Bug::whereIn('status', ['completed', 'approved', 'cleared', 'closed'])->count();
    
    $bugsUrl = function($filter = 'pending') {
        $queries = request()->query();
        $queries['filter'] = $filter;
        unset($queries['page']);
        return route('bugs.index', $queries);
    };
    
    $currentFilter = request('filter', 'pending');
@endphp

<div class="topnav-status-container">
    <div class="topnav-status-track">
        <a class="topnav-status-pill {{ $currentFilter === 'pending' ? 'active' : '' }}" href="{{ $bugsUrl('pending') }}">
            Pending Bugs <span class="count">{{ $pendingBugsCount }}</span>
        </a>
        <a class="topnav-status-pill {{ $currentFilter === 'completed' ? 'active' : '' }}" href="{{ $bugsUrl('completed') }}">
            Completed Bugs <span class="count">{{ $completedBugsCount }}</span>
        </a>
    </div>
</div>
