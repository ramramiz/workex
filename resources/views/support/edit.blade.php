@extends('layouts.app')

@section('title', 'Edit Support Ticket')
@section('page-title', 'Edit Support Ticket')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('support.index') }}">Support</a></li>
    <li class="breadcrumb-item"><a href="{{ route('support.show', $support) }}">{{ $support->ticket_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Support Ticket: {{ $support->ticket_number }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('support.update', $support) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Ticket Subject / Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $support->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Client Account</label>
                            <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" disabled>
                                <option value="">{{ $support->client->company_name ?? 'General support / Internal' }}</option>
                            </select>
                            <div class="form-text">Client cannot be modified after opening.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Project board</label>
                            <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" disabled>
                                <option value="">{{ $support->project ? $support->project->name . ' (' . ($support->project->client?->company_name ?? 'Internal Project') . ')' : 'General / AMC support' }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Ticket Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="open" {{ old('status', $support->status) === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ old('status', $support->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="closed" {{ old('status', $support->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Assigned Agent</label>
                            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">-- Choose Support Engineer --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to', $support->assigned_to) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="low" {{ old('priority', $support->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', $support->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority', $support->priority) === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ old('priority', $support->priority) === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">AMC Contract Start</label>
                            <input type="date" name="amc_start_date" class="form-control @error('amc_start_date') is-invalid @enderror" value="{{ old('amc_start_date', $support->amc_start_date ? $support->amc_start_date->format('Y-m-d') : '') }}">
                            @error('amc_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">AMC Contract End</label>
                            <input type="date" name="amc_end_date" class="form-control @error('amc_end_date') is-invalid @enderror" value="{{ old('amc_end_date', $support->amc_end_date ? $support->amc_end_date->format('Y-m-d') : '') }}">
                            @error('amc_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Issue Details / Message <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5" required>{{ old('description', $support->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('support.show', $support) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
