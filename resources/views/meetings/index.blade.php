@extends('layouts.app')

@section('title', 'Meetings & Discussions')
@section('page-title', 'Meetings & Discussions')

@section('breadcrumb')
    <li class="breadcrumb-item active">Meetings & Discussions</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Meetings & Discussions</h5>
        <a href="{{ route('meetings.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Meeting
        </a>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Meeting Title</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Linked Tasks</th>
                    <th>Organizer</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meetings as $meeting)
                    <tr>
                        <td>
                            <div class="fw-semibold">
                                <a href="{{ route('meetings.show', $meeting) }}" class="text-decoration-none text-dark">{{ $meeting->title }}</a>
                            </div>
                            @if($meeting->description)
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">{{ $meeting->description }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="fw-medium">
                                <i class="bi bi-calendar-event text-primary me-1"></i>
                                {{ $meeting->meeting_date->format('d M Y') }}
                            </span>
                        </td>
                        <td>
                            <span>
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                {{ $meeting->location }}
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background: #f3e8ff; color: #7c3aed; border: 1px solid #e9d5ff; font-size: 10px;">
                                {{ $meeting->tasks_count }} {{ Str::plural('task', $meeting->tasks_count) }}
                            </span>
                        </td>
                        <td>
                            @if($meeting->creator)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $meeting->creator->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <span class="fs-7">{{ $meeting->creator->name }}</span>
                                </div>
                            @else
                                <span class="text-muted fs-7">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('meetings.show', $meeting) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('meetings.destroy', $meeting) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this meeting?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-chat-left-quote" style="font-size: 32px;"></i>
                            <div class="mt-2">No meetings or discussions found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($meetings->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $meetings->links() }}
        </div>
    @endif
</div>
@endsection
