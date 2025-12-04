@extends('template.master', ['pageTitle' => __('index.contact')])

@push('styles')
@endpush

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Contact</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/settings/contact') }}">Settings</a></li>
                            <li class="breadcrumb-item">Contact Us</li>
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
                        <h5 class="mb-0">Contact Us</h5>
                    </div>
                    <div class="card-body">
                        <form id="contactForm" method="POST" action="{{ route('settings.contact.update') }}">
                            @csrf

                            <div class="row g-3 mb-3">
                                <div class="col-12">
                                    <label for="contact_description" class="form-label">Description <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="contact_description" id="contact_description"
                                        value="{{ old('contact_description', $contact->description ?? '') }}"
                                        class="form-control @error('contact_description') is-invalid @enderror">
                                    @error('contact_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="contact_address" class="form-label">Address <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="contact_address" id="contact_address"
                                        value="{{ old('contact_address', $contact->address ?? '') }}"
                                        class="form-control @error('contact_address') is-invalid @enderror">
                                    @error('contact_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6 col-lg-5">
                                    <label for="contact_email" class="form-label">Email <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="contact_email" id="contact_email"
                                        value="{{ old('contact_email', $contact->email ?? '') }}"
                                        class="form-control @error('contact_email') is-invalid @enderror">
                                    @error('contact_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div> 
                                
                                <div class="col-12 col-md-6 col-lg-5">
                                    <label for="contact_phone" class="form-label">Phone Number <span
                                            class="text-danger">*</span></label>
                                    <input type="tel" name="contact_phone" id="contact_phone"
                                        value="{{ old('contact_phone', $contact->phone ?? '') }}"
                                        class="form-control @error('contact_phone') is-invalid @enderror">
                                    @error('contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- SEO & Meta Details -->
                            <div class="col-12">
                                <b>SEO & Meta Details</b>
                            </div>

                            <hr />

                            <div class="row">
                                <!-- Meta About Title -->
                                <div class="mb-3 col-md-12">
                                    <label for="meta_contact_title" class="form-label">Meta Title<span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="meta_contact_title" id="meta_contact_title"
                                        value="{{ old('meta_contact_title', $contact->meta_title ?? '') }}"
                                        class="form-control @error('meta_contact_title') is-invalid @enderror">
                                    @error('meta_contact_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Meta About Description -->
                                <div class="mb-3 col-md-12">
                                    <label for="meta_contact_description" class="form-label">Meta Description<span
                                            class="text-danger">*</span></label>
                                    <textarea name="meta_contact_description" id="meta_contact_description" rows="5"
                                        class="form-control @error('meta_contact_description') is-invalid @enderror">{{ old('meta_contact_description', $contact->meta_description ?? '') }}</textarea>
                                    @error('meta_contact_description')
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
