@extends('layouts.app')

@section('title', 'Log a Bug')
@section('page-title', 'Log a Bug')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('bugs.index') }}">Bugs</a></li>
    <li class="breadcrumb-item active">Log Bug</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">File an Issue / Bug</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('bugs.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Issue Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="e.g. Login button fails on Firefox browser">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Project board <span class="text-danger">*</span></label>
                            <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                <option value="">-- Choose Project --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ (old('project_id') == $p->id || request('project_id') == $p->id) ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Assignee / Developer</label>
                            <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">-- Choose Developer --</option>
                                @foreach($developers as $dev)
                                    <option value="{{ $dev->id }}" {{ old('assigned_to') == $dev->id ? 'selected' : '' }}>{{ $dev->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Steps to Reproduce</label>
                        <textarea name="steps_to_reproduce" class="form-control @error('steps_to_reproduce') is-invalid @enderror" rows="3" placeholder="1. Go to page X&#10;2. Click on button Y&#10;3. See error Z">{{ old('steps_to_reproduce') }}</textarea>
                        @error('steps_to_reproduce')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Related Link / URL</label>
                        <input type="text" name="link" class="form-control @error('link') is-invalid @enderror" value="{{ old('link') }}" placeholder="e.g. http://127.0.0.1:8000/some-page or relevant repository url">
                        @error('link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Detailed Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="More context or details about the issue..." required>{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Screenshots / Reference Images <span class="text-muted fs-8">(Optional, Up to 3 images)</span></label>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="card p-3 border border-light-subtle shadow-xs">
                                    <label class="form-label fs-7 fw-bold text-dark mb-1">Screenshot 1</label>
                                    <input type="file" name="screenshots[]" accept="image/*" class="form-control form-control-sm bug-file-input" data-preview="preview-1">
                                    <div class="mt-2 text-center d-none" id="preview-container-1">
                                        <img id="preview-1" src="" class="img-fluid rounded border" style="max-height: 120px; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card p-3 border border-light-subtle shadow-xs">
                                    <label class="form-label fs-7 fw-bold text-dark mb-1">Screenshot 2</label>
                                    <input type="file" name="screenshots[]" accept="image/*" class="form-control form-control-sm bug-file-input" data-preview="preview-2">
                                    <div class="mt-2 text-center d-none" id="preview-container-2">
                                        <img id="preview-2" src="" class="img-fluid rounded border" style="max-height: 120px; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card p-3 border border-light-subtle shadow-xs">
                                    <label class="form-label fs-7 fw-bold text-dark mb-1">Screenshot 3</label>
                                    <input type="file" name="screenshots[]" accept="image/*" class="form-control form-control-sm bug-file-input" data-preview="preview-3">
                                    <div class="mt-2 text-center d-none" id="preview-container-3">
                                        <img id="preview-3" src="" class="img-fluid rounded border" style="max-height: 120px; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-uppercase text-muted fs-8 font-monospace mb-2 border-bottom pb-2">Environment Information (Auto-filled)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label fs-7">Browser Agent</label>
                            <input type="text" name="browser_info" id="browser_info" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-7">Operating System (OS)</label>
                            <input type="text" name="os_info" id="os_info" class="form-control form-control-sm" readonly>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('bugs.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">File Bug</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Auto read user environment
        const ua = navigator.userAgent;
        let os = "Unknown OS";
        let browser = "Unknown Browser";

        if (ua.indexOf("Win") != -1) os = "Windows";
        else if (ua.indexOf("Mac") != -1) os = "MacOS";
        else if (ua.indexOf("X11") != -1) os = "UNIX";
        else if (ua.indexOf("Linux") != -1) os = "Linux";

        if (ua.indexOf("Chrome") != -1) browser = "Chrome";
        else if (ua.indexOf("Safari") != -1) browser = "Safari";
        else if (ua.indexOf("Firefox") != -1) browser = "Firefox";
        else if (ua.indexOf("MSIE") != -1 || !!document.documentMode == true) browser = "IE";

        document.getElementById('browser_info').value = browser;
        document.getElementById('os_info').value = os + " (Agent: " + navigator.platform + ")";

        // Image preview logic
        document.querySelectorAll('.bug-file-input').forEach(input => {
            input.addEventListener('change', function() {
                const previewId = this.dataset.preview;
                const index = previewId.split('-')[1];
                const container = document.getElementById('preview-container-' + index);
                const previewImg = document.getElementById(previewId);

                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        container.classList.remove('d-none');
                    }
                    reader.readAsDataURL(this.files[0]);
                } else {
                    previewImg.src = '';
                    container.classList.add('d-none');
                }
            });
        });
    });
</script>
@endpush
