@extends('template.master', ['pageTitle' => 'Payment Settings'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">

    <style>
        .required-asterisk {
            color: red;
            margin-left: 2px;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
        }
    </style>
@endpush

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">DoorDash Settings</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item">Settings</li>
                            <li class="breadcrumb-item">DoorDash</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-7 align-self-center">
                <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row justify-content-center">

            <div class="col-md-8 col-lg-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">DoorDash API Settings</h5>
                    </div>
                    <div class="card-body">

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        <form action="{{ route('doordash.store') }}" method="post" data-parsley-validate>
                            @csrf

                            <div class="form-group">
                                <label>Mode<span class="required-asterisk">*</span></label>
                                <select name="mode" class="form-control @error('mode') is-invalid @enderror" required
                                    data-parsley-required-message="Please select mode">
                                    <option value="">-- Select Mode --</option>
                                    <option value="sandbox" {{ old('mode', $setting->mode ?? '') == 'sandbox' ? 'selected' : '' }}>
                                        Sandbox</option>
                                    <option value="live" {{ old('mode', $setting->mode ?? '') == 'live' ? 'selected' : '' }}>
                                        Live</option>
                                </select>
                                @error('mode')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Developer ID --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Test Developer ID<span class="required-asterisk">*</span></label>
                                        <input type="text" name="test_developer_id" class="form-control"
                                            value="{{ old('test_developer_id', $setting->test_developer_id ?? '') }}"
                                            required data-parsley-required-message="Test Developer ID is required">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Live Developer ID<span class="required-asterisk">*</span></label>
                                        <input type="text" name="live_developer_id" class="form-control"
                                            value="{{ old('live_developer_id', $setting->live_developer_id ?? '') }}"
                                            required data-parsley-required-message="Live Developer ID is required">
                                    </div>
                                </div>
                            </div>

                            {{-- Key ID --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Test Key ID<span class="required-asterisk">*</span></label>
                                        <input type="text" name="test_key_id" class="form-control"
                                            value="{{ old('test_key_id', $setting->test_key_id ?? '') }}" required
                                            data-parsley-required-message="Test Key ID is required">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Live Key ID<span class="required-asterisk">*</span></label>
                                        <input type="text" name="live_key_id" class="form-control"
                                            value="{{ old('live_key_id', $setting->live_key_id ?? '') }}" required
                                            data-parsley-required-message="Live Key ID is required">
                                    </div>
                                </div>
                            </div>

                            {{-- Signing Secret --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Test Signing Secret<span class="required-asterisk">*</span></label>
                                        <input type="text" name="test_signing_secret" class="form-control"
                                            value="{{ old('test_signing_secret', $setting->test_signing_secret ?? '') }}"
                                            required data-parsley-required-message="Test Signing Secret is required">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Live Signing Secret<span class="required-asterisk">*</span></label>
                                        <input type="text" name="live_signing_secret" class="form-control"
                                            value="{{ old('live_signing_secret', $setting->live_signing_secret ?? '') }}"
                                            required data-parsley-required-message="Live Signing Secret is required">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success mt-2">
                                <i class="fa fa-save"></i> Update
                            </button>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script src="{{ asset('theme/js/select2.min.js') }}"></script>

    <script>
        $(function() {
            $('form').parsley();
        });
    </script>

    <script>
        setTimeout(function() {
            let alertBox = document.querySelector('.alert-success');
            if (alertBox) {
                alertBox.style.transition = "0.5s";
                alertBox.style.opacity = "0";
                setTimeout(() => alertBox.remove(), 500);
            }
        }, 3000);
    </script>
@endpush
