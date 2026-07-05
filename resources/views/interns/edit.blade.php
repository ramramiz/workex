@extends('layouts.app')

@section('title', 'Edit Intern')
@section('page-title', 'Edit Intern')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('interns.index') }}">Interns</a></li>
    <li class="breadcrumb-item active">Edit Intern</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark">Edit Intern Details</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('interns.update', $intern->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2 fw-semibold">Personal Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $intern->name) }}" required placeholder="e.g. John Doe">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $intern->email) }}" required placeholder="e.g. john@domain.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $intern->phone) }}" placeholder="e.g. +91 98765 43210">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Intern Photo</label>
                            <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                            <div class="form-text fs-8 text-muted" style="font-size: 11px;">Leave empty to keep current photo. JPEG, PNG. Max: 2MB.</div>
                            @if($intern->photo)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $intern->photo) }}" alt="Current Photo" class="img-thumbnail" style="max-height: 80px;">
                                </div>
                            @endif
                            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2 fw-semibold">Internship Placement</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required onchange="filterDesignations()">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $intern->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-medium">Designation / Role Title</label>
                            <select name="designation_id" id="designation_id" class="form-select @error('designation_id') is-invalid @enderror">
                                <option value="">Select Designation</option>
                            </select>
                            @error('designation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-medium">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', $intern->joining_date ? $intern->joining_date->format('Y-m-d') : '') }}" required>
                            @error('joining_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-medium">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $intern->end_date ? $intern->end_date->format('Y-m-d') : '') }}" required>
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $intern->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ old('status', $intern->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $intern->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('interns.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Update Intern</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Documents Manager Card -->
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark">Uploaded Documents</h5>
            </div>
            <div class="card-body p-4">
                <!-- List existing documents -->
                <div class="table-responsive mb-4">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title / Description</th>
                                <th>File Name</th>
                                <th>Size</th>
                                <th>Upload Date</th>
                                <th>Uploaded By</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($intern->uploadedDocuments as $doc)
                                <tr>
                                    <td><span class="fw-semibold text-dark">{{ $doc->title }}</span></td>
                                    <td><code class="text-muted">{{ $doc->file_name }}</code></td>
                                    <td>{{ $doc->file_size_human }}</td>
                                    <td>{{ $doc->created_at->format('d M Y h:i A') }}</td>
                                    <td>{{ $doc->uploader->name ?? 'System' }}</td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('documents.view', $doc) }}" target="_blank" class="btn btn-outline-info btn-sm" title="View Document">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('documents.download', $doc) }}" class="btn btn-outline-primary btn-sm" title="Download">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <form method="POST" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('Are you sure you want to delete this document?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-folder2-open text-primary fs-3 d-block mb-2"></i>
                                        No documents uploaded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Document Upload Form -->
                <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2 fw-semibold">Upload New Document</h6>
                <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="documentable_type" value="intern">
                    <input type="hidden" name="documentable_id" value="{{ $intern->id }}">

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-medium">Document Title <span class="text-muted">(Optional)</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Offer Letter, ID Proof">
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-medium">Select File <span class="text-danger">*</span></label>
                            <input type="file" name="document" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-upload me-1"></i> Upload
                            </button>
                        </div>
                    </div>
                    <div class="form-text fs-8 text-muted mt-2" style="font-size: 11px;">Supported formats: PDF, DOC, DOCX, XLS, XLSX, Images. Max: 10MB.</div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const departments = @json($departments);
    const selectedDesignationId = "{{ old('designation_id', $intern->designation_id) }}";

    function filterDesignations() {
        const deptId = document.getElementById('department_id').value;
        const desigSelect = document.getElementById('designation_id');
        
        // Reset designations
        desigSelect.innerHTML = '<option value="">Select Designation</option>';
        
        if (!deptId) return;
        
        const department = departments.find(d => d.id == deptId);
        if (department && department.designations) {
            department.designations.forEach(desig => {
                const opt = document.createElement('option');
                opt.value = desig.id;
                opt.textContent = desig.name;
                if (desig.id == selectedDesignationId) {
                    opt.selected = true;
                }
                desigSelect.appendChild(opt);
            });
        }
    }

    // Run on load to select active designation
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('department_id').value) {
            filterDesignations();
        }
    });
</script>
@endpush
