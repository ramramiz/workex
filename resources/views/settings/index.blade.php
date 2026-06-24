@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'System Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Navigation Sidebar for Settings -->
    <div class="col-12 col-md-3">
        <div class="card">
            @include('settings.sidebar')
        </div>
    </div>

    <!-- Right Content Card -->
    <div class="col-12 col-md-9">
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs m-0 border-bottom-0 px-3" id="settingsTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-3" id="company-tab" data-bs-toggle="tab" data-bs-target="#company" type="button" role="tab">Company</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="work-tab" data-bs-toggle="tab" data-bs-target="#work" type="button" role="tab">Work Configuration</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice" type="button" role="tab">Invoices & Financials</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">Notifications</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3" id="mailbox-tab" data-bs-toggle="tab" data-bs-target="#mailbox" type="button" role="tab">Mailbox Config</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="tab-content" id="settingsTabsContent">
                        <!-- Company Settings -->
                        <div class="tab-pane fade show active" id="company" role="tabpanel">
                            <div class="row g-3">
                                @foreach($settings['company'] ?? [] as $s)
                                    @php
                                        $val = $s->value;
                                        if (isset($company) && $company) {
                                            if ($s->key === 'company_name') $val = $company->name ?: $s->value;
                                            elseif ($s->key === 'company_email') $val = $company->email ?: $s->value;
                                            elseif ($s->key === 'company_auth_person_name') $val = $company->auth_person_name ?: $s->value;
                                            elseif ($s->key === 'company_auth_person_email') $val = $company->auth_person_email ?: $s->value;
                                            elseif ($s->key === 'company_phone') $val = $company->phone ?: $s->value;
                                            elseif ($s->key === 'company_address') $val = $company->address ?: $s->value;
                                            elseif ($s->key === 'company_gst') $val = $company->gst ?: $s->value;
                                        }
                                    @endphp
                                    <div class="col-12 {{ $s->type === 'textarea' ? 'col-md-12' : 'col-md-6' }}">
                                        <label class="form-label fw-medium">{{ $s->label }}</label>
                                        @if($s->type === 'textarea')
                                            <textarea name="{{ $s->key }}" class="form-control" rows="3">{{ $val }}</textarea>
                                        @elseif($s->type === 'file')
                                            <div class="d-flex align-items-center gap-3">
                                                @if($val)
                                                    <div class="border rounded p-1 bg-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; overflow: hidden; flex-shrink: 0;">
                                                        <img src="{{ asset('storage/' . $val) }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                    </div>
                                                @endif
                                                <input type="file" name="{{ $s->key }}" class="form-control" accept="image/*">
                                            </div>
                                        @else
                                            <input type="text" name="{{ $s->key }}" class="form-control" value="{{ $val }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Work Settings -->
                        <div class="tab-pane fade" id="work" role="tabpanel">
                            <div class="row g-3">
                                <input type="hidden" name="week_off_days_present" value="1">
                                @foreach($settings['work'] ?? [] as $s)
                                    @if($s->key === 'working_days') @continue @endif
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-medium">{{ $s->label }}</label>
                                        @if($s->key === 'work_start_time' || $s->key === 'work_end_time')
                                            <input type="time" name="{{ $s->key }}" class="form-control" value="{{ $s->value }}">
                                        @elseif($s->key === 'week_start_day')
                                            <select name="{{ $s->key }}" class="form-select">
                                                <option value="mon" {{ $s->value === 'mon' ? 'selected' : '' }}>Monday</option>
                                                <option value="tue" {{ $s->value === 'tue' ? 'selected' : '' }}>Tuesday</option>
                                                <option value="wed" {{ $s->value === 'wed' ? 'selected' : '' }}>Wednesday</option>
                                                <option value="thu" {{ $s->value === 'thu' ? 'selected' : '' }}>Thursday</option>
                                                <option value="fri" {{ $s->value === 'fri' ? 'selected' : '' }}>Friday</option>
                                                <option value="sat" {{ $s->value === 'sat' ? 'selected' : '' }}>Saturday</option>
                                                <option value="sun" {{ $s->value === 'sun' ? 'selected' : '' }}>Sunday</option>
                                            </select>
                                        @elseif($s->key === 'week_off_days')
                                            @php
                                                $selectedOffs = explode(',', $s->value ?? 'sun');
                                                $daysList = [
                                                    'mon' => 'Monday',
                                                    'tue' => 'Tuesday',
                                                    'wed' => 'Wednesday',
                                                    'thu' => 'Thursday',
                                                    'fri' => 'Friday',
                                                    'sat' => 'Saturday',
                                                    'sun' => 'Sunday'
                                                ];
                                            @endphp
                                            <div class="d-flex flex-wrap gap-2 mt-1">
                                                @foreach($daysList as $dayKey => $dayName)
                                                    <div class="form-check form-check-inline border rounded p-2 px-3 bg-light" style="margin-right: 0;">
                                                        <input class="form-check-input ms-0 me-2" type="checkbox" name="week_off_days[]" value="{{ $dayKey }}" id="off-{{ $dayKey }}" {{ in_array($dayKey, $selectedOffs) ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-medium" for="off-{{ $dayKey }}">{{ $dayName }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <input type="text" name="{{ $s->key }}" class="form-control" value="{{ $s->value }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Invoice Settings -->
                        <div class="tab-pane fade" id="invoice" role="tabpanel">
                            <div class="row g-3">
                                @foreach($settings['invoice'] ?? [] as $s)
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-medium">{{ $s->label }}</label>
                                        <input type="text" name="{{ $s->key }}" class="form-control" value="{{ $s->value }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Notifications Settings -->
                        <div class="tab-pane fade" id="notifications" role="tabpanel">
                            <div class="row g-3">
                                @foreach($settings['notifications'] ?? [] as $s)
                                    <div class="col-12 col-md-6">
                                        <div class="form-check form-switch mt-2">
                                            <input type="hidden" name="{{ $s->key }}" value="0">
                                            <input class="form-check-input" type="checkbox" name="{{ $s->key }}" value="1" id="switch-{{ $s->key }}" {{ $s->value == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-medium ms-2" for="switch-{{ $s->key }}">{{ $s->label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Mailbox Settings -->
                        <div class="tab-pane fade" id="mailbox" role="tabpanel">
                            <div class="row g-3">
                                @foreach($settings['mailbox'] ?? [] as $s)
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-medium">{{ $s->label }}</label>
                                        @if($s->type === 'boolean')
                                            <div class="form-check form-switch mt-2">
                                                <input type="hidden" name="{{ $s->key }}" value="0">
                                                <input class="form-check-input" type="checkbox" name="{{ $s->key }}" value="1" id="switch-{{ $s->key }}" {{ $s->value == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-medium ms-2" for="switch-{{ $s->key }}">{{ $s->label }}</label>
                                            </div>
                                        @elseif($s->key === 'mailbox_imap_encryption')
                                            <select name="{{ $s->key }}" class="form-select">
                                                <option value="ssl" {{ $s->value === 'ssl' ? 'selected' : '' }}>SSL</option>
                                                <option value="tls" {{ $s->value === 'tls' ? 'selected' : '' }}>TLS</option>
                                                <option value="none" {{ $s->value === 'none' ? 'selected' : '' }}>None</option>
                                            </select>
                                        @elseif($s->type === 'password')
                                            <input type="password" name="{{ $s->key }}" class="form-control" value="{{ $s->value }}">
                                        @else
                                            <input type="text" name="{{ $s->key }}" class="form-control" value="{{ $s->value }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3 mt-4">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
