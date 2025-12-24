@extends('template.master', ['pageTitle' => 'Soft Drink Update'])
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
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/js/summernote/dist/summernote-bs4.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Soft Drink Edit</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/softdrinks/list') }}">Soft Drink</a></li>
                            <li class="breadcrumb-item">Edit</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/softdrinks/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
                    <form id="addressform" action="{{ url('/softdrinks/update') }}" method="post"
                        enctype="multipart/form-data" data-parsley-validate="">
                        @csrf
                        <input type="hidden" id="nonzero" value="0">
                        <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                        <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                        <div class="row">
                            <div class="col-sm-12 form-group">
                                <label>Name : <span style="color:red">*</span></label>
                                <input type="text" id="softdrinks" name="softdrinks" class="form-control" required
                                    value="{{ $queryresult->softdrinks }}"
                                    data-parsley-required-message="Soft Drink name is required" maxlength='150'
                                    data-parsley-minlength="3"
                                    data-parsley-minlength-message="You need to enter at least 3 characters"
                                    data-parsley-trigger="change">
                                <span class="text-danger">
                                    @error('softdrinks')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>

                            <div class="col-sm-6 form-group">
                                <label>Price : <span style="color:red">*</span></label>
                                <input type="number" id="price" name="price" step="0.01" min="1"
                                    max="9999999" class="form-control" value="{{ $queryresult->price }}" required
                                    data-parsley-required-message="Price is required" data-parsley-trigger="change"
                                    data-parsley-gt="#nonzero">
                                <span class="text-danger">
                                    @error('price')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>

                            <div class="col-sm-6 form-group">
                                <label>Drinks Count : <span style="color:red">*</span></label>
                                <input type="number" id="drinksCount" name="drinksCount" step="0.01" min="1"
                                    max="9999999" class="form-control" value="{{ $queryresult->drinksCount }}" required
                                    data-parsley-required-message="Drinks Count is required" data-parsley-trigger="change"
                                    data-parsley-gt="#nonzero">
                                <span class="text-danger">
                                    @error('drinksCount')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Type : <span style="color:red">*</span></label>
                                <select class="form-control" id="type" name="type" required
                                    data-parsley-required-message="Type is required.">
                                    <option value="">Select</option>
                                    <option value="pop" @if ($queryresult->type == 'pop') selected @endif>Pop</option>
                                    <option value="bottle" @if ($queryresult->type == 'bottle') selected @endif>Bottle</option>

                                </select>
                                <span class="text-danger">
                                    @error('type')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>

                            <div class="col-md-12 form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description"  rows="3" class="form-control">{{ $queryresult->description }}</textarea>
                                <span class="text-danger">
                                    @error('description')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label" for="form-wizard-progress-wizard-softdrinkimage">Soft Drink
                                    Image</label>
                                <input type="file" id="file" class="form-control " name="softDrinkImage"
                                    accept=".jpg, .jpeg, .png">

                            </div>
                            @if (!empty($queryresult->softDrinkImage))
                                <div class="col-md-3 mb-3" id="eImage">
                                    <img class="img-thumbnail mb-2" width="100" height="100" id="showProfileImg"
                                        src="{{ url('uploads/softdrinks/' . $queryresult->softDrinkImage) . '?v=' . time() }}"
                                        data-src="">
                                    <a class="btn btn-danger text-white"
                                        onclick="deleteImage('{{ $queryresult->code }}','{{ $queryresult->softDrinkImage }}');"><i
                                            class="fa fa-trash"></i></a>
                                </div>
                            @endif
                            <div class="col-md-3 mb-3 d-none" id="eDisImage">
                                <img class="img-thumbnail mb-2" width="100" height="100" id="showImage"
                                    src="" data-src="">
                            </div>
                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="isActive" name="isActive"
                                        value="1" {{ $queryresult->isActive == 1 ? 'checked' : '' }}>
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
<script type="text/javascript" src="{{ asset('theme/js/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/parsley-fields-comparison-validators.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/init_site/softdrinks/edit.js?v=' . time()) }}"></script>

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
