@extends('layouts.app')

@section('title', 'Notifications Center')
@section('page-title', 'Notifications')

@section('breadcrumb')
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
<div class="card border border-light shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">All System Alerts & Messages</h5>
        <div class="d-flex gap-2">
            @if(auth()->user()->unreadNotifications()->exists())
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-check2-all"></i> Mark All as Read
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="notifications-list">
            @forelse($notifications as $notif)
                <div class="list-group-item p-3 notification-item transition-all {{ is_null($notif->read_at) ? 'bg-light border-start border-3 border-primary' : '' }}" 
                     id="notif-{{ $notif->id }}" 
                     data-id="{{ $notif->id }}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle p-2 bg-{{ is_null($notif->read_at) ? 'primary' : 'secondary' }}-subtle text-{{ is_null($notif->read_at) ? 'primary' : 'secondary' }} d-flex align-items-center justify-content-center mt-1" style="width: 36px; height: 36px;">
                            <i class="bi {{ is_null($notif->read_at) ? 'bi-bell-fill' : 'bi-bell' }} fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h6 class="mb-1 {{ is_null($notif->read_at) ? 'fw-bold text-dark' : 'text-secondary' }}">{{ $notif->title }}</h6>
                                <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-2 text-muted fs-7">{{ $notif->message }}</p>
                            <div class="d-flex align-items-center gap-2">
                                @if($notif->url)
                                    <a href="{{ $notif->url }}" class="btn btn-link p-0 fs-7 text-decoration-none fw-semibold">
                                        View Details <i class="bi bi-chevron-right fs-8"></i>
                                    </a>
                                @endif
                                @if(is_null($notif->read_at))
                                    @if($notif->url)
                                        <span class="text-muted fs-8">•</span>
                                    @endif
                                    <button class="btn btn-link p-0 fs-7 text-decoration-none text-success fw-semibold mark-read-btn" onclick="markAsRead('{{ $notif->id }}')">
                                        Mark as Read
                                    </button>
                                @endif
                            </div>
                        </div>
                        @if(is_null($notif->read_at))
                            <span class="badge bg-primary rounded-circle p-1 mt-2 unread-dot" style="width: 8px; height: 8px;" title="Unread"> </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bell-slash" style="font-size: 48px; color: #a5b4fc;"></i>
                    <h5 class="mt-3">No notifications found</h5>
                    <p class="text-muted fs-7">We'll alert you here when tasks, projects, or payments are updated.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    function markAsRead(id) {
        const url = `{{ url('notifications') }}/${id}/mark-read`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.getElementById(`notif-${id}`);
                if (item) {
                    // Update classes to look read
                    item.classList.remove('bg-light', 'border-start', 'border-3', 'border-primary');
                    
                    // Update text weight
                    const title = item.querySelector('h6');
                    if (title) {
                        title.classList.remove('fw-bold', 'text-dark');
                        title.classList.add('text-secondary');
                    }
                    
                    // Hide unread dot
                    const dot = item.querySelector('.unread-dot');
                    if (dot) dot.remove();
                    
                    // Hide mark as read button
                    const btn = item.querySelector('.mark-read-btn');
                    if (btn) {
                        // If there was a separator dot, remove it
                        const separator = btn.previousElementSibling;
                        if (separator && separator.textContent === '•') {
                            separator.remove();
                        }
                        btn.remove();
                    }
                    
                    // Change icon styles
                    const iconWrapper = item.querySelector('.rounded-circle');
                    if (iconWrapper) {
                        iconWrapper.classList.remove('bg-primary-subtle', 'text-primary');
                        iconWrapper.classList.add('bg-secondary-subtle', 'text-secondary');
                        const icon = iconWrapper.querySelector('i');
                        if (icon) {
                            icon.classList.remove('bi-bell-fill');
                            icon.classList.add('bi-bell');
                        }
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }
</script>
@endpush
@endsection
