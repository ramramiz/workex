@extends('layouts.app')

@section('title', 'Preview Projects Import')
@section('page-title', 'Preview Projects Import')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item active">Preview</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 mx-auto">
        
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="p-3 bg-success-subtle text-success rounded">
                            <i class="bi bi-check-circle fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-7 text-uppercase fw-semibold">Valid Projects (To Import)</div>
                            <h5 class="mb-0 fw-bold">{{ count($validRows) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="p-3 bg-danger-subtle text-danger rounded">
                            <i class="bi bi-x-circle fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-7 text-uppercase fw-semibold">Invalid Projects (Skipped)</div>
                            <h5 class="mb-0 fw-bold">{{ count($invalidRows) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
                <h5 class="mb-0 text-dark fw-bold">Parsed Projects Preview</h5>
                
                <form method="POST" action="{{ route('projects.import.submit') }}">
                    @csrf
                    <input type="hidden" name="temp_file_path" value="{{ $tempFilePath }}">
                    
                    <div class="d-inline-flex gap-2">
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-sm" {{ count($validRows) === 0 ? 'disabled' : '' }}>
                            <i class="bi bi-cloud-arrow-up me-1"></i> Confirm & Import ({{ count($validRows) }} Projects)
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
                                <th>Project Name</th>
                                <th>Type</th>
                                <th>Client Company</th>
                                <th>Team Leader</th>
                                <th>Budget</th>
                                <th>Priority</th>
                                <th>AMC Details</th>
                                <th>Warnings / Errors</th>
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
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $row['name'] }}</div>
                                        @if(!empty($row['project_code']))
                                            <div class="fs-8 text-muted mt-1"><span class="fw-semibold text-secondary">Code:</span> {{ $row['project_code'] }}</div>
                                        @endif
                                        @if(!empty($row['url']))
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">URL:</span> <a href="{{ $row['url'] }}" target="_blank" class="text-break">{{ $row['url'] }}</a></div>
                                        @endif
                                        @if(!empty($row['completed_date']))
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">Completed:</span> {{ $row['completed_date'] }}</div>
                                        @endif
                                        @if(!empty($row['advance_amount']))
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">Advance:</span> ₹{{ number_format($row['advance_amount'], 2) }}</div>
                                        @endif
                                        @if(!empty($row['balance_amount']))
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">Balance:</span> ₹{{ number_format($row['balance_amount'], 2) }}</div>
                                        @endif
                                        @if(!empty($row['manager_email']))
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">Manager:</span> {{ $row['manager_email'] }}</div>
                                        @endif
                                        @if(isset($row['progress_percentage']) && $row['progress_percentage'] !== '')
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">Progress:</span> {{ $row['progress_percentage'] }}%</div>
                                        @endif
                                        @if(!empty($row['notes']))
                                            <div class="fs-8 text-muted text-truncate" style="max-width: 250px;" title="{{ $row['notes'] }}"><span class="fw-semibold text-secondary">Notes:</span> {{ $row['notes'] }}</div>
                                        @endif
                                    </td>
                                    <td><span>{{ ucfirst($row['project_type']) }}</span></td>
                                    <td><span>{{ $row['client_name'] ?: '—' }}</span></td>
                                    <td><span class="fs-7 text-muted">{{ $row['leader_email'] ?: '—' }}</span></td>
                                    <td><span class="font-monospace fs-7">₹{{ number_format($row['budget'], 2) }}</span></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($row['priority']) }}</span>
                                    </td>
                                    <td>
                                        @if($row['amc_start_date'])
                                            <div class="fs-7"><span class="text-muted">Start:</span> {{ $row['amc_start_date'] }}</div>
                                            <div class="fs-7"><span class="text-muted">Freq:</span> {{ ucfirst($row['amc_frequency']) }}</div>
                                            <div class="fs-7"><span class="text-muted">Value:</span> ₹{{ number_format($row['amc_amount'], 2) }}</div>
                                            @if($row['amc_end_date'])
                                                <div class="fs-7"><span class="text-muted">Due:</span> {{ $row['amc_end_date'] }}</div>
                                            @endif
                                            <div class="fs-7"><span class="text-muted">Status:</span> <span class="badge bg-secondary-subtle text-secondary py-0">{{ ucfirst($row['amc_status']) }}</span></div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($row['warnings'])
                                            <span class="text-warning fs-7 fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $row['warnings'] }}</span>
                                        @else
                                            <span class="text-success fs-7"><i class="bi bi-check-lg me-1"></i>Ready to import</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Invalid Rows -->
                            @foreach($invalidRows as $index => $row)
                                <tr class="table-danger-subtle">
                                    <td class="text-center font-monospace text-muted">{{ count($validRows) + $index + 1 }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1">Invalid</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $row['name'] ?: '—' }}</div>
                                        @if(!empty($row['project_code']))
                                            <div class="fs-8 text-muted mt-1"><span class="fw-semibold text-secondary">Code:</span> {{ $row['project_code'] }}</div>
                                        @endif
                                        @if(!empty($row['url']))
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">URL:</span> <a href="{{ $row['url'] }}" target="_blank" class="text-break">{{ $row['url'] }}</a></div>
                                        @endif
                                        @if(!empty($row['completed_date']))
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">Completed:</span> {{ $row['completed_date'] }}</div>
                                        @endif
                                        @if(!empty($row['advance_amount']))
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">Advance:</span> ₹{{ number_format($row['advance_amount'], 2) }}</div>
                                        @endif
                                        @if(!empty($row['balance_amount']))
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">Balance:</span> ₹{{ number_format($row['balance_amount'], 2) }}</div>
                                        @endif
                                        @if(!empty($row['manager_email']))
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">Manager:</span> {{ $row['manager_email'] }}</div>
                                        @endif
                                        @if(isset($row['progress_percentage']) && $row['progress_percentage'] !== '')
                                            <div class="fs-8 text-muted"><span class="fw-semibold text-secondary">Progress:</span> {{ $row['progress_percentage'] }}%</div>
                                        @endif
                                        @if(!empty($row['notes']))
                                            <div class="fs-8 text-muted text-truncate" style="max-width: 250px;" title="{{ $row['notes'] }}"><span class="fw-semibold text-secondary">Notes:</span> {{ $row['notes'] }}</div>
                                        @endif
                                    </td>
                                    <td><span>{{ ucfirst($row['project_type']) }}</span></td>
                                    <td><span>{{ $row['client_name'] ?: '—' }}</span></td>
                                    <td><span class="fs-7 text-muted">{{ $row['leader_email'] ?: '—' }}</span></td>
                                    <td><span class="font-monospace fs-7">₹{{ number_format($row['budget'], 2) }}</span></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($row['priority']) }}</span>
                                    </td>
                                    <td>
                                        @if($row['amc_start_date'])
                                            <div class="fs-7"><span class="text-muted">Start:</span> {{ $row['amc_start_date'] }}</div>
                                            <div class="fs-7"><span class="text-muted">Freq:</span> {{ ucfirst($row['amc_frequency']) }}</div>
                                            <div class="fs-7"><span class="text-muted">Value:</span> ₹{{ number_format($row['amc_amount'], 2) }}</div>
                                            @if($row['amc_end_date'])
                                                <div class="fs-7"><span class="text-muted">Due:</span> {{ $row['amc_end_date'] }}</div>
                                            @endif
                                            <div class="fs-7"><span class="text-muted">Status:</span> <span class="badge bg-secondary-subtle text-secondary py-0">{{ ucfirst($row['amc_status']) }}</span></div>
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
