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
                        <label class="form-label">Detailed Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="More context or details about the issue...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Screenshots / Reference Images <span class="text-muted fs-8">(Up to 5 images)</span></label>
                        
                        <!-- Upload Trigger Area -->
                        <div id="upload-trigger" class="border border-dashed border-primary rounded-3 p-4 text-center bg-light-subtle hover-bg-light transition-all duration-200 mb-3" style="border-width: 2px !important; cursor: pointer;">
                            <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 32px;"></i>
                            <h6 class="mt-2 mb-1 text-dark fw-bold">Upload and annotate image</h6>
                            <p class="text-muted fs-8 mb-0">Select an image to mark or draw circles on before filing the bug.</p>
                            <input type="file" id="bug-image-input" accept="image/*" class="d-none">
                        </div>

                        <!-- Screenshots Preview Grid -->
                        <div class="row g-3" id="screenshots-preview-grid">
                            <!-- Previews will be dynamically appended here via JS -->
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

<!-- Image Annotation Markup Modal -->
<div class="modal fade" id="imageMarkupModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="imageMarkupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="imageMarkupModalLabel">Annotate Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="markup-modal-close"></button>
            </div>
            <div class="modal-body text-center pt-2">
                <!-- Toolbar -->
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3 bg-light p-2 rounded-3 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-dark active" id="tool-pencil" title="Pencil Tool">
                        <i class="bi bi-pencil-fill me-1"></i> Pencil
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark" id="tool-circle" title="Circle Tool">
                        <i class="bi bi-circle me-1"></i> Circle
                    </button>
                    <div class="vr mx-2"></div>
                    <!-- Colors -->
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn active" data-color="#ef4444" style="width: 24px; height: 24px; background-color: #ef4444;" title="Red"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn" data-color="#3b82f6" style="width: 24px; height: 24px; background-color: #3b82f6;" title="Blue"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn" data-color="#22c55e" style="width: 24px; height: 24px; background-color: #22c55e;" title="Green"></button>
                    <button type="button" class="btn btn-sm rounded-circle border-0 p-0 color-btn" data-color="#eab308" style="width: 24px; height: 24px; background-color: #eab308;" title="Yellow"></button>
                    <div class="vr mx-2"></div>
                    <button type="button" class="btn btn-sm btn-danger text-white" id="btn-clear-canvas">
                        <i class="bi bi-trash3-fill me-1"></i> Clear
                    </button>
                </div>
                
                <!-- Canvas Container -->
                <div class="d-flex justify-content-center align-items-center border rounded-3 bg-dark overflow-auto p-2" style="max-height: 400px; min-height: 250px;">
                    <canvas id="markup-canvas" style="cursor: crosshair; display: block; max-width: 100%; height: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></canvas>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-save-markup">Save Annotation</button>
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

        // SCREENSHOT UPLOAD & CANVAS MARKUP LOGIC
        const uploadTrigger = document.getElementById('upload-trigger');
        const bugImageInput = document.getElementById('bug-image-input');
        const previewGrid = document.getElementById('screenshots-preview-grid');
        const canvas = document.getElementById('markup-canvas');
        const ctx = canvas ? canvas.getContext('2d') : null;
        
        let drawing = false;
        let currentTool = 'pencil'; // pencil or circle
        let currentColor = '#ef4444'; // default red
        let currentLineWidth = 3;
        let startX = 0;
        let startY = 0;
        let savedImageData = null;
        let loadedImg = null;

        // Click trigger area
        if (uploadTrigger && bugImageInput) {
            uploadTrigger.addEventListener('click', () => {
                if (document.querySelectorAll('.screenshot-preview-card').length >= 5) {
                    alert('You can only upload up to 5 images.');
                    return;
                }
                bugImageInput.click();
            });
        }

        // File change
        if (bugImageInput && canvas && ctx) {
            bugImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        loadedImg = new Image();
                        loadedImg.onload = function() {
                            const maxDimension = 600;
                            let width = loadedImg.width;
                            let height = loadedImg.height;
                            if (width > maxDimension || height > maxDimension) {
                                if (width > height) {
                                    height = Math.round((height * maxDimension) / width);
                                    width = maxDimension;
                                } else {
                                    width = Math.round((width * maxDimension) / height);
                                    height = maxDimension;
                                }
                            }
                            canvas.width = width;
                            canvas.height = height;
                            ctx.drawImage(loadedImg, 0, 0, width, height);

                            const markupModalEl = document.getElementById('imageMarkupModal');
                            const modal = new bootstrap.Modal(markupModalEl);
                            modal.show();
                        };
                        loadedImg.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Canvas drawing methods
            function getCoordinates(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: (clientX - rect.left) * (canvas.width / rect.width),
                    y: (clientY - rect.top) * (canvas.height / rect.height)
                };
            }

            function startDrawing(e) {
                drawing = true;
                savedImageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const coords = getCoordinates(e);
                startX = coords.x;
                startY = coords.y;

                if (currentTool === 'pencil') {
                    ctx.beginPath();
                    ctx.moveTo(startX, startY);
                    ctx.strokeStyle = currentColor;
                    ctx.lineWidth = currentLineWidth;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                }
            }

            function draw(e) {
                if (!drawing) return;
                if (e.touches) e.preventDefault();

                const coords = getCoordinates(e);
                const x = coords.x;
                const y = coords.y;

                if (currentTool === 'pencil') {
                    ctx.lineTo(x, y);
                    ctx.stroke();
                } else if (currentTool === 'circle') {
                    ctx.putImageData(savedImageData, 0, 0);
                    const radius = Math.sqrt(Math.pow(x - startX, 2) + Math.pow(y - startY, 2));
                    ctx.beginPath();
                    ctx.arc(startX, startY, radius, 0, 2 * Math.PI);
                    ctx.strokeStyle = currentColor;
                    ctx.lineWidth = currentLineWidth;
                    ctx.stroke();
                }
            }

            function stopDrawing() {
                if (drawing) {
                    drawing = false;
                    if (currentTool === 'pencil') {
                        ctx.closePath();
                    }
                }
            }

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseleave', stopDrawing);

            canvas.addEventListener('touchstart', startDrawing, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            canvas.addEventListener('touchend', stopDrawing);

            // Tools selectors
            const pencilBtn = document.getElementById('tool-pencil');
            const circleBtn = document.getElementById('tool-circle');
            if (pencilBtn && circleBtn) {
                pencilBtn.addEventListener('click', () => {
                    currentTool = 'pencil';
                    pencilBtn.classList.add('active');
                    circleBtn.classList.remove('active');
                });
                circleBtn.addEventListener('click', () => {
                    currentTool = 'circle';
                    circleBtn.classList.add('active');
                    pencilBtn.classList.remove('active');
                });
            }

            // Colors selection
            const colorBtns = document.querySelectorAll('.color-btn');
            colorBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    colorBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentColor = this.getAttribute('data-color');
                });
            });

            // Clear
            const clearBtn = document.getElementById('btn-clear-canvas');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    if (loadedImg) {
                        ctx.drawImage(loadedImg, 0, 0, canvas.width, canvas.height);
                    }
                });
            }

            // Save Annotation
            const saveMarkupBtn = document.getElementById('btn-save-markup');
            if (saveMarkupBtn) {
                saveMarkupBtn.addEventListener('click', function() {
                    const base64 = canvas.toDataURL('image/jpeg', 0.85);
                    const index = document.querySelectorAll('.screenshot-preview-card').length;

                    // Append preview
                    const cardHtml = `
                        <div class="col-6 col-md-4 screenshot-preview-card" id="screenshot-card-${index}">
                            <div class="card h-100 border border-light-subtle shadow-xs position-relative overflow-hidden">
                                <img src="${base64}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                <div class="card-body p-2 d-flex justify-content-between align-items-center bg-light">
                                    <span class="fs-8 text-muted fw-semibold">Screenshot ${index + 1}</span>
                                    <button type="button" class="btn btn-link text-danger p-0 m-0 btn-delete-screenshot" onclick="deleteScreenshot(${index})" title="Delete Image">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="screenshots[]" value="${base64}">
                            </div>
                        </div>
                    `;
                    previewGrid.insertAdjacentHTML('beforeend', cardHtml);

                    const modalEl = document.getElementById('imageMarkupModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    bugImageInput.value = '';
                    updateUploadTriggerVisibility();
                });
            }

            // Hidden clean up
            const modalEl = document.getElementById('imageMarkupModal');
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function () {
                    bugImageInput.value = '';
                });
            }
        }
    });

    function deleteScreenshot(index) {
        const card = document.getElementById(`screenshot-card-${index}`);
        if (card) {
            card.remove();
            // Re-index remaining preview cards labels
            document.querySelectorAll('.screenshot-preview-card').forEach((el, idx) => {
                el.id = `screenshot-card-${idx}`;
                el.querySelector('.fs-8').textContent = `Screenshot ${idx + 1}`;
                el.querySelector('.btn-delete-screenshot').setAttribute('onclick', `deleteScreenshot(${idx})`);
            });
            updateUploadTriggerVisibility();
        }
    }

    function updateUploadTriggerVisibility() {
        const trigger = document.getElementById('upload-trigger');
        const count = document.querySelectorAll('.screenshot-preview-card').length;
        if (count >= 5) {
            trigger.style.pointerEvents = 'none';
            trigger.style.opacity = '0.5';
            trigger.querySelector('h6').textContent = 'Maximum 5 images uploaded';
        } else {
            trigger.style.pointerEvents = 'auto';
            trigger.style.opacity = '1';
            trigger.querySelector('h6').textContent = 'Upload and annotate image';
        }
    }
</script>
@endpush
