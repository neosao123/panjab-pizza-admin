@extends('template.master', ['pageTitle' => 'Payment Settings'])

@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">

<style>
    .required-asterisk { color: red; margin-left: 2px; }
    .is-invalid { border-color: #dc3545 !important; }
    .invalid-feedback { display: block; color: #dc3545; }
</style>
@endpush

@section('content')

<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Payment Settings</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Settings</li>
                        <li class="breadcrumb-item">Payment</li>
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
                    <h5 class="mb-0">Payment Settings</h5>
                </div>
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('payment-gateway.store') }}" 
                          method="post" 
                          data-parsley-validate>
                        @csrf

                        <div class="form-group">
                            <label>Payment Mode<span class="required-asterisk">*</span></label>

                            <select name="payment_mode" 
                                    class="form-control @error('payment_mode') is-invalid @enderror"
                                    required
                                    data-parsley-required-message="Please select a payment mode.">

                                <option value="">-- Select Mode --</option>

                                <option value="0" 
                                    {{ old('payment_mode', $settings->payment_mode ?? '') == "0" ? 'selected' : '' }}>
                                    Sandbox
                                </option>

                                <option value="1" 
                                    {{ old('payment_mode', $settings->payment_mode ?? '') == "1" ? 'selected' : '' }}>
                                    Live
                                </option>

                            </select>

                            @error('payment_mode')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Test Secret Key<span class="required-asterisk">*</span></label>
                                    <input type="text" 
                                           name="test_secret_key"
                                           class="form-control @error('test_secret_key') is-invalid @enderror"
                                           value="{{ old('test_secret_key', $settings->test_secret_key ?? '') }}"
                                           required
                                           data-parsley-required-message="Test Secret Key is required."
                                           data-parsley-maxlength="20"
                                           data-parsley-maxlength-message="Length must not exceed 20 characters.">

                                    @error('test_secret_key')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Live Secret Key<span class="required-asterisk">*</span></label>
                                    <input type="text" 
                                           name="live_secret_key"
                                           class="form-control @error('live_secret_key') is-invalid @enderror"
                                           value="{{ old('live_secret_key', $settings->live_secret_key ?? '') }}"
                                           required
                                           data-parsley-required-message="Live Secret Key is required."
                                           data-parsley-maxlength="20"
                                           data-parsley-maxlength-message="Length must not exceed 20 characters.">

                                    @error('live_secret_key')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Test Client ID<span class="required-asterisk">*</span></label>
                                    <input type="text" 
                                           name="test_client_id"
                                           class="form-control @error('test_client_id') is-invalid @enderror"
                                           value="{{ old('test_client_id', $settings->test_client_id ?? '') }}"
                                           required
                                           data-parsley-maxlength="20"
                                           data-parsley-maxlength-message="Length must not exceed 20 characters.">

                                    @error('test_client_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Live Client ID<span class="required-asterisk">*</span></label>
                                    <input type="text" 
                                           name="live_client_id"
                                           class="form-control @error('live_client_id') is-invalid @enderror"
                                           value="{{ old('live_client_id', $settings->live_client_id ?? '') }}"
                                           required
                                           data-parsley-maxlength="20"
                                           data-parsley-maxlength-message="Length must not exceed 20 characters.">

                                    @error('live_client_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

 
                        <div class="form-group">
                            <label>Webhook Secret Key<span class="required-asterisk">*</span></label>
                            <input type="text" 
                                   name="webhook_secret_key"
                                   class="form-control @error('webhook_secret_key') is-invalid @enderror"
                                   value="{{ old('webhook_secret_key', $settings->webhook_secret_key ?? '') }}"
                                   required
                                   data-parsley-maxlength="20"
                                   data-parsley-maxlength-message="Length must not exceed 20 characters.">

                            @error('webhook_secret_key')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
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
    setTimeout(function () {
        let alertBox = document.querySelector('.alert-success');
        if (alertBox) {
            alertBox.style.transition = "0.5s";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500);
        }
    }, 3000);
</script>

@endpush
