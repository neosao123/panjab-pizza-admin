@extends('template.master', ['pageTitle' => 'Add Offer Card'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Add Offer Card</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/pictures/list') }}">Offer Card</a></li>
                            <li class="breadcrumb-item active">Add</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @if ($viewRights == 1)
                <div class="col-7 align-self-center">
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
                        <h5 class="mb-0">Add Offer Card</h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ url('/pictures/store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="code" value="">
                    <input type="hidden" name="id" value="">

                    <div class="row">

                        <!-- Title -->
                        <div class="col-sm-12 form-group">
                            <label>Title : <span style="color:red">*</span></label>
                            <input type="text" id="title" name="title" class="form-control"
                                value="{{ old('title') }}" data-parsley-required-message="Title is required"
                                maxlength='150' data-parsley-minlength="3"
                                data-parsley-minlength-message="You need to enter at least 3 characters"
                                data-parsley-trigger="change">
                            <span class="text-danger">
                                @error('title')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <!-- Pizza Type (Category) -->
                        <div class="col-sm-6 form-group">
                            <label>Pizza Type : <span style="color:red">*</span></label>
                            <select id="pizza_type" name="pizza_type" class="form-control"
                                data-parsley-required-message="Pizza type is required">
                                <option value="">Select Pizza Type</option>
                                <option value="special_offers" {{ old('pizza_type') == 'special_offers' ? 'selected' : '' }}>Special Offers</option>
                                <option value="signature_pizzas" {{ old('pizza_type') == 'signature_pizzas' ? 'selected' : '' }}>Signature Pizzas</option>
                                <option value="other_pizzas" {{ old('pizza_type') == 'other_pizzas' ? 'selected' : '' }}>Other Pizzas</option>
                                <option value="sides" {{ old('pizza_type') == 'sides' ? 'selected' : '' }}>Sides</option>
                            </select>
                            <span class="text-danger">
                                @error('pizza_type')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <!-- Product -->
                        <div class="col-sm-6 form-group">
                            <label>Product : <span style="color:red">*</span></label>
                            <select id="product_id" name="product_id" class="form-control select2"
                                data-parsley-required-message="Product is required" disabled>
                                <option value="">Select Pizza Type First</option>
                            </select>
                            <span class="text-danger">
                                @error('product_id')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <!-- Image -->
                        <div class="col-md-6 form-group">
                            <label>Image : <span style="color:red">*</span></label>

                            <div id="drop-area" class="border border-dashed p-4 text-center" style="cursor:pointer;">
                                <p>Drag & drop an image here, or click to select</p>
                                <input type="file" id="fileInput" class="d-none" name="image" accept="image/*">
                                <img id="preview" src="" alt=""
                                    style="max-width:200px; display:none; margin-top:10px;">
                            </div>

                            <span class="text-danger">
                                @error('image')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <!-- Product URL -->
                        <div class="col-sm-6 form-group">
                            <label>Product URL :</label>
                            <input type="text" id="product_url" name="product_url" class="form-control"
                                value="{{ old('product_url') }}" maxlength="255">
                            <small class="form-text text-muted">
                                The product URL you enter must be the same as the product on the website.
                            </small>
                            <span class="text-danger">
                                @error('product_url')
                                    {{ $message }}
                                @enderror
                            </span>
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
    <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/init_site/picture/add.js?v=' . time()) }}"></script>
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
