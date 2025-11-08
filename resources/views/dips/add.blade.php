@extends('template.master', ['pageTitle' => 'Dips Add'])

@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Dips Add</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/dips/list') }}">Dips</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </nav>
            </div>
        </div>
        @if ($viewRights == 1)
        <div class="col-7 align-self-center">
            <a href="{{ url('/dips/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
        </div>
        @endif
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add</h5>
        </div>
        <div class="card-body">
            @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

                <form id="dipsForm" action="{{ url('dips/store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="nonzero" value="0">

                <div class="row">
                    <!-- Name -->
                    <div class="col-sm-6 form-group">
                        <label>Name : <span style="color:red">*</span></label>
                        <input type="text" id="dips" name="dips" class="form-control" required
                            value="{{ old('dips') }}"
                            data-parsley-required-message="Dips name is required"
                            maxlength='150'
                            data-parsley-minlength="3"
                            data-parsley-minlength-message="You need to enter at least 3 characters"
                            data-parsley-trigger="change">
                        <span class="text-danger">@error('dips') {{ $message }} @enderror</span>
                    </div>

                    <!-- Price -->
                    <div class="col-sm-6 form-group">
                        <label>Price : <span style="color:red">*</span></label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" class="form-control"
                            value="{{ old('price') }}" required
                            data-parsley-required-message="Price is required"
                            data-parsley-trigger="change" data-parsley-gt="#nonzero">
                        <span class="text-danger">@error('price') {{ $message }} @enderror</span>
                    </div>

                    <!-- Image -->
                    <div class="col-md-6 form-group">
                        <label>Dips Image</label>
                        <input type="file" id="file" class="form-control" name="dipsImage" accept=".jpg, .jpeg, .png">
                    </div>


                    <!-- Submit -->
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
    setTimeout(() => { $(".alert").remove(); }, 5000);
});
</script>
@endif
@endpush
