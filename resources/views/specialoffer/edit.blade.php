@extends('template.master', ['pageTitle' => 'Special Offer Update'])
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
                            <li class="breadcrumb-item">Edit</li>
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
    @if ($queryresult)
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
                    @php
                        $i = 1;
                        $itemCount = 0;
                        if ($specialofferline && count($specialofferline) > 0) {
                            $itemCount = count($specialofferline);
                        }
                    @endphp
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <form action="{{ url('/specialoffer/update') }}" id="inFrom" method="post"
                        enctype="multipart/form-data" data-parsley-validate="">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="rowCount" id="rowCount" value="{{ $itemCount }}" readonly>
                            <input type="hidden" name="code" id="code" value="{{ $queryresult->code }}" readonly>
                            <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                            <div class="col-md-8 form-group">
                                <label> Name : <span style="color:red">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required
                                    value="{{ $queryresult->name }}" data-parsley-required-message="Name is required"
                                    maxlength='150' data-parsley-minlength="3"
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
                                    value="{{ $queryresult->subtitle }}" maxlength='150' data-parsley-minlength="3"
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
                                    data-parsley-trigger="change">{{ $queryresult->description }}</textarea>
                                <span class="text-danger">
                                    {{ $errors->first('description') }}
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            @php
                                $cnt = 0;
                                $pizza_prices = json_decode($queryresult->pizza_prices, true);
                            @endphp

                            @foreach ($pizzaPrices as $item)
                                @php
                                    $price = $item->price; // Set the default price from the table
                                    if ($pizza_prices) {
                                        foreach ($pizza_prices as $price_item) {
                                            // Only update the price if the shortcode matches
                                            if ($item->shortcode == $price_item['shortcode']) {
                                                $price = $price_item['price'];
                                                break; // Break out of the loop once a match is found
                                            }
                                        }
                                    }
                                @endphp
                                <div class="col-sm-6 col-md-2 col-lg-2 form-group">
                                    <label>Price for {{ $item->size }}: <span style="color:red">*</span></label>
                                    <input type="hidden" id="size_{{ $item->size }}"
                                        name="pizzaPrice[{{ $cnt }}][size]" value="{{ $item->size }}" readonly>
                                    <input type="hidden" id="shortcode_{{ $item->shortcode }}"
                                        name="pizzaPrice[{{ $cnt }}][shortcode]" value="{{ $item->shortcode }}"
                                        readonly>
                                    <input type="number" id="price_{{ $item->size }}"
                                        name="pizzaPrice[{{ $cnt }}][price]" step="0.01" min="0"
                                        max="9999" class="form-control" value="{{ $price }}" required
                                        data-parsley-required-message="Price is required">
                                </div>
                                @php
                                    $cnt++;
                                @endphp
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-lg-2 form-group">
                                <label>Deal Type:</label>
                                @php
                                    $dealType = $queryresult->dealType;
                                @endphp
                                <div class="form-group">
                                    <select class="form-control" id="dealType" name="dealType" required
                                        data-parsley-required-message="Deal Type is required">
                                        <option value="">---- Choose type of deal ----</option>
                                        <option value="pickupdeal"
                                            {{ !empty($queryresult) && $dealType == 'pickupdeal' ? 'selected' : '' }}>
                                            Pickup Deal</option>
                                        <option value="deliverydeal"
                                            {{ !empty($queryresult) && $dealType == 'deliverydeal' ? 'selected' : '' }}>
                                            Delivery Deal</option>
                                        <option value="otherdeal"
                                            {{ !empty($queryresult) && $dealType == 'otherdeal' ? 'selected' : '' }}>Others
                                        </option>
                                    </select>
                                </div>
                                <span class="text-danger" id="showextraLargePriceError">
                                    @error('dealType')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-3 col-lg-2 form-group">
                                <label>Number of pizza : <span style="color:red">*</span></label>
                                <input type="number" id="noofPizza" name="noofPizza" class="form-control"
                                    value="{{ $queryresult->noofPizza }}" required
                                    data-parsley-required-message="Number of Pizza is required">
                                <span class="text-danger">
                                    @error('noofPizza')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-3 col-lg-2 form-group d-none">
                                <label>Number of toppings : </label>
                                <input type="number" id="noofToppings" name="noofToppings" class="form-control"
                                    max="20" value="{{ $queryresult->noofToppings }}">
                                <span class="text-danger">
                                    @error('noofToppings')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-3 col-lg-2 form-group">
                                <label>Number of dips : <span style="color:red">*</span></label>
                                <input type="number" id="noofDips" name="noofDips" class="form-control"
                                    max="500" value="{{ $queryresult->noofDips }}" required
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
                                    max="500" value="{{ $queryresult->noofSides }}" required
                                    data-parsley-required-message="Number of sides is required">
                                <span class="text-danger">
                                    @error('noofSides')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                            <div class="col-md-6 col-lg-4 form-group">
                                <label>Side Types:</label>
                                <div class="form-group">
                                    @php
                                        $typeCode = json_decode($queryresult->type, true);

                                    @endphp
                                    <select class="select2 form-control" multiple id="type" name="type[]"
                                        style="width: 100%;">
                                        <option value="">Select</option>
                                        <option value="side"
                                            {{ !empty($typeCode) ? (in_array('side', $typeCode) ? 'selected' : '') : '' }}>
                                            Side
                                        </option>
                                        <option value="subs"
                                            {{ !empty($typeCode) ? (in_array('subs', $typeCode) ? 'selected' : '') : '' }}>
                                            Subs
                                        </option>
                                        <option value="poutine"
                                            {{ !empty($typeCode) ? (in_array('poutine', $typeCode) ? 'selected' : '') : '' }}>
                                            Poutine</option>
                                        <option value="plantbites"
                                            {{ !empty($typeCode) ? (in_array('plantbites', $typeCode) ? 'selected' : '') : '' }}>
                                            Plant Bites</option>
                                        <option value="tenders"
                                            {{ !empty($typeCode) ? (in_array('tenders', $typeCode) ? 'selected' : '') : '' }}>
                                            Tenders</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12" id="showSide"
                                {{ $queryresult->noofSides == '0' ? 'style=display:none;' : '' }}>
                                <table class="table table-bordered" id="tbl-sides" style="width:100%">
                                    <thead>
                                        <th style="width:40%">Sides</th>
                                        <th style="width:40%">Size</th>
                                        <th style="width:20%">Action</th>
                                    </thead>
                                    <tbody id="tableBody">
                                        @if ($itemCount > 0)
                                            @for ($i = 0; $i < $itemCount; $i++)
                                                <tr id="row{{ $i }}" class="tblrows"
                                                    data-type="{{ $specialofferline[$i]->type }}">
                                                    <td>
                                                        <select class="select2 form-control custom-select side"
                                                            id="sides{{ $i }}" name="sides[]"
                                                            style="width:100%"
                                                            onchange="checkDuplicateItem({{ $i }});">
                                                            <option value="{{ $specialofferline[$i]->sidemasterCode }}"
                                                                required data-parsely-required-message="Side is required.">
                                                                {{ $specialofferline[$i]->sidename }}</option>
                                                        </select>
                                                    </td>

                                                    <td>
                                                        <select class="select2 form-control custom-select size"
                                                            id="size{{ $i }}" name="size[]"
                                                            style="width:100%">
                                                            <option value="{{ $specialofferline[$i]->sidelineentries }}"
                                                                required data-parsely-required-message="Size is required.">
                                                                {{ $specialofferline[$i]->size }}
                                                                {{ $specialofferline[$i]->price }}</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" id="rowCode{{ $i }}"
                                                            name="rowCode[]" value="{{ $specialofferline[$i]->code }}"
                                                            readonly>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger del-button"
                                                            data-row-id="row{{ $i }}"><i
                                                                class="fa fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endfor
                                        @else
                                            <tr id="row0" class="tblrows">
                                                <td>
                                                    <select class="select2 form-control custom-select side" id="sides0"
                                                        name="sides[]" style="width:100%"
                                                        onchange="checkDuplicateItem(0);"
                                                        {{ $queryresult->noofSides == '0' ? '' : 'required' }}>

                                                    </select>
                                                </td>

                                                <td>
                                                    <select class="select2 form-control custom-select size" id="size0"
                                                        name="size[]" style="width:100%"
                                                        {{ $queryresult->noofSides == '0' ? '' : 'required' }}>

                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="hidden" id="rowCode0" name="rowCode[]" value="-"
                                                        readonly>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger del-button"
                                                        data-row-id="row0"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2">
                                                <button type="button"
                                                    class="btn btn-outline-success add-sides">Add</button>
                                            </td>

                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Pops:</label>
                                <div class="form-group">
                                    <select class="select2 form-control" id="pops" name="pops"
                                        style="width: 100%;">
                                        <option value="">Select</option>
                                        @foreach ($pops as $item)
                                            <option value="{{ $item->code }}"
                                                {{ $item->code == $queryresult->pops ? 'selected' : '' }}>
                                                {{ $item->softdrinks }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Bottle:</label>
                                <div class="form-group">
                                    <select class="select2 form-control" id="bottle" name="bottle"
                                        style="width: 100%;">
                                        <option value="">Select</option>
                                        @foreach ($bottle as $item)
                                            <option value="{{ $item->code }}"
                                                {{ $item->code == $queryresult->bottle ? 'selected' : '' }}>
                                                {{ $item->softdrinks }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 form-group">
                                <label class="form-label" for="form-wizard-progress-wizard-specialofferphoto">Special
                                    Offer Image</label>
                                <input type="file" id="file" class="form-control " name="specialofferphoto"
                                    accept=".jpg, .jpeg, .png">

                            </div>

                            @if (!empty($queryresult->specialofferphoto))
                                <div class="col-md-3 form-group mb-3" id="eImage">
                                    <img class="img-thumbnail mb-2" width="100" height="100" id="showProfileImg"
                                        src="{{ url('uploads/specialoffer/' . $queryresult->specialofferphoto) . '?v=' . time() }}"
                                        data-src="">
                                    <a class="btn btn-danger text-white"
                                        onclick="deleteImage('{{ $queryresult->code }}','{{ $queryresult->specialofferphoto }}');"><i
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

                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="showOnClient"
                                        name="showOnClient" value="1"
                                        {{ $queryresult->showOnClient == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="showOnClient">Show On Client</label>
                                </div>
                            </div>

                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="limited_offer"
                                        name="limited_offer" value="1"
                                        {{ $queryresult->limited_offer == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="limited_offer">Limited Offer</label>
                                </div>
                            </div>

                            <div class="col-md-4 col-lg-3 form-group">
                                <label>Offer Start Date:</label>
                                <input type="datetime-local" id="start_date" name="start_date" class="form-control"
                                    value="{{ $queryresult->start_date ? $queryresult->start_date : old('start_date') }}">
                                <span class="text-danger" id="startDateError">
                                    @error('start_date')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>

                            <div class="col-md-4 col-lg-3 form-group">
                                <label>Offer End Date:</label>
                                <input type="datetime-local" id="end_date" name="end_date" class="form-control"
                                    value="{{ $queryresult->end_date ? $queryresult->end_date : old('end_date') }}">
                                <span class="text-danger" id="startDateError">
                                    @error('end_date')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>

                            <div class="col-sm-12 form-group">
                                <button class="btn btn-primary" type="submit" id="submit"> Update </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('scripts')
    <script type="text/javascript" src="{{ asset('theme/js/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/init_site/specialoffer/edit.js?v=' . time()) }}"></script>

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
