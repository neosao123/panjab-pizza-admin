@extends('template.master', ['pageTitle' => 'Sauce Add'])

@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
@endpush

@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Sauce Add</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/sauce/list') }}">Sauce</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </nav>
            </div>
        </div>
        @if ($viewRights == 1)
        <div class="col-7 align-self-center">
            <a href="{{ url('/sauce/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
        </div>
        @endif
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-sm-12">
                    <h5 class="mb-0" data-anchor="data-anchor">Add</h5>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif

            <form id="addressform" action="{{ url('/sauce/store') }}" method="post" enctype="multipart/form-data" data-parsley-validate>
                @csrf
                <input type="hidden" name="code" value="">
                <input type="hidden" name="id" value="">

                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>Name : <span style="color:red">*</span></label>
                        <input type="text" id="sauce" name="sauce" class="form-control" required
                            value="{{ old('sauce') }}"
                            data-parsley-required-message="Name is required"
                            maxlength='150'
                            data-parsley-minlength="3"
                            data-parsley-minlength-message="You need to enter at least 3 characters"
                            data-parsley-trigger="change">
                        <span class="text-danger">
                            @error('sauce') {{ $message }} @enderror
                        </span>
                    </div>
                    
                    <div class="col-sm-12 form-group">
                        <button class="btn btn-primary" type="submit"> Save </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
@if (session('error'))
<script>
    $(document).ready(function () {
        'use strict';
        setTimeout(() => {
            $(".alert").remove();
        }, 5000);
    });
</script>
@endif
@endpush
