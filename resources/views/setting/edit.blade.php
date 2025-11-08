@extends('template.master', ['pageTitle' => 'Setting Update'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Setting</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/setting/list') }}">Setting</a></li>
                            <li class="breadcrumb-item">Edit</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/setting/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
            </div>
            <?php } ?>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h5 class="mb-0" data-anchor="data-anchor">Edit</h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($setting)
                    <form action="{{ url('setting/update') }}" method="post" enctype="multipart/form-data"
                        data-parsley-validate="">
                        @csrf
                        <input type="hidden" name="code" value="{{ $setting->code }}" readonly>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label for="settingName">Setting Name <span style="color:red">*</span></label>
                                <input type="text" id="settingName" name="settingName" class="form-control" readonly
                                    value="{{ $setting->settingName }}">
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="settingValue">Setting Value <span style="color:red">*</span></label>
                                @if ($setting->code == 'STG_7')
                                    <input type="number" id="settingValue" name="settingValue" class="form-control"
                                        required value="{{ $setting->settingValue }}"
                                        min="1"
                                        max="2"
                                        step="1"
                                        data-parsley-type="number"
                                        data-parsley-required-message="Setting value is required">
                                @else
                                    <input type="text" id="settingValue" name="settingValue" class="form-control"
                                        required value="{{ $setting->settingValue }}"
                                        data-parsley-required-message="Setting value is required">
                                @endif
                                <span class="text-danger">
                                    @error('settingValue')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-sm-12 form-group">
                                <button class="btn btn-success"> Update </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/parsley-fields-comparison-validators.js') }}"></script>

    @if (session('error'))
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
