@extends('layouts.app')

@section('title', 'Add Task')
@section('page-title', 'Add Task')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active">Add Task</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create Task Card</h5>
            </div>
            <div class="card-body">
                @if(isset($selectedMeeting) && $selectedMeeting)
                    <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-calendar2-check-fill text-primary"></i>
                        <span>Adding task to meeting: <strong>{{ $selectedMeeting->title }}</strong> ({{ $selectedMeeting->meeting_date->format('Y-m-d') }})</span>
                    </div>
                @endif
                <form method="POST" action="{{ route('tasks.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if(isset($selectedMeeting) && $selectedMeeting)
                        <input type="hidden" name="meeting_id" value="{{ $selectedMeeting->id }}">
                    @endif
                    
                    <div class="mb-3">
                        <label class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="e.g. Design Landing Page Login Flow">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Task Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="Detailed instructions or specifications for this task...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Do you want to add this task to any project?</label>
                        <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror">
                            <option value="">-- No, do not link to any project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ (old('project_id') == $p->id || ($selectedProject && $selectedProject->id == $p->id)) ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Assignee / Developer <span class="text-danger">*</span></label>
                            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" required>
                                <option value="">-- Choose Assignee --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('assigned_to') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Task Deadline</label>
                            <input type="date" name="deadline" class="form-control @error('deadline') is-invalid @enderror" value="{{ old('deadline') }}">
                            @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Estimated Hours</label>
                            <input type="number" step="0.5" name="estimated_hours" class="form-control @error('estimated_hours') is-invalid @enderror" value="{{ old('estimated_hours') }}" placeholder="e.g. 8">
                            @error('estimated_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Task Attachment <span class="text-muted">(Optional, Image or PDF)</span></label>
                        <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept="image/*,application/pdf">
                        <div class="form-text text-muted" style="font-size: 0.8rem;">Supported formats: JPEG, PNG, JPG, GIF, PDF. Max size: 10MB.</div>
                        @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection




