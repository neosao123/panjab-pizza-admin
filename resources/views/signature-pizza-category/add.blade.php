@extends('template.master', ['pageTitle' => 'Category'])
@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Category Add</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/signature-pizza-category/list') }}">Category</a></li>
                        <li class="breadcrumb-item">Add</li>
                    </ol>
                </nav>
            </div>
        </div>
        <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/signature-pizza-category/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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

            <form action="{{ url('/signature-pizza-category/store') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
                @csrf
                <div class="row">
                    <div class="col-sm-12 form-group">
                        <label>Category Name : <span style="color:red">*</span></label>
                        <input type="text" id="categoryName" name="categoryName" class="form-control" required value="{{old('categoryName')}}"data-parsley-required-message="Category name is required" maxlength='150' data-parsley-minlength="3" data-parsley-minlength-message="You need to enter at least 3 characters" data-parsley-trigger="change" onkeypress="return ValidateAlpha(event)">
                        <span class="text-danger">
                            @error('categoryName')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>                   
                    <div class="col-md-12 form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1">
                            <label class="custom-control-label" for="isActive"> Status</label>
                        </div>
                    </div>
                    <div class="col-sm-12 form-group">
                        <button class="btn btn-primary" type="submit"> Add </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/parsley-fields-comparison-validators.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/init_site/signature-pizza-category/edit.js?v=' . time()) }}"></script>

@if (session('error'))
<script>
    $(document).ready(function() {
        'use strict';
        setTimeout(() => {
            $(".alert").remove();
        }, 5000);

        $('#tradeform').submit(function(e) {
            //check atleat 1 checkbox is checked
            if (!$('.checkSelect').is(':checked')) {
                //prevent the default form submit if it is not checked
                e.preventDefault();
            }
        });

    });
</script>
@endif
@endpush