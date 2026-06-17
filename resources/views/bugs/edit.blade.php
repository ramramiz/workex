@extends('layouts.app')

@section('title', 'Edit Bug Log')
@section('page-title', 'Edit Bug Log')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('bugs.index') }}">Bugs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bugs.show', $bug) }}">{{ $bug->title }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('topnav-middle')
    @include('bugs.status_nav')
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Bug: {{ $bug->title }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('bugs.update', $bug) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Issue Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $bug->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Project board <span class="text-danger">*</span></label>
                            <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ old('project_id', $bug->project_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Assignee / Developer</label>
                            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">-- Choose Developer --</option>
                                @foreach($developers as $dev)
                                    <option value="{{ $dev->id }}" {{ old('assigned_to', $bug->assigned_to) == $dev->id ? 'selected' : '' }}>{{ $dev->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="low" {{ old('priority', $bug->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', $bug->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority', $bug->priority) === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ old('priority', $bug->priority) === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Steps to Reproduce</label>
                        <textarea name="steps_to_reproduce" class="form-control @error('steps_to_reproduce') is-invalid @enderror" rows="3">{{ old('steps_to_reproduce', $bug->steps_to_reproduce) }}</textarea>
                        @error('steps_to_reproduce')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Related Link / URL</label>
                        <input type="text" name="link" class="form-control @error('link') is-invalid @enderror" value="{{ old('link', $bug->link) }}" placeholder="e.g. http://127.0.0.1:8000/some-page or relevant repository url">
                        @error('link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Detailed Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" required>{{ old('description', $bug->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label fs-7">Browser Agent</label>
                            <input type="text" name="browser_info" class="form-control form-control-sm" value="{{ $bug->browser_info }}" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-7">Operating System (OS)</label>
                            <input type="text" name="os_info" class="form-control form-control-sm" value="{{ $bug->os_info }}" readonly>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('bugs.show', $bug) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Bug</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
