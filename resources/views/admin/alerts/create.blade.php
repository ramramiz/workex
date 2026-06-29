@extends('layouts.app')

@section('title', 'Create Global Alert')
@section('page-title', 'Create Alert')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.alerts.index') }}">Global Alerts</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <!-- Form Card -->
            <div class="card border shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-bell-fill text-warning"></i> Send Global Broadcast Alert
                    </h5>
                    <p class="text-secondary mb-0 small mt-1">This will display a blocking alert modal containing your message to chosen users across all views.</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.alerts.store') }}">
                        @csrf

                        <!-- Heading -->
                        <div class="mb-3">
                            <label for="heading" class="form-label text-dark fw-bold" style="font-size: 13px;">Alert Heading</label>
                            <input type="text" name="heading" id="heading" class="form-control @error('heading') is-invalid @enderror" value="{{ old('heading') }}" placeholder="e.g. SYSTEM MAINTENANCE NOTICE" required style="border-radius: 10px;">
                            @error('heading')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Message Title / Body -->
                        <div class="mb-4">
                            <label for="title" class="form-label text-dark fw-bold" style="font-size: 13px;">Alert Title & Details</label>
                            <textarea name="title" id="title" rows="5" class="form-control @error('title') is-invalid @enderror" placeholder="Write the main body message or title details of the alert here..." required style="border-radius: 10px;">{{ old('title') }}</textarea>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Broadcast Target -->
                        <div class="mb-4">
                            <label class="form-label text-dark fw-bold d-block" style="font-size: 13px;">Recipient Target</label>
                            
                            <div class="d-flex gap-4 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target" id="target_all" value="all" checked {{ old('target') === 'all' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="target_all">
                                        Send to All Users
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target" id="target_selected" value="selected" {{ old('target') === 'selected' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="target_selected">
                                        Send to Selected Users
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- User Selection (Collapsible / Toggleable via JS) -->
                        <div class="mb-4 d-none" id="user_selection_wrapper">
                            <label class="form-label text-dark fw-bold" style="font-size: 13px;">Select Recipient Users</label>
                            
                            <div class="border rounded p-3 bg-light" style="max-height: 250px; overflow-y: auto; border-radius: 10px !important;">
                                <div class="mb-2 border-bottom pb-2 d-flex justify-content-between align-items-center">
                                    <span class="text-secondary small fw-medium">Check users to receive the alert</span>
                                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-bold" style="font-size: 11px; color: #d97706;" onclick="toggleSelectAllUsers(this)">Select All</button>
                                </div>
                                
                                <div class="user-checkbox-list">
                                    @forelse($users as $user)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input user-checkbox" type="checkbox" name="users[]" value="{{ $user->id }}" id="user_{{ $user->id }}" {{ is_array(old('users')) && in_array($user->id, old('users')) ? 'checked' : '' }}>
                                            <label class="form-check-label text-dark fw-medium" for="user_{{ $user->id }}">
                                                {{ $user->name }} <span class="text-secondary small">({{ ucfirst($user->role ?? 'user') }})</span>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-3">
                                            No other active users found.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            @error('users')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('admin.alerts.index') }}" class="btn btn-light fw-bold px-4 py-2.5" style="border-radius: 10px;">Cancel</a>
                            <button type="submit" class="btn btn-warning fw-bold text-dark px-4 py-2.5" style="border-radius: 10px;">
                                <i class="bi bi-send-fill me-1"></i> Send Global Alert
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const targetAll = document.getElementById('target_all');
        const targetSelected = document.getElementById('target_selected');
        const wrapper = document.getElementById('user_selection_wrapper');

        function toggleWrapper() {
            if (targetSelected.checked) {
                wrapper.classList.remove('d-none');
            } else {
                wrapper.classList.add('d-none');
            }
        }

        targetAll.addEventListener('change', toggleWrapper);
        targetSelected.addEventListener('change', toggleWrapper);
        toggleWrapper(); // Initial trigger
    });

    function toggleSelectAllUsers(button) {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => cb.checked = !allChecked);
        button.textContent = allChecked ? 'Select All' : 'Deselect All';
    }
</script>
@endsection
