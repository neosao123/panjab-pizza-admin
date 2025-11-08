@extends('template.master', ['pageTitle' => 'Special Offer add'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/js/summernote/dist/summernote-bs4.css') }}" rel="stylesheet">
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
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Special Offer</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/specialoffer/list') }}">Special Offer</a></li>
                            <li class="breadcrumb-item">Add</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/specialoffer/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
            </div>
            <?php } ?>
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
                <form id="inFrom" action="{{ url('/specialoffer/store') }}" method="post" enctype="multipart/form-data"
                    data-parsley-validate="">
                    @csrf
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label>Name : <span style="color:red">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required
                                value="{{ old('name') }}" data-parsley-required-message="Name is required" maxlength='150'
                                data-parsley-minlength="3"
                                data-parsley-minlength-message="You need to enter at least 3 characters"
                                data-parsley-trigger="change">
                            <span class="text-danger">
                                @error('name')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Sub-title :</label>
                            <input type="text" id="subtitle" name="subtitle" class="form-control"
                                value="{{ old('subtitle') }}" maxlength='150' data-parsley-minlength="3"
                                data-parsley-minlength-message="You need to enter at least 3 characters"
                                data-parsley-trigger="change" />
                            <span class="text-danger">
                                @error('subtitle')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <div class="col-md-12 form-group">
                            <label> Description : </label>
                            <textarea id="description" name="description" class="form-control summernote" rows="4" maxlength='300'
                                data-parsley-minlength="2" data-parsley-minlength-message="You need to enter at least 2 characters"
                                data-parsley-trigger="change">{{ old('description') }}</textarea>
                            <span class="text-danger">
                                {{ $errors->first('description') }}
                            </span>
                        </div>
                    </div>
                    <div class="row">

                        @php
                            $cnt = 0;
                        @endphp

                        @foreach ($pizzaPrices as $item)
                            <div class="col-sm-6 col-md-2 col-lg-2 form-group">
                                <label>Price for {{ $item->size }}: <span style="color:red">*</span></label>
                                <input type="hidden" id="size_{{ $item->size }}"
                                    name="pizzaPrice[{{ $cnt }}][size]" value="{{ $item->size }}" readonly>
                                <input type="hidden" id="size_{{ $item->shortcode }}"
                                    name="pizzaPrice[{{ $cnt }}][shortcode]" value="{{ $item->shortcode }}"
                                    readonly>
                                <input type="number" id="{{ $item->size }}"
                                    name="pizzaPrice[{{ $cnt }}][price]" step="0.01" min="0"
                                    max="9999" class="form-control" value="{{ $item->price }}" required
                                    data-parsley-required-message="Price is required">
                                <span class="text-danger">
                                    @error("pizzaPrice[{{ $cnt }}][price]")
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            @php
                                $cnt++;
                            @endphp
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-lg-2 form-group">
                            <label>Deal Type:</label>
                            <div class="form-group">
                                <select class="form-control" id="dealType" name="dealType" required
                                    data-parsley-required-message="Deal Type is required">
                                    <option value="">---- Choose type of deal ----</option>
                                    <option value="pickupdeal">Pickup Deal</option>
                                    <option value="deliverydeal">Delivery Deal</option>
                                    <option value="otherdeal">Others</option>
                                </select>
                            </div>
                            <span class="text-danger" id="showextraLargePriceError">
                                @error('dealType')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>
                        <div class="col-md-3 col-lg-2 form-group">
                            <label>Number of pizzas : <span style="color:red">*</span></label>
                            <input type="number" id="noofPizza" name="noofPizza" class="form-control"
                                value="{{ old('noofPizza') }}" max="500" required
                                data-parsley-required-message="Number of Pizza is required">
                            <span class="text-danger">
                                @error('noofPizza')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>
                        <div class="col-md-3 col-lg-2 d-none form-group">
                            <label>Number of toppings : </label>
                            <input type="number" id="noofToppings" name="noofToppings" class="form-control"
                                value="{{ old('noofToppings') }}" max="20">
                            <span class="text-danger">
                                @error('noofToppings')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>
                        <div class="col-md-3 col-lg-2 form-group">
                            <label>Number of dips : <span style="color:red">*</span></label>
                            <input type="number" id="noofDips" name="noofDips" class="form-control"
                                value="{{ old('noofDips') }}" max="500" required
                                data-parsley-required-message="Number of Dips is required">
                            <span class="text-danger">
                                @error('noofDips')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>
                        <div class="col-md-3 col-lg-2 form-group">
                            <label>Number of sides : <span style="color:red">*</span></label>
                            <input type="number" id="noofSides" name="noofSides" class="form-control"
                                value="{{ old('noofSides') }}" max="500" required
                                data-parsley-required-message="Number of sides is required">
                            <span class="text-danger">
                                @error('noofSides')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>
                        <div class="col-md-4 col-lg-4 form-group">
                            <label>Side Types:</label>
                            <div class="form-group">
                                <select class="select2 form-control" multiple id="type" name="type[]"
                                    style="width: 100%;">
                                    <option value="">Select</option>
                                    <option value="side">Side</option>
                                    <option value="subs">Subs</option>
                                    <option value="poutine">Poutine</option>
                                    <option value="plantbites">Plant Bites</option>
                                    <option value="tenders">Tenders</option>
                                </select>
                            </div>
                            <input type="hidden" name="rowCount" value="0" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12" id="showSide" {{ old('noofSides') == '0' ? 'style=display:none;' : '' }}>
                            <table class="table table-bordered" id="tbl-sides" style="width:100%">
                                <thead>
                                    <th style="width:40%">Sides</th>
                                    <th style="width:40%">Size</th>
                                    <th style="width:20%">Action</th>
                                </thead>
                                <tbody id="tableBody">
                                    <tr id="row0" class="tblrows" data-type="">
                                        <td>
                                            <select class="select2 form-control custom-select side" id="sides0"
                                                name="sides[]" style="width:100%" onchange="checkDuplicateItem(0);"
                                                required data-parsley-required-message="Side is required">

                                            </select>
                                        </td>

                                        <td>
                                            <select class="select2 form-control custom-select size" id="size0"
                                                name="size[]" style="width:100%" required
                                                data-parsley-required-message="Size is required.">

                                            </select>
                                        </td>
                                        <td>
                                            <input type="hidden" id="rowCode0" name="rowCode[]" value="-"
                                                readonly>
                                            <button type="button" class="btn btn-sm btn-outline-danger del-button"
                                                data-row-id="row0"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2">
                                            <button type="button" class="btn btn-outline-success add-sides">Add</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Pops:</label>
                            <div class="form-group">
                                <select class="select2 form-control" id="pops" name="pops" style="width: 100%;">
                                    <option value="">Select</option>
                                    @foreach ($pops as $item)
                                        <option value="{{ $item->code }}">{{ $item->softdrinks }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Bottle:</label>
                            <div class="form-group">
                                <select class="select2 form-control" id="bottle" name="bottle" style="width: 100%;">
                                    <option value="">Select</option>
                                    @foreach ($bottle as $item)
                                        <option value="{{ $item->code }}">{{ $item->softdrinks }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 form-group">
                            <label class="form-label" for="form-wizard-progress-wizard-specialofferphoto">Special offer
                                image:</label>
                            <input type="file" id="file" class="form-control" name="specialofferphoto"
                                accept=".jpg, .jpeg, .png">

                        </div>

                        <div class="col-md-3" id="eImage">
                            <img class="img-thumbnail mb-2" width="100" height="100" id="showImage"
                                src="{{ asset('uploads/default-img.jpg') }}" data-src="">
                        </div>

                        <div class="col-md-12 form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="isActive" name="isActive"
                                    value="1" checked>
                                <label class="custom-control-label" for="isActive"> Status</label>
                            </div>
                        </div>

                        <div class="col-md-12 form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="showOnClient"
                                    name="showOnClient" value="1" checked>
                                <label class="custom-control-label" for="showOnClient">Show On Client</label>
                            </div>
                        </div>

                        <div class="col-md-12 form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="limited_offer"
                                    name="limited_offer" value="1" {{ old('limited_offer') == 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="limited_offer">Limited Offer</label>
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-3 form-group">
                            <label>Offer Start Date:</label>
                            <input type="datetime-local" id="start_date" name="start_date" class="form-control"
                                value="{{ old('start_date') }}">
                            <span class="text-danger" id="startDateError">
                                @error('start_date')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <div class="col-md-4 col-lg-3 form-group">
                            <label>Offer End Date:</label>
                            <input type="datetime-local" id="end_date" name="end_date" class="form-control"
                                value="{{ old('end_date') }}">
                            <span class="text-danger" id="startDateError">
                                @error('end_date')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <div class="col-md-12 form-group">
                            <button class="btn btn-primary" type="submit" id="submit"> Submit </button>
                            <button type="button" class="btn btn-danger"
                                onclick="window.location.reload();">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')

    <script type="text/javascript" src="{{ asset('theme/js/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/init_site/specialoffer/add.js?v=' . time()) }}"></script>

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
