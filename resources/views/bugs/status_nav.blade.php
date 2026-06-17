@php
    $user = auth()->user();
    
    // Total bugs count (scoped by the global company scope automatically)
    $allBugsCount = \App\Models\Bug::count();
    
    // Solved bugs count (status in approved, cleared, also scoped automatically)
    $solvedBugsCount = \App\Models\Bug::whereIn('status', ['approved', 'cleared'])->count();
    
    $bugsUrl = function($filter = null) {
        $queries = request()->query();
        if (is_null($filter)) {
            unset($queries['filter']);
        } else {
            $queries['filter'] = $filter;
        }
        unset($queries['page']);
        return route('bugs.index', $queries);
    };
@endphp

<div class="topnav-status-container">
    <div class="topnav-status-track">
        <a class="topnav-status-pill {{ request('filter') !== 'solved' ? 'active' : '' }}" href="{{ $bugsUrl(null) }}">
            All Issues <span class="count">{{ $allBugsCount }}</span>
        </a>
        <a class="topnav-status-pill {{ request('filter') === 'solved' ? 'active' : '' }}" href="{{ $bugsUrl('solved') }}">
            Solved Bugs <span class="count">{{ $solvedBugsCount }}</span>
        </a>
    </div>
</div>
