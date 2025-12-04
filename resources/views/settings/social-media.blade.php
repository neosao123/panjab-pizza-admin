@extends('template.master', ['pageTitle' => __('index.social_media_settings')])
@push('styles')
    <style>
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .required::after {
            content: " *";
            color: #e63757;
        }
    </style>
@endpush
@section('content')
    @php
        // $socialSettings is an associative array like ['facebook' => 'url', ...]
        $socialSettings = $socialSettings ?? [];
        $facebook = $socialSettings['facebook'] ?? '';
        $instagram = $socialSettings['instagram'] ?? '';
        $twitter = $socialSettings['twitter'] ?? '';
        $linkedin = $socialSettings['linkedin'] ?? '';
        $youtube = $socialSettings['youtube'] ?? '';
        $snapchat = $socialSettings['snapchat'] ?? '';
        $tiktok = $socialSettings['tiktok'] ?? '';
        $copyright_text = $socialSettings['copyright_text'] ?? '';
        $footer_note = $socialSettings['footer_note'] ?? '';
    @endphp
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Social Media Links</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/settings/social-media') }}">Settings</a></li>
                            <li class="breadcrumb-item">Social Media Links</li>
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
                        <h5 class="mb-0">Update Social Media Links</h5>
                    </div>
                    <div class="card-body">
                        <form id="socialMediaForm" method="POST" action="{{ route('settings.social.update') }}" novalidate>
                            @csrf

                            <!-- Social Media Links -->
                            <h6 class="mb-3 text-muted">Social Media Links</h6>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="facebook" class="form-label">Facebook</label>
                                    <input type="url" class="form-control" id="facebook" name="facebook"
                                        value="{{ old('facebook', $facebook) }}">
                                    @error('facebook')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="instagram" class="form-label">Instagram</label>
                                    <input type="url" class="form-control" id="instagram" name="instagram"
                                        value="{{ old('instagram', $instagram) }}">
                                    @error('instagram')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="twitter" class="form-label">Twitter</label>
                                    <input type="url" class="form-control" id="twitter" name="twitter"
                                        value="{{ old('twitter', $twitter) }}">
                                    @error('twitter')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="linkedin" class="form-label">LinkedIn</label>
                                    <input type="url" class="form-control" id="linkedin" name="linkedin"
                                        value="{{ old('linkedin', $linkedin) }}">
                                    @error('linkedin')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="youtube" class="form-label">YouTube</label>
                                    <input type="url" class="form-control" id="youtube" name="youtube"
                                        value="{{ old('youtube', $youtube) }}">
                                    @error('youtube')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="tiktok" class="form-label">TikTok</label>
                                    <input type="url" class="form-control" id="tiktok" name="tiktok"
                                        value="{{ old('tiktok', $tiktok) }}">
                                    @error('tiktok')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="snapchat" class="form-label">Snapchat</label>
                                    <input type="url" class="form-control" id="snapchat" name="snapchat"
                                        value="{{ old('snapchat', $snapchat) }}">
                                    @error('snapchat')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-3">

                            <h6 class="mb-3 text-muted">Footer Content</h6>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="footer_note" class="form-label required">Footer Note</label>
                                    <textarea class="form-control" id="footer_note" name="footer_note" rows="3" >{{ old('footer_note', $footer_note) }}</textarea>
                                    @error('footer_note')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="copyright_text" class="form-label required">Copyright Text</label>
                                    <input type="text" class="form-control" id="copyright_text" name="copyright_text"
                                        value="{{ old('copyright_text', $copyright_text) }}" >
                                    @error('copyright_text')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-4 d-flex gap-2">
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
    <script>
        $(document).ready(function() {
            $('#socialMediaForm').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).removeClass('was-validated');
            });

            $('input[type="url"]').on('blur', function() {
                var value = $(this).val();
                if (value && !value.match(/^https?:\/\/.+/)) {
                    $(this).val('https://' + value);
                }
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
