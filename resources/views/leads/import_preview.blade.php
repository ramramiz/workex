@extends('layouts.app')

@section('title', 'Preview Leads Import')
@section('page-title', 'Preview Leads Import')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
    <li class="breadcrumb-item"><a href="{{ route('leads.import.form') }}">Import</a></li>
    <li class="breadcrumb-item active">Preview</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 mx-auto">
        
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="p-3 bg-primary-subtle text-primary rounded">
                            <i class="bi bi-door-open fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-7 text-uppercase fw-semibold">Target Room</div>
                            <h5 class="mb-0 fw-bold">{{ $roomName }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="p-3 bg-success-subtle text-success rounded">
                            <i class="bi bi-check-circle fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-7 text-uppercase fw-semibold">Valid Leads (To Import)</div>
                            <h5 class="mb-0 fw-bold">{{ count($validRows) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="p-3 bg-danger-subtle text-danger rounded">
                            <i class="bi bi-x-circle fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-7 text-uppercase fw-semibold">Invalid Leads (Skipped)</div>
                            <h5 class="mb-0 fw-bold">{{ count($invalidRows) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                <h5 class="mb-0 text-dark fw-bold">Parsed Leads Preview</h5>
                
                <form method="POST" action="{{ route('leads.import.submit') }}">
                    @csrf
                    <input type="hidden" name="temp_file_path" value="{{ $tempFilePath }}">
                    <input type="hidden" name="lead_room_id" value="{{ $lead_room_id }}">
                    
                    <div class="d-inline-flex gap-2">
                        <a href="{{ route('leads.import.form') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-sm" {{ count($validRows) === 0 ? 'disabled' : '' }}>
                            <i class="bi bi-cloud-arrow-up me-1"></i> Confirm & Import ({{ count($validRows) }} Leads)
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th style="width: 60px;" class="text-center">#</th>
                                <th style="width: 100px;" class="text-center">Status</th>
                                <th>Client Name</th>
                                <th>Client Phone</th>
                                <th>Client Email</th>
                                <th>Location</th>
                                <th>Source</th>
                                <th>Est. Budget</th>
                                <th>Errors / Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Valid Rows -->
                            @foreach($validRows as $index => $row)
                                <tr>
                                    <td class="text-center font-monospace text-muted">{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle py-1">Valid</span>
                                    </td>
                                    <td><div class="fw-semibold text-dark">{{ $row['client_name'] }}</div></td>
                                    <td><span class="font-monospace">{{ $row['client_phone'] }}</span></td>
                                    <td><span class="fs-7 text-muted">{{ $row['client_email'] ?: '—' }}</span></td>
                                    <td><span class="fs-7">{{ $row['location'] ?: '—' }}</span></td>
                                    <td><span class="fs-7">{{ ucfirst($row['source'] ?: 'direct') }}</span></td>
                                    <td>
                                        @if($row['estimated_budget'])
                                            <span class="fw-semibold font-monospace">₹{{ number_format($row['estimated_budget'], 2) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><span class="text-success fs-7"><i class="bi bi-check-lg me-1"></i>Ready to import</span></td>
                                </tr>
                            @endforeach

                            <!-- Invalid Rows -->
                            @foreach($invalidRows as $index => $row)
                                <tr class="table-danger-subtle">
                                    <td class="text-center font-monospace text-muted">{{ count($validRows) + $index + 1 }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1">Invalid</span>
                                    </td>
                                    <td><div class="fw-semibold text-dark">{{ $row['client_name'] ?: '—' }}</div></td>
                                    <td><span class="font-monospace text-danger fw-bold">{{ $row['client_phone'] ?: '—' }}</span></td>
                                    <td><span class="fs-7 text-muted">{{ $row['client_email'] ?: '—' }}</span></td>
                                    <td><span class="fs-7">{{ $row['location'] ?: '—' }}</span></td>
                                    <td><span class="fs-7">{{ ucfirst($row['source'] ?: 'direct') }}</span></td>
                                    <td>
                                        @if($row['estimated_budget'])
                                            <span class="fw-semibold font-monospace">₹{{ number_format($row['estimated_budget'], 2) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-danger fw-semibold fs-7">
                                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $row['errors'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
