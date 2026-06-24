@extends('layouts.app')

@section('title', 'Import Leads from Excel')
@section('page-title', 'Import Leads from Excel')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
    <li class="breadcrumb-item active">Import</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-dark fw-bold">Import Leads from Spreadsheet</h5>
            </div>
            <div class="card-body p-4">
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Instructions Box -->
                <div class="alert alert-info border-info-subtle bg-info-subtle text-info d-flex gap-3 mb-4">
                    <div class="fs-4"><i class="bi bi-info-circle-fill"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Instructions for Importing</h6>
                        <ul class="mb-2 fs-7 ps-3">
                            <li>Ensure you download and use the official spreadsheet template structure.</li>
                            <li><strong>Client Name</strong> and <strong>Client Phone</strong> are the only mandatory fields for each row.</li>
                            <li>Make sure to select the correct destination <strong>Room</strong> to import the leads into.</li>
                            <li>You will be shown a preview of valid and invalid leads before importing them finally.</li>
                        </ul>
                        <a href="{{ route('leads.import.template') }}" class="btn btn-info btn-sm text-white fw-semibold">
                            <i class="bi bi-cloud-arrow-down-fill me-1"></i> Download Excel Template (.xlsx)
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('leads.import.preview') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Target Room Select -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Target Room <span class="text-danger">*</span></label>
                        <select name="lead_room_id" class="form-select @error('lead_room_id') is-invalid @enderror" required>
                            <option value="">-- Select Destination Room --</option>
                            @foreach($clients as $client)
                                @if($client->rooms && $client->rooms->count() > 0)
                                    <optgroup label="{{ $client->company_name }}">
                                        @foreach($client->rooms as $room)
                                            <option value="{{ $room->id }}" {{ old('lead_room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted">Select a calling room (listed under its respective customer/client) to import leads into.</small>
                        @error('lead_room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- File Select -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Excel or CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required accept=".xlsx,.xls,.csv">
                        <small class="text-muted">Accepted formats: .xlsx, .xls, .csv. Max size: 4MB.</small>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-eye me-1"></i> Upload & Preview
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
