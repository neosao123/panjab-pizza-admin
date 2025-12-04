@extends('template.master', ['pageTitle' => 'Sides Update'])
@push('styles')
<style>
    .btn-group>.btn:first-child,
    .dropdown-toggle-split::after,
    .dropright .dropdown-toggle-split::after,
    .dropup .dropdown-toggle-split::after {
      margin-left: 0;
      background-color: white;
      color: #040404;
      border: 0;
    }

  
    </style>
    <link href="{{ asset('theme/js/summernote/dist/summernote-bs4.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Side Edit</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/sides/list') }}">Side</a></li>
                            <li class="breadcrumb-item">Edit</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/sides/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
                    <form id="addressform" action="{{ url('/sides/update') }}" method="post" enctype="multipart/form-data"
                        data-parsley-validate="">
                        @csrf
                        <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                        <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>Sides Name : <span style="color:red">*</span></label>
                                <input type="text" id="sideName" name="sideName" class="form-control" required
                                    value="{{ $queryresult->sidename }}"
                                    data-parsley-required-message="Side name is required" maxlength='150'
                                    data-parsley-minlength="3"
                                    data-parsley-minlength-message="You need to enter at least 3 characters"
                                    data-parsley-trigger="change" onkeypress="return ValidateAlpha(event)">
                                <span class="text-danger">
                                    @error('sideName')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>

                            <div class="col-md-6">
                                <label>Type<span style="color:red">*</span></label>
                                <div class="form-inline">
                                    <div class="custom-control custom-radio mr-2">
                                        <input type="radio" id="side" name="type" class="custom-control-input"
                                            value="side" {{ $queryresult->type == 'side' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="side">Side</label>
                                    </div>
                                    <div class="custom-control custom-radio mr-2">
                                        <input type="radio" id="subs" name="type" class="custom-control-input"
                                            value="subs" {{ $queryresult->type == 'subs' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="subs">Subs</label>
                                    </div>
                                    <div class="custom-control custom-radio mr-2">
                                        <input type="radio" id="poutine" name="type" class="custom-control-input"
                                            value="poutine" {{ $queryresult->type == 'poutine' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="poutine">Poutine</label>
                                    </div>
                                    <div class="custom-control custom-radio mr-2">
                                        <input type="radio" id="plantbites" name="type" class="custom-control-input"
                                            value="plantbites" {{ $queryresult->type == 'plantbites' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="plantbites">Plant Bites</label>
                                    </div>
                                    <div class="custom-control custom-radio mr-2">
                                        <input type="radio" id="tenders" name="type" class="custom-control-input"
                                            value="tenders" {{ $queryresult->type == 'tenders' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="tenders">Tenders</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" rows="3" class="form-control">{{ $queryresult->description }}</textarea>
                                <span class="text-danger">
                                    @error('description')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-3 form-group ">
                                <label class="form-label" for="form-wizard-progress-wizard-sidesimage">Side Image</label>
                                <input type="file" id="file" class="form-control " name="sidesImage"
                                    accept=".jpg, .jpeg, .png">

                            </div>

                            @if (!empty($queryresult->image))
                                <div class="col-md-3 mb-3 form-group " id="eImage">
                                    <img class="img-thumbnail mb-2" width="100" height="100" id="showSideImg"
                                        src="{{ url('uploads/sides/' . $queryresult->image) . '?v=' . time() }}"
                                        data-src="">
                                    <a class="btn btn-danger text-white"
                                        onclick="deleteImage('{{ $queryresult->code }}','{{ $queryresult->image }}');"><i
                                            class="fa fa-trash"></i></a>
                                </div>
                            @endif
                            <div class="col-md-3 mb-3 d-none" id="eDisImage">
                                <img class="img-thumbnail mb-2" width="100" height="100" id="showImage"
                                    src="" data-src="">
                            </div>
                            <div class="col-md-12 form-group ">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="isActive" name="isActive"
                                        value="1" {{ $queryresult->isActive == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="isActive"> Status</label>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="nonzero" value="0">
                        @if ($sidelineentries && count($sidelineentries) > 0)
                            @foreach ($sidelineentries as $item)
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label>Size : <span style="color:red">*</span></label>
                                        <input type="hidden" class="form-control" name="rowCode[]"
                                            value="{{ $item->code }}">
                                        <input type="text" class="form-control" name="size[]" maxlength='80'
                                            data-parsley-minlength="2"
                                            data-parsley-minlength-message="You need to enter at least 2 characters"
                                            data-parsley-trigger="change" value="{{ $item->size }}" required
                                            data-parsley-required-message="Size is required.">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Price : <span style="color:red">*</span></label>
                                        <input type="number" step="0.01" min="1" class="form-control"
                                            name="price[]" required data-parsley-trigger="change"
                                            data-parsley-gt="#nonzero" value="{{ $item->price }}"
                                            data-parsley-required-message="Price is required.">
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <div class="row">
                            <div class="col-md-12 form-group ">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="hasToppings"
                                        name="hasToppings" value="1"
                                        {{ $queryresult->hasToppings == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="hasToppings">Has Toppings</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Number of Toppings : <span style="color:red">*</span></label>
                                <input type="number" step="1" id="nooftoppings" name="nooftoppings"
                                    class="form-control" min="0" value="{{ $queryresult->nooftoppings }}">
                                <span class="text-danger">
                                    @error('nooftoppings')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                        </div>

                        <div class="row">
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
 <script type="text/javascript" src="{{ asset('theme/js/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/parsley-fields-comparison-validators.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/init_site/sides/edit.js?v=' . time()) }}"></script>

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
