@extends('template.master', ['pageTitle' => __('index.logo_settings')])

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <style>
        .upload-block {
            border: 2px dashed #cbd5e0;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 200px;
            position: relative;
        }

        .upload-block:hover,
        .upload-block.dragover {
            border-color: #4e73df;
            background-color: #e7f0ff;
        }

        .upload-text {
            color: #6c757d;
            font-size: 0.95rem;
            margin-top: 0.5rem;
        }

        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border: 2px solid #dee2e6;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .delete-btn {
            position: absolute;
            top: 6px;
            right: 6px;
            background: rgba(255, 255, 255, 0.8);
            border: none;
            color: #dc3545;
            padding: 4px 6px;
            border-radius: 50%;
            font-size: 0.75rem;
            line-height: 1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .delete-btn:hover {
            background: #dc3545;
            color: #fff;
        }

        /* Quill editor */
        #editor {
            min-height: 300px;
            background-color: #fff;

            @error('value')
                border: 1px solid #dc3545;
            @enderror
        }

        .ql-editor {
            min-height: 300px;
        }

        .ql-toolbar {
            background-color: #f8f9fa;
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
        }
    </style>
@endpush

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Logo & Favicon</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/settings/logoUpdate') }}">Settings</a></li>
                            <li class="breadcrumb-item">Logo & Favicon</li>
                        </ol>
                    </nav>
                </div>
            </div>

        </div>
    </div>

    <div class="container-fluid">
        <div class="row">

            <!-- LEFT SIDE -->
            <div class="col-md-6">

                <!-- Logo Upload -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Upload Logo</h5>
                    </div>
                    <div class="card-body">
                        <form id="logoForm" action="{{ route('settings.logo.save') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="cropped_image" id="logo_cropped_image">

                            <label class="form-label fw-semibold">Logo</label>
                            <div id="logo-block" class="upload-block text-center" data-type="logo">
                                @if (isset($logo) && $logo)
                                    <button type="button" class="delete-btn delete-image" data-field="logo"
                                        data-url="{{ route('settings.delete.logo') }}">
                                        <i class="fa fa-times"></i>
                                    </button>
                                    <img id="logo-preview" src="{{ asset('storage/' . $logo) }}" class="image-preview"
                                        alt="Logo">
                                @else
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                    <p class="upload-text">Click to select logo</p>
                                @endif
                            </div>

                            <input type="file" name="logo" id="logoInput" class="d-none"
                                accept=".png,.jpg,.jpeg,.svg">
                            <span class="text-danger small">
                                @error('logo')
                                    {{ $message }}
                                @enderror
                            </span>

                            <button type="submit" class="btn btn-primary mt-3">
                                <i class="fas fa-save me-1"></i> Update Logo
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Favicon Upload -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Upload Favicon</h5>
                    </div>
                    <div class="card-body">
                        <form id="faviconForm" action="{{ route('settings.favicon.save') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="cropped_image" id="favicon_cropped_image">

                            <label class="form-label fw-semibold">Favicon</label>
                            <div id="favicon-block" class="upload-block text-center" data-type="favicon">
                                @if (isset($favicon) && $favicon)
                                    <button type="button" class="delete-btn delete-image" data-field="favicon"
                                        data-url="{{ route('settings.delete.favicon') }}">
                                        <i class="fa fa-times"></i>
                                    </button>
                                    <img id="favicon-preview" src="{{ asset('storage/' . $favicon) }}" class="image-preview"
                                        alt="Favicon">
                                @else
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                    <p class="upload-text">Click to select favicon</p>
                                @endif
                            </div>

                            <input type="file" name="favicon" id="faviconInput" class="d-none"
                                accept=".png,.ico,.jpg,.jpeg,.svg">
                            <span class="text-danger small">
                                @error('favicon')
                                    {{ $message }}
                                @enderror
                            </span>

                            <button type="submit" class="btn btn-primary mt-3">
                                <i class="fas fa-save me-1"></i> Update Favicon
                            </button>
                        </form>
                    </div>
                </div>


                <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Upload Barcode</h5>
                </div>

                <div class="card-body">
                    <form id="barcodeForm" action="{{ route('settings.barcode.save') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <label class="form-label fw-semibold">Barcode</label>

                        <div id="barcode-block" class="upload-block text-center" data-type="barcode">
                            @if (isset($barcode) && $barcode)
                                <button type="button"
                                    class="delete-btn delete-image"
                                    data-field="barcode"
                                    data-url="{{ route('settings.delete.barcode') }}">
                                    <i class="fa fa-times"></i>
                                </button>

                                <img id="barcode-preview"
                                    src="{{ asset('storage/' . $barcode) }}"
                                    class="image-preview"
                                    alt="Barcode">
                            @else
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                <p class="upload-text">Click to select barcode</p>
                            @endif
                        </div>

                        <input type="file" name="barcode" id="barcodeInput" class="d-none"
                            accept=".png,.jpg,.jpeg,.svg">

                        <span class="text-danger small">
                            @error('barcode')
                                {{ $message }}
                            @enderror
                        </span>

                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="fas fa-save me-1"></i> Update Barcode
                        </button>
                    </form>
                </div>
            </div>


            </div>

            <!-- RIGHT SIDE -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Site Details</h5>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                                {{ session('success') }}
                            </div>
                        @endif
                        <form id="siteDetailsForm" method="POST" action="{{ route('settings.sites.update') }}">
                            @csrf

                            <!-- SEO & Meta Details -->
                            <div class="col-12 mb-4">
                                <h5>SEO & Meta Details</h5>
                                <hr>
                            </div>

                            <div class="row">

                                <div class="mb-3 col-md-12">
                                    <label for="site_title" class="form-label">Site Title <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="site_title" id="site_title"
                                        value="{{ old('site_title', $site->site_title ?? '') }}"
                                        class="form-control @error('site_title') is-invalid @enderror">

                                    @error('site_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3 col-md-12">
                                    <label for="meta_site_title" class="form-label">Meta Title <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="meta_site_title" id="meta_site_title"
                                        value="{{ old('meta_site_title', $site->meta_site_title ?? '') }}"
                                        class="form-control @error('meta_site_title') is-invalid @enderror">
                                    @error('meta_site_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 col-md-12">
                                    <label for="meta_site_description" class="form-label">Meta Description <span
                                            class="text-danger">*</span></label>
                                    <textarea name="meta_site_description" id="meta_site_description" rows="5"
                                        class="form-control @error('meta_site_description') is-invalid @enderror">{{ old('meta_site_description', $site->meta_site_description ?? '') }}</textarea>

                                    @error('meta_site_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>


                            <div class="mt-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Submit
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupManualUpload(blockSelector, inputSelector, maxSize = null) {
                const block = document.querySelector(blockSelector);
                const input = document.querySelector(inputSelector);
                let cropper = null;
                let cropping = false;
                let objectUrl = null;

                // Utility: reset UI back to initial state (keeps delete button if present)
                function resetBlockToPlaceholder(type) {
                    // Keep delete button if present by only clearing/adding the inner upload content area.
                    // We'll use a child wrapper to hold upload content so delete button (absolute) remains.
                    let content = block.querySelector('.upload-content');
                    if (!content) {
                        // Build wrapper if it doesn't exist
                        content = document.createElement('div');
                        content.className = 'upload-content';
                        block.prepend(content);
                    }
                    content.innerHTML = `
            <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
            <p class="upload-text">Click to select ${type === 'favicon' ? 'favicon' : 'logo'}</p>
        `;
                }

                // Initialize placeholder wrapper if missing
                if (!block.querySelector('.upload-content')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'upload-content';
                    // If block currently has an img preview (server-side), move it into wrapper
                    const existingImg = block.querySelector('img.image-preview');
                    if (existingImg) {
                        wrapper.appendChild(existingImg.cloneNode(true));
                    } else {
                        wrapper.innerHTML = `
                <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                <p class="upload-text">Click to select ${block.dataset.type === 'favicon' ? 'favicon' : 'logo'}</p>
            `;
                    }
                    // clear block and append wrapper (preserve delete button if any)
                    // but don't remove delete-btn
                    const deleteBtn = block.querySelector('.delete-btn');
                    block.innerHTML = '';
                    if (deleteBtn) block.appendChild(deleteBtn);
                    block.appendChild(wrapper);
                }

                // Open file dialog
                block.addEventListener('click', (e) => {
                    if (e.target.closest('.delete-btn') || cropping) return;
                    input.click();
                });

                input.addEventListener('change', (e) => {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;

                    // Basic validations
                    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/x-icon',
                        'image/vnd.microsoft.icon'
                    ];
                    if (!allowedTypes.includes(file.type) && !file.name.match(/\.svg$/i)) {
                        alert('Please upload a PNG/JPEG/ICO image (SVG not supported for cropping).');
                        input.value = '';
                        return;
                    }
                    const maxFileMB = 5;
                    if (file.size > maxFileMB * 1024 * 1024) {
                        alert(`Please upload image smaller than ${maxFileMB} MB.`);
                        input.value = '';
                        return;
                    }

                    // Revoke old object url if any
                    if (objectUrl) {
                        URL.revokeObjectURL(objectUrl);
                        objectUrl = null;
                    }
                    objectUrl = URL.createObjectURL(file);

                    cropping = true;

                    // Prepare UI inside .upload-content so delete button is preserved
                    const content = block.querySelector('.upload-content');
                    content.innerHTML = `
            <div class="cropper-wrap" style="max-width:100%; text-align:center;">
                <img id="crop-image" src="${objectUrl}" style="max-width:100%; display:block; margin:auto; border-radius:8px;">
            </div>
            <div class="mt-2 d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-sm btn-success" id="applyCrop">Crop</button>
                <button type="button" class="btn btn-sm btn-secondary" id="cancelCrop">Cancel</button>
            </div>
        `;

                    const image = document.getElementById('crop-image');

                    // If SVG - skip cropper and just preview as-is and set hidden input to file data if needed.
                    if (file.name.match(/\.svg$/i)) {
                        // Convert SVG to data URL (we could just use objectUrl). For now, preview only.
                        // Optionally: send SVG directly to server without cropping.
                        cropping = false;
                        // Show preview in place of crop UI (simple)
                        const previewId = block.dataset.type + '-preview';
                        let img = document.getElementById(previewId);
                        if (!img) {
                            img = document.createElement('img');
                            img.id = previewId;
                            img.className = 'image-preview';
                        }
                        content.innerHTML = '';
                        content.appendChild(img);
                        img.src = objectUrl;
                        // set hidden input as objectURL won't be posted - you may submit original File via form
                        // cleanup
                        return;
                    }

                    // Create cropper. Destroy previous if any.
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }

                    cropper = new Cropper(image, {
                        aspectRatio: block.dataset.type === 'favicon' ? 1 : NaN,
                        viewMode: 2, // restrict the crop box to not exceed the canvas
                        autoCropArea: 0.8, // initial crop area covers 80% of image
                        responsive: true,
                        background: false,
                        center: true,
                        modal: true,
                        zoomable: true,
                        movable: true,
                        // ensure crop box is nicely sized after image loads
                        ready() {
                            try {
                                // Center crop box explicitly (defensive)
                                const imgData = cropper.getImageData();
                                const cropBoxData = cropper.getCropBoxData();
                                // If crop box > image (rare), reset it
                                if (cropBoxData.width > imgData.naturalWidth) {
                                    const w = Math.round(imgData.naturalWidth * 0.8);
                                    const h = block.dataset.type === 'favicon' ? w : Math.round(w *
                                        (cropBoxData.height / cropBoxData.width));
                                    cropper.setCropBoxData({
                                        width: w,
                                        height: h
                                    });
                                }
                                cropper.moveTo(0, 0); // center
                            } catch (err) {
                                // ignore
                            }
                        }
                    });

                    const applyBtn = document.getElementById('applyCrop');
                    const cancelBtn = document.getElementById('cancelCrop');

                    // helper to cleanup
                    function finishCropper() {
                        if (cropper) {
                            cropper.destroy();
                            cropper = null;
                        }
                        if (objectUrl) {
                            URL.revokeObjectURL(objectUrl);
                            objectUrl = null;
                        }
                        cropping = false;
                        input.value = '';
                    }

                    // Use 'once' option to ensure listener runs only once per UI creation
                    applyBtn.addEventListener('click', function(ev) {
                        ev.stopPropagation();
                        ev.preventDefault();

                        // compute desired output size
                        const devicePixelRatio = window.devicePixelRatio || 1;
                        let outW, outH;

                        // Use cropper.getData(true) to get rounded values for the crop box
                        const cropData = cropper.getData(true);
                        if (block.dataset.type === 'favicon' && maxSize) {
                            // for favicon enforce square exactly maxSize
                            outW = maxSize * devicePixelRatio;
                            outH = maxSize * devicePixelRatio;
                        } else if (maxSize) {
                            // keep aspect ratio
                            const ratio = cropData.width / cropData.height || 1;
                            outW = Math.round(maxSize * devicePixelRatio);
                            outH = Math.round(outW / ratio);
                        } else {
                            // default to crop box natural size (with device pixel ratio for crispness)
                            outW = Math.round(cropData.width * devicePixelRatio);
                            outH = Math.round(cropData.height * devicePixelRatio);
                        }

                        // ensure positive integers
                        outW = Math.max(1, Math.round(outW));
                        outH = Math.max(1, Math.round(outH));

                        const canvasOptions = {
                            width: outW,
                            height: outH,
                            imageSmoothingEnabled: true,
                            imageSmoothingQuality: 'high'
                        };
                        const croppedCanvas = cropper.getCroppedCanvas(canvasOptions);

                        // If you want to downscale for display while preserving pixel density:
                        // convert canvas to a final canvas with CSS pixel size (optional).
                        // For now produce PNG dataURL:
                        const croppedDataUrl = croppedCanvas.toDataURL('image/png');

                        // dimension info (in pixels)
                        console.log('Cropped canvas px:', croppedCanvas.width, croppedCanvas
                            .height);

                        // Set hidden inputs
                        if (block.dataset.type === 'logo') {
                            document.getElementById('logo_cropped_image').value = croppedDataUrl;
                        } else {
                            document.getElementById('favicon_cropped_image').value = croppedDataUrl;
                        }

                        // Update preview but preserve delete-btn in block
                        const content = block.querySelector('.upload-content');
                        const previewId = block.dataset.type + '-preview';
                        let img = document.getElementById(previewId);
                        if (!img) {
                            img = document.createElement('img');
                            img.id = previewId;
                            img.className = 'image-preview';
                        }
                        content.innerHTML = '';
                        content.appendChild(img);
                        // set CSS size (max-width/height as in your CSS) raw dataURL fits well
                        img.src = croppedDataUrl;

                        finishCropper();
                    }, {
                        once: true
                    });

                    cancelBtn.addEventListener('click', function(ev) {
                        ev.stopPropagation();
                        ev.preventDefault();
                        // Reset UI
                        resetBlockToPlaceholder(block.dataset.type);
                        finishCropper();
                    }, {
                        once: true
                    });
                });
            }

            setupManualUpload('#logo-block', '#logoInput', 256);
            setupManualUpload('#favicon-block', '#faviconInput', 128);

            // Delete image confirmation
            document.querySelectorAll('.delete-image').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    const field = this.dataset.field;
                    const url = this.dataset.url;

                    Swal.fire({
                        title: "Are you sure?",
                        text: "This will delete the " + field + ".",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, delete it!"
                    }).then(result => {
                        if (result.isConfirmed) {
                            fetch(url, {
                                    method: "POST",
                                    headers: {
                                        "X-CSRF-TOKEN": document.querySelector(
                                            'meta[name="csrf-token"]').content,
                                        "Content-Type": "application/json",
                                    },
                                    body: JSON.stringify({
                                        field
                                    })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire("Deleted!", field +
                                            " has been deleted.", "success").then(
                                            () => location.reload());
                                    } else {
                                        Swal.fire("Error!", "Something went wrong.",
                                            "error");
                                    }
                                })
                                .catch(() => Swal.fire("Error!", "Something went wrong.",
                                    "error"));
                        }
                    });
                });
            });

        const barcodeBlock = document.getElementById("barcode-block");
        const barcodeInput = document.getElementById("barcodeInput");

        // When clicking the block → open file dialog
        barcodeBlock.addEventListener("click", function(e) {
            if (e.target.closest(".delete-btn")) return;  // don't trigger on delete click
            barcodeInput.click();
        });

        // Upload file preview
        barcodeInput.addEventListener("change", function() {
            const file = barcodeInput.files[0];
            if (!file) return;

            const allowedTypes = ["image/png", "image/jpeg", "image/jpg", "image/svg+xml"];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire("Invalid File", "Only PNG, JPG, JPEG, SVG allowed.", "error");
                barcodeInput.value = "";
                return;
            }

            let previewUrl = URL.createObjectURL(file);

            barcodeBlock.innerHTML = `
                <button type="button" class="delete-btn delete-image" data-field="barcode"
                    data-url="{{ route('settings.delete.barcode') }}">
                    <i class="fa fa-times"></i>
                </button>

                <img id="barcode-preview" src="${previewUrl}"
                    class="image-preview" alt="Barcode">
            `;
        });

        // Delete barcode
        document.addEventListener("click", function(e) {
            if (!e.target.closest(".delete-image")) return;

            let btn = e.target.closest(".delete-image");
            let deleteUrl = btn.dataset.url;

            Swal.fire({
                title: "Delete Barcode?",
                text: "Are you sure you want to delete this barcode?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Delete",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {

                    fetch(deleteUrl, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(res => res.json())
                    .then(data => {

                        if (data.status === 200) {
                            Swal.fire("Deleted!", data.message, "success");

                            barcodeBlock.innerHTML = `
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                <p class="upload-text">Click to select barcode</p>
                            `;

                            barcodeInput.value = "";
                        } else {
                            Swal.fire("Error", data.message, "error");
                        }

                    })
                    .catch(() => {
                        Swal.fire("Error", "Something went wrong", "error");
                    });

                }
            });
        });
    });
    </script>
    @if (session('success'))
        <script>
            $(document).ready(function() {
                'use strict';
                setTimeout(() => {
                    $(".alert").remove();
                }, 5000);
            });
        </script>
    @endif
@endpush
