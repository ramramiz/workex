@extends('layouts.app')

@section('title', 'Hiring & Vacancies')
@section('page-title', 'Hiring & Vacancies')

@section('breadcrumb')
    <li class="breadcrumb-item active">Hiring & Vacancies</li>
@endsection

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <h5 class="mb-0 fw-bold text-dark">Job Vacancies</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('job-vacancies.mail-logs') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius: 8px;">
                <i class="bi bi-envelope-paper-fill"></i> Sent Mails Log
            </a>
            <a href="{{ route('job-vacancies.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2" style="border-radius: 8px;">
                <i class="bi bi-plus-lg"></i> Register New Vacancy
            </a>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3" style="width: 25%">Job Title</th>
                        <th class="py-3" style="width: 15%">Department</th>
                        <th class="py-3" style="width: 12%">Type & Location</th>
                        <th class="py-3 text-center" style="width: 10%">Status</th>
                        <th class="py-3 text-center" style="width: 12%">Applications</th>
                        <th class="py-3" style="width: 13%">Share Link</th>
                        <th class="pe-4 py-3 text-end" style="width: 13%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vacancies as $vacancy)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-dark">{{ $vacancy->title }}</div>
                                <small class="text-muted">Created {{ $vacancy->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                @if($vacancy->department)
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1.5 rounded">
                                        {{ $vacancy->department->name }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $vacancy->job_type }}</div>
                                <small class="text-muted"><i class="bi bi-geo-alt-fill me-1"></i>{{ $vacancy->location ?? 'Not Specified' }}</small>
                            </td>
                            <td class="text-center">
                                @if($vacancy->status === 'active')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded">
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($vacancy->applications_count > 0)
                                    <a href="{{ route('job-vacancies.applications', $vacancy->id) }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-semibold">
                                        {{ $vacancy->applications_count }} Applications
                                    </a>
                                @else
                                    <span class="text-muted">0 Applications</span>
                                @endif
                            </td>
                            <td>
                                <div class="input-group input-group-sm" style="max-width: 180px;">
                                    <input type="text" class="form-control bg-light border-end-0" readonly 
                                           value="{{ route('careers.vacancy.show', $vacancy->token) }}" 
                                           id="link-{{ $vacancy->id }}">
                                    @php
                                        $shareText = "Dear Candidate, thank you for your interest in joining Techsoul. We are currently accepting applications for the {$vacancy->title} position. Kindly register and submit your details through our careers portal: " . route('careers.vacancy.show', $vacancy->token);
                                    @endphp
                                    <button class="btn btn-outline-secondary border-start-0" type="button" 
                                            onclick="copyToClipboard('{{ addslashes($shareText) }}', this)"
                                            data-bs-toggle="tooltip" title="Copy Share Message">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('job-vacancies.applications', $vacancy->id) }}" 
                                       class="btn btn-sm btn-light border" 
                                       data-bs-toggle="tooltip" 
                                       title="View Applications">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('job-vacancies.edit', $vacancy->id) }}" 
                                       class="btn btn-sm btn-light border text-primary" 
                                       data-bs-toggle="tooltip" 
                                       title="Edit Vacancy">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('job-vacancies.destroy', $vacancy->id) }}" method="POST" 
                                          class="d-inline" onsubmit="return confirm('Are you sure you want to delete this vacancy and all its applications?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" 
                                                data-bs-toggle="tooltip" 
                                                title="Delete Vacancy">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted mb-3">
                                    <i class="bi bi-briefcase text-secondary display-4 d-block mb-3"></i>
                                    No job vacancies registered yet.
                                </div>
                                <a href="{{ route('job-vacancies.create') }}" class="btn btn-primary btn-sm px-4 py-2">
                                    Create First Vacancy
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vacancies->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $vacancies->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            const icon = button.querySelector('i');
            
            // Success State
            icon.classList.remove('bi-clipboard');
            icon.classList.add('bi-check-lg', 'text-success');
            button.classList.add('border-success');
            
            // Revert State
            setTimeout(() => {
                icon.classList.remove('bi-check-lg', 'text-success');
                icon.classList.add('bi-clipboard');
                button.classList.remove('border-success');
            }, 1500);
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
        });
    }
</script>
@endsection
