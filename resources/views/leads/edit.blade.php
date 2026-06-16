@extends('layouts.app')

@section('title', 'Edit Lead')
@section('page-title', 'Edit Lead')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
    <li class="breadcrumb-item"><a href="{{ route('leads.show', $lead) }}">{{ $lead->client_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Lead: {{ $lead->client_name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leads.update', $lead) }}">
                    @csrf
                    @method('PUT')
                    
                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Client Details</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Link to Existing Client <span class="text-muted">(Optional)</span></label>
                            <select name="client_id" id="client_id" class="form-select">
                                <option value="">-- Choose Client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id', $lead->client_id) == $client->id ? 'selected' : '' }}>{{ $client->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Client / Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="client_name" class="form-control @error('client_name') is-invalid @enderror" value="{{ old('client_name', $lead->client_name) }}" required>
                            @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Client Email Address</label>
                            <input type="email" name="client_email" class="form-control @error('client_email') is-invalid @enderror" value="{{ old('client_email', $lead->client_email) }}">
                            @error('client_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Client Phone Number</label>
                            <input type="text" name="client_phone" class="form-control @error('client_phone') is-invalid @enderror" value="{{ old('client_phone', $lead->client_phone) }}">
                            @error('client_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Requirements & Pipeline</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Lead Source</label>
                            <select name="source" class="form-select @error('source') is-invalid @enderror">
                                <option value="website" {{ old('source', $lead->source) === 'website' ? 'selected' : '' }}>Website Enquiry</option>
                                <option value="email" {{ old('source', $lead->source) === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="phone" {{ old('source', $lead->source) === 'phone' ? 'selected' : '' }}>Phone</option>
                                <option value="reference" {{ old('source', $lead->source) === 'reference' ? 'selected' : '' }}>Reference</option>
                                <option value="google" {{ old('source', $lead->source) === 'google' ? 'selected' : '' }}>Google Search</option>
                                <option value="other" {{ old('source', $lead->source) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Estimated Budget</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="estimated_budget" class="form-control @error('estimated_budget') is-invalid @enderror" value="{{ old('estimated_budget', $lead->estimated_budget) }}">
                            </div>
                            @error('estimated_budget')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Assigned To <span class="text-muted">(Admin / PM)</span></label>
                            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">Select Assignee</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to', $lead->assigned_to) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Lead Room <span class="text-muted">(Optional)</span></label>
                            <select name="lead_room_id" class="form-select @error('lead_room_id') is-invalid @enderror">
                                <option value="">Select Room</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ old('lead_room_id', $lead->lead_room_id) == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                @endforeach
                            </select>
                            @error('lead_room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Next Follow Up Date</label>
                            <input type="date" name="follow_up_date" class="form-control @error('follow_up_date') is-invalid @enderror" value="{{ old('follow_up_date', $lead->follow_up_date ? $lead->follow_up_date->format('Y-m-d') : '') }}">
                            @error('follow_up_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Lead Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="new" {{ old('status', $lead->status) === 'new' ? 'selected' : '' }}>New</option>
                                <option value="following_up" {{ old('status', $lead->status) === 'following_up' ? 'selected' : '' }}>Following Up</option>
                                <option value="converted" {{ old('status', $lead->status) === 'converted' ? 'selected' : '' }}>Converted</option>
                                <option value="lost" {{ old('status', $lead->status) === 'lost' ? 'selected' : '' }}>Lost</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Detailed Requirement <span class="text-danger">*</span></label>
                            <textarea name="requirement" class="form-control @error('requirement') is-invalid @enderror" rows="4" required>{{ old('requirement', $lead->requirement) }}</textarea>
                            @error('requirement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Notes</h6>
                    <div class="mb-4">
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $lead->notes) }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
