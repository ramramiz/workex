@php
    $user = auth()->user();
    $getStatusCount = function($status) use ($user) {
        return \App\Models\Lead::forUser($user)
            ->where('status', $status)
            ->count();
    };
    
    $statusUrl = function($status) {
        $queries = request()->query();
        if (is_null($status)) {
            unset($queries['status']);
        } else {
            $queries['status'] = $status;
        }
        unset($queries['page']);
        return route('leads.index', $queries);
    };
@endphp

<div class="topnav-status-container">
    <div class="topnav-status-track">
        <a class="topnav-status-pill {{ !request('status') ? 'active' : '' }}" href="{{ $statusUrl(null) }}">
            All <span class="count">{{ \App\Models\Lead::forUser($user)->count() }}</span>
        </a>
        <a class="topnav-status-pill {{ request('status') === 'new' ? 'active' : '' }}" href="{{ $statusUrl('new') }}">
            New <span class="count">{{ $getStatusCount('new') }}</span>
        </a>
        <a class="topnav-status-pill {{ request('status') === 'following_up' ? 'active' : '' }}" href="{{ $statusUrl('following_up') }}">
            Following Up <span class="count">{{ $getStatusCount('following_up') }}</span>
        </a>
        <a class="topnav-status-pill {{ request('status') === 'interested' ? 'active' : '' }}" href="{{ $statusUrl('interested') }}">
            Interested <span class="count">{{ $getStatusCount('interested') }}</span>
        </a>
        <a class="topnav-status-pill {{ request('status') === 'not_interested' ? 'active' : '' }}" href="{{ $statusUrl('not_interested') }}">
            Not Interested <span class="count">{{ $getStatusCount('not_interested') }}</span>
        </a>
        <a class="topnav-status-pill {{ request('status') === 'call_back_later' ? 'active' : '' }}" href="{{ $statusUrl('call_back_later') }}">
            Call Back <span class="count">{{ $getStatusCount('call_back_later') }}</span>
        </a>
        <a class="topnav-status-pill {{ request('status') === 'follow_up_required' ? 'active' : '' }}" href="{{ $statusUrl('follow_up_required') }}">
            Follow-up Req <span class="count">{{ $getStatusCount('follow_up_required') }}</span>
        </a>
        <a class="topnav-status-pill {{ request('status') === 'converted' ? 'active' : '' }}" href="{{ $statusUrl('converted') }}">
            Converted <span class="count">{{ $getStatusCount('converted') }}</span>
        </a>
        <a class="topnav-status-pill {{ request('status') === 'closed' ? 'active' : '' }}" href="{{ $statusUrl('closed') }}">
            Closed <span class="count">{{ $getStatusCount('closed') }}</span>
        </a>
    </div>
</div>
