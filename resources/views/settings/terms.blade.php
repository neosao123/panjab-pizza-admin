@extends('template.master', ['pageTitle' => __('index.terms')])

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <style>
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
                <h4 class="page-title">Terms & Conditions</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/settings/terms') }}">Settings</a></li>
                            <li class="breadcrumb-item">Terms & Conditions</li>
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
                        <h5 class="mb-0">Terms & Conditions</h5>
                    </div>
                    <div class="card-body">
                        <form id="termsForm" method="POST" action="{{ route('settings.terms.update') }}">
                            @csrf

                            <!-- SEO & Meta Details -->
                            <div class="col-12 mb-4">
                                <h5>SEO & Meta Details</h5>
                                <hr>
                            </div>
                            <div class="row">
                                <!-- Meta Terms Title -->
                                <div class="mb-3 col-md-12">
                                    <label for="meta_terms_title" class="form-label">Meta Title <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="meta_terms_title" id="meta_terms_title"
                                        value="{{ old('meta_terms_title', $terms->meta_terms_title ?? '') }}"
                                        class="form-control @error('meta_terms_title') is-invalid @enderror">
                                    @error('meta_terms_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Meta Terms Description -->
                                <div class="mb-3 col-md-12">
                                    <label for="meta_terms_description" class="form-label">Meta Description <span
                                            class="text-danger">*</span></label>
                                    <textarea name="meta_terms_description" id="meta_terms_description" rows="5"
                                        class="form-control @error('meta_terms_description') is-invalid @enderror">{{ old('meta_terms_description', $terms->meta_terms_description ?? '') }}</textarea>
                                    @error('meta_terms_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Quill Editor -->
                            <div class="mt-1">
                                <label for="editor" class="form-label fw-bold">Page Content <span
                                        class="text-danger">*</span></label>
                                <div id="editor">{!! old('value', $terms->value ?? '') !!}</div>

                                <input type="hidden" name="value" id="value">
                                @error('value')
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
            const quill = new Quill('#editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{
                            'header': [1, 2, 3, 4, 5, 6, false]
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            'color': []
                        }, {
                            'background': []
                        }],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        [{
                            'indent': '-1'
                        }, {
                            'indent': '+1'
                        }],
                        [{
                            'align': []
                        }],
                        ['link', 'image', 'video'],
                        [{
                            'script': 'sub'
                        }, {
                            'script': 'super'
                        }],
                        ['clean']
                    ]
                }
            });

            const form = document.getElementById('termsForm');
            form.addEventListener('submit', function() {
                document.getElementById('value').value = quill.root.innerHTML;
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
