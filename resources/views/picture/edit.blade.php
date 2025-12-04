@extends('template.master', ['pageTitle' => 'Edit Offer Card'])
@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Edit Offer Card</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/pictures/list') }}">Offer Card</a></li>
                        <li class="breadcrumb-item">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
        @if ($viewRights == 1)
        <div class="col-7 align-self-center ">
            <a href="{{ url('/pictures/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
        </div>
        @endif
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

            @if($queryresult)
            <form id="pictureForm" action="{{ url('/pictures/update') }}" method="POST" enctype="multipart/form-data" data-parsley-validate="">
                @csrf
                <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>

                <div class="row">

                    <!-- Title -->
                    <div class="col-sm-12 form-group">
                        <label>Title : <span style="color:red">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" required
                            value="{{ $queryresult->title }}"
                            data-parsley-required-message="Title is required"
                            maxlength='150'
                            data-parsley-minlength="3"
                            data-parsley-minlength-message="You need to enter at least 3 characters"
                            data-parsley-trigger="change">
                        <span class="text-danger">@error('title') {{ $message }} @enderror</span>
                    </div>

                    <!-- Pizza Type (Category) -->
                    <div class="col-sm-6 form-group">
                        <label>Pizza Type : <span style="color:red">*</span></label>
                        <select id="pizza_type" name="pizza_type" class="form-control" required
                            data-parsley-required-message="Pizza type is required">
                            <option value="">Select Pizza Type</option>
                            <option value="special_offers" {{ $queryresult->pizza_type == 'special_offers' ? 'selected' : '' }}>Special Offers</option>
                            <option value="signature_pizzas" {{ $queryresult->pizza_type == 'signature_pizzas' ? 'selected' : '' }}>Signature Pizzas</option>
                            <option value="other_pizzas" {{ $queryresult->pizza_type == 'other_pizzas' ? 'selected' : '' }}>Other Pizzas</option>
                            <option value="sides" {{ $queryresult->pizza_type == 'sides' ? 'selected' : '' }}>Sides</option>
                        </select>
                        <span class="text-danger">@error('pizza_type') {{ $message }} @enderror</span>
                    </div>

                    <!-- Product -->
                    <div class="col-sm-6 form-group">
                        <label>Product : <span style="color:red">*</span></label>
                        <select id="product_id" name="product_id" class="form-control select2" required
                            data-parsley-required-message="Product is required">
                            <option value="">Loading...</option>
                        </select>
                        <span class="text-danger">@error('product_id') {{ $message }} @enderror</span>
                    </div>

                    <!-- Image -->
                    <div class="col-md-6 form-group">
                        <label>Picture Image :<span style="color:red">*</span></label>

                        <div id="image-block" class="border rounded p-3 text-center position-relative" style="cursor:pointer;">
                            <!-- Show existing image or preview -->
                            <img id="showImage"
                                src="{{ !empty($queryresult->image) ? url('uploads/picture/' . $queryresult->image) . '?v=' . time() : '' }}"
                                class="img-thumbnail {{ !empty($queryresult->image) ? '' : 'd-none' }}"
                                width="100" height="100">

                            <!-- Delete button for existing image -->
                            @if (!empty($queryresult->image))
                            <button type="button"
                                    class="btn btn-danger btn-sm position-absolute top-0 end-0"
                                    style="transform: translate(50%, -50%);"
                                    onclick="deleteImage('{{ $queryresult->code }}','{{ $queryresult->image }}');">
                                <i class="fa fa-trash"></i>
                            </button>
                            @endif

                            <!-- Drag & drop / click-to-upload instruction -->
                            <p class="m-0 text-muted mt-2">Drag & drop image here or click to select</p>
                            <input type="file" id="file" name="image" accept="image/*" class="form-control d-none">
                        </div>

                        <span id="imageError" class="text-danger">@error('image') {{ $message }} @enderror</span>
                    </div>

                    <!-- Product URL -->
                    <div class="col-sm-6 form-group">
                        <label>Product URL :</label>
                        <input type="text" id="product_url" name="product_url" class="form-control"
                            value="{{ $queryresult->product_url ?? '' }}" maxlength="255">
                        <small class="form-text text-muted">
                            The product URL you enter must be the same as the product on the website.
                        </small>
                        <span class="text-danger">@error('product_url') {{ $message }} @enderror</span>
                    </div>

                    <!-- Status -->
                    <div class="col-md-12 form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1"
                                {{ $queryresult->isActive == 1 ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isActive"> Status</label>
                        </div>
                    </div>

                    <!-- Submit -->
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
<script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/parsley-fields-comparison-validators.js') }}"></script>
<script>
    // Pass existing product_id to JavaScript
    var existingProductId = '{{ $queryresult->product_id ?? '' }}';
    var existingPizzaType = '{{ $queryresult->pizza_type ?? '' }}';
</script>
<script type="text/javascript" src="{{ asset('theme/init_site/picture/edit.js?v=' . time()) }}"></script>


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
