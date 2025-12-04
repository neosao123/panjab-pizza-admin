@extends('template.master', ['pageTitle' => __('index.privacy')])

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <style>
        /* Quill editor */
        #editor {
            min-height: 300px;
            background-color: #fff;

            @error('description')
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
                <h4 class="page-title">Privacy Policy</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/settings/privacyPolicy') }}">Settings</a></li>
                            <li class="breadcrumb-item">Privacy Policy</li>
                        </ol>
                    </nav>
                </div>
            </div>

        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-10 col-md-12 col-sm-12">
                @if (session('success'))
                    <div class="alert alert-success">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        {{ session('success') }}
                    </div>
                @endif
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Privacy Policy</h5>
                    </div>
                    <div class="card-body">
                        <form id="privacyForm" method="POST" action="{{ route('settings.privacyPolicy.update') }}">
                            @csrf

                            <!-- SEO & Meta Details -->
                            <div class="col-12 mb-4">
                                <h5>SEO & Meta Details</h5>
                                <hr>
                            </div>
                            <div class="row">
                                <!-- Meta Privacy Title -->
                                <div class="mb-3 col-md-12">
                                    <label for="meta_privacy_title" class="form-label">Meta Title <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="meta_privacy_title" id="meta_privacy_title"
                                        value="{{ old('meta_privacy_title', $privacy->meta_privacy_title ?? '') }}"
                                        class="form-control @error('meta_privacy_title') is-invalid @enderror">
                                    @error('meta_privacy_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Meta Privacy Description -->
                                <div class="mb-3 col-md-12">
                                    <label for="meta_privacy_description" class="form-label">Meta Description <span
                                            class="text-danger">*</span></label>
                                    <textarea name="meta_privacy_description" id="meta_privacy_description" rows="5"
                                        class="form-control @error('meta_privacy_description') is-invalid @enderror">{{ old('meta_privacy_description', $privacy->meta_privacy_description ?? '') }}</textarea>
                                    @error('meta_privacy_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Quill Editor -->
                            <div class="mt-1">
                                <label for="editor" class="form-label fw-bold">Page Content <span
                                        class="text-danger">*</span></label>
                                <div id="editor">{!! old('description', $privacy->value ?? '') !!}</div>

                                <!-- Hidden field to store HTML content -->
                                <input type="hidden" name="description" id="description">

                                <!-- Error message -->
                                @error('description')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
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
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Quill with full toolbar
            const quill = new Quill('#editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{
                            header: [1, 2, 3, 4, 5, 6, false]
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            color: []
                        }, {
                            background: []
                        }],
                        [{
                            list: 'ordered'
                        }, {
                            list: 'bullet'
                        }],
                        [{
                            indent: '-1'
                        }, {
                            indent: '+1'
                        }],
                        [{
                            align: []
                        }],
                        ['link', 'image', 'video'],
                        [{
                            script: 'sub'
                        }, {
                            script: 'super'
                        }],
                        ['clean']
                    ]
                }
            });

            // Update hidden input on form submit
            document.getElementById('privacyForm').addEventListener('submit', function() {
                document.getElementById('description').value = quill.root.innerHTML;
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
