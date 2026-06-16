@extends('layouts.app')

@section('title', 'Add Lead')
@section('page-title', 'Add Lead')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
    <li class="breadcrumb-item active">Add Lead</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Capture New Lead</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leads.store') }}">
                    @csrf
                    
                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Client Details</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Link to Existing Client <span class="text-muted">(Optional)</span></label>
                            <select name="client_id" id="client_id" class="form-select" onchange="autofillClient()">
                                <option value="">-- Choose Client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" data-name="{{ $client->company_name }}" data-email="{{ $client->email }}" data-phone="{{ $client->mobile ?? $client->phone }}">{{ $client->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Client / Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="client_name" id="client_name" class="form-control @error('client_name') is-invalid @enderror" value="{{ old('client_name') }}" required placeholder="e.g. Acme Corp or Jane Doe">
                            @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Client Email Address</label>
                            <input type="email" name="client_email" id="client_email" class="form-control @error('client_email') is-invalid @enderror" value="{{ old('client_email') }}" placeholder="e.g. contact@acme.com">
                            @error('client_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Client Phone Number</label>
                            <input type="text" name="client_phone" id="client_phone" class="form-control @error('client_phone') is-invalid @enderror" value="{{ old('client_phone') }}" placeholder="e.g. 9876543210">
                            @error('client_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Requirements & Pipeline</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Lead Source</label>
                            <select name="source" class="form-select @error('source') is-invalid @enderror">
                                <option value="website" {{ old('source') === 'website' ? 'selected' : '' }}>Website Enquiry</option>
                                <option value="email" {{ old('source') === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="phone" {{ old('source') === 'phone' ? 'selected' : '' }}>Phone</option>
                                <option value="reference" {{ old('source') === 'reference' ? 'selected' : '' }}>Reference</option>
                                <option value="google" {{ old('source') === 'google' ? 'selected' : '' }}>Google Search</option>
                                <option value="other" {{ old('source') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Estimated Budget</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="estimated_budget" class="form-control @error('estimated_budget') is-invalid @enderror" value="{{ old('estimated_budget') }}" placeholder="0.00">
                            </div>
                            @error('estimated_budget')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Assigned To <span class="text-muted">(Admin / PM)</span></label>
                            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">Select Assignee</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Lead Room <span class="text-muted">(Optional)</span></label>
                            <select name="lead_room_id" class="form-select @error('lead_room_id') is-invalid @enderror">
                                <option value="">Select Room</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ old('lead_room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                @endforeach
                            </select>
                            @error('lead_room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Next Follow Up Date</label>
                            <input type="date" name="follow_up_date" class="form-control @error('follow_up_date') is-invalid @enderror" value="{{ old('follow_up_date') }}">
                            @error('follow_up_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Detailed Requirement <span class="text-danger">*</span></label>
                            <textarea name="requirement" class="form-control @error('requirement') is-invalid @enderror" rows="4" required placeholder="Describe what the lead wants (e.g. mobile app, e-commerce, CRM, SEO)...">{{ old('requirement') }}</textarea>
                            @error('requirement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Notes</h6>
                    <div class="mb-4">
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Any internal notes, background, or meeting details...">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function autofillClient() {
        const select = document.getElementById('client_id');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption.value) {
            document.getElementById('client_name').value = selectedOption.getAttribute('data-name') || '';
            document.getElementById('client_email').value = selectedOption.getAttribute('data-email') || '';
            document.getElementById('client_phone').value = selectedOption.getAttribute('data-phone') || '';
        }
    }
</script>
@endpush
