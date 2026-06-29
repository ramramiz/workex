@extends('layouts.app')

@section('title', 'Start Today Work - Select Customer')
@section('page-title', 'Select Customer')

@section('breadcrumb')
    <li class="breadcrumb-item active">Select Customer</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <!-- Heading -->
    <div class="text-center border-top pt-4 pb-0 mb-3">
        <h3 class="fw-bold text-dark mb-2">Select Customer</h3>
        <p class="text-secondary fs-6 mb-0">Choose a customer to view their stats and calling rooms.</p>
    </div>

    <!-- Grid of Customer Cards -->
    <div class="row g-4 justify-content-center">
        @foreach($clients as $client)
            @php
                $isSelected = ($selectedClientId == $client->id);
            @endphp
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card h-100 border shadow-xs hover-shadow-sm transition-all {{ $isSelected ? 'border-warning' : '' }}" style="border-radius: 16px; border-color: {{ $isSelected ? '#ffc107 !important; border-width: 2px;' : 'rgba(99, 102, 241, 0.15) !important;' }}">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header with Icon & Name -->
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px; background-color: #fef3c7 !important; color: #d97706 !important;">
                                    <i class="bi bi-person-fill fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-0" style="font-size: 16px;">{{ $client->company_name }}</h5>
                                    @if($isSelected)
                                        <span class="badge bg-warning text-dark mt-1" style="font-size: 10px;">Active Customer</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary mt-1" style="font-size: 10px;">Customer</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Contact Person -->
                            <div class="mb-3 text-secondary" style="font-size: 13px;">
                                <i class="bi bi-person me-1"></i> {{ $client->contact_person ?? 'N/A' }}
                            </div>

                            <!-- Description/Contact Info -->
                            <p class="text-secondary fs-8 mb-4">
                                <i class="bi bi-envelope me-1"></i> {{ $client->email ?? 'No email provided' }} <br>
                                <i class="bi bi-telephone me-1"></i> {{ $client->phone ?? 'No phone provided' }}
                            </p>
                        </div>

                        <!-- Select Action Button -->
                        <div class="mt-auto">
                            <form method="POST" action="{{ route('leads.start-work.update-customer') }}">
                                @csrf
                                <input type="hidden" name="client_id" value="{{ $client->id }}">
                                <button type="submit" class="btn btn-warning w-100 fw-bold btn-sm d-flex align-items-center justify-content-center gap-1.5 py-2.5 text-dark" style="border-radius: 10px;">
                                    Select Customer <i class="bi bi-arrow-right-short"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .hover-shadow-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }
</style>
@endsection
