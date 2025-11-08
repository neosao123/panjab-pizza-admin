@extends('template.master', ['pageTitle' => 'Special Base Edit'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Special Base Edit</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/specialbases/list') }}">Special Base</a></li>
                            <li class="breadcrumb-item">Edit</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/specialbases/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
                @if ($queryresult)
                    <form action="{{ url('/specialbases/update') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
                        @csrf
                        <input type="hidden" id="nonzero" value="0">
                        <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                        <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>Name : <span style="color:red">*</span></label>
                                <input type="text" id="specialbase" name="specialbase" class="form-control" required value="{{ $queryresult->specialbase }}"
                                    data-parsley-required-message="Special Base Name is required" maxlength='150' data-parsley-minlength="3"
                                    data-parsley-minlength-message="You need to enter at least 3 characters" data-parsley-trigger="change">
                                <span class="text-danger">
                                    @error('specialbase')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-sm-6 form-group">
                                <label>Price : <span style="color:red">*</span></label>
                                <input type="number" id="price" name="price" step="0.01" min="0" class="form-control" max="9999999" value="{{ $queryresult->price }}" required
                                    data-parsley-required-message="Price is required" data-parsley-trigger="change">
                                <span class="text-danger">
                                    @error('price')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1" {{ $queryresult->isActive == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="isActive"> Status</label>
                                </div>
                            </div>

                            <div class="col-sm-12 form-group">
                                <button class="btn btn-primary" type="submit"> Update </button>
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
