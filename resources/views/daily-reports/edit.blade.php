@extends('layouts.app')

@section('title', 'Edit Daily Report')
@section('page-title', 'Edit Daily Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('daily-reports.index') }}">Daily Reports</a></li>
    <li class="breadcrumb-item"><a href="{{ route('daily-reports.show', $report) }}">{{ \Carbon\Carbon::parse($report->date)->format('d M Y') }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Daily Report: {{ \Carbon\Carbon::parse($report->date)->format('d M Y') }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('daily-reports.update', $report) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Report Date</label>
                        <input type="text" class="form-control bg-light fw-semibold" value="{{ \Carbon\Carbon::parse($report->date)->format('d M Y') }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Completed Work Details <span class="text-danger">*</span></label>
                        <textarea name="completed_work" class="form-control @error('completed_work') is-invalid @enderror" rows="5" required>{{ old('completed_work', $report->completed_work) }}</textarea>
                        @error('completed_work')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pending / Remaining Tasks</label>
                        <textarea name="pending_work" class="form-control @error('pending_work') is-invalid @enderror" rows="3">{{ old('pending_work', $report->pending_work) }}</textarea>
                        @error('pending_work')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Blockers / Issues Faced</label>
                        <textarea name="issues_faced" class="form-control @error('issues_faced') is-invalid @enderror" rows="3">{{ old('issues_faced', $report->issues_faced) }}</textarea>
                        @error('issues_faced')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Plan for Tomorrow</label>
                        <textarea name="tomorrow_plan" class="form-control @error('tomorrow_plan') is-invalid @enderror" rows="3">{{ old('tomorrow_plan', $report->tomorrow_plan) }}</textarea>
                        @error('tomorrow_plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Git Commit / Repository URL</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-github"></i></span>
                            <input type="url" name="git_commit_link" class="form-control @error('git_commit_link') is-invalid @enderror" value="{{ old('git_commit_link', $report->git_commit_link) }}">
                        </div>
                        @error('git_commit_link')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('daily-reports.show', $report) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
