@extends('template.master', ['pageTitle' => 'Sides Add'])
@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Sides Add</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/sides/list') }}">Side</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </nav>
            </div>
        </div>
        @if ($viewRights == 1)
        <div class="col-7 align-self-center">
            <a href="{{ url('/sides/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif
            <form id="addressform" action="{{ url('/sides/store') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
                @csrf
                <input type="hidden" name="code" value="">
                <input type="hidden" name="id" value="">
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>Sides Name : <span style="color:red">*</span></label>
                        <input type="text" id="sideName" name="sideName" class="form-control" required value="{{ old('sideName') }}" data-parsley-required-message="Side name is required" maxlength='150' data-parsley-minlength="3" data-parsley-minlength-message="You need to enter at least 3 characters" data-parsley-trigger="change" onkeypress="return ValidateAlpha(event)">
                        <span class="text-danger">
                            @error('sideName')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>

                    <div class="col-md-6">
                        <label>Type <span style="color:red">*</span></label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="side" {{ old('type') == 'side' ? 'selected' : '' }}>Side</option>
                            <option value="subs" {{ old('type') == 'subs' ? 'selected' : '' }}>Subs</option>
                            <option value="poutine" {{ old('type') == 'poutine' ? 'selected' : '' }}>Poutine</option>
                            <option value="plantbites" {{ old('type') == 'plantbites' ? 'selected' : '' }}>Plant Bites</option>
                            <option value="tenders" {{ old('type') == 'tenders' ? 'selected' : '' }}>Tenders</option>
                        </select>
                        <span class="text-danger">
                            @error('type')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>

                    <div class="col-md-3 form-group ">
                        <label class="form-label" for="form-wizard-progress-wizard-sidesimage">Side Image</label>
                        <input type="file" id="file" class="form-control " name="sidesImage" accept=".jpg, .jpeg, .png">
                    </div>
                </div>

                <!-- {{-- Repeater table for Size & Price --}} -->
                <div class="row mt-3">
                    <div class="col-sm-12" id="showSide">
                        <table class="table table-bordered" id="tbl-sides" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width:40%">Size</th>
                                    <th style="width:40%">Price</th>
                                    <th style="width:20%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr id="row0">
                                    <td>
                                        <input type="text" class="form-control" name="size[]" required placeholder="Size">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="form-control" name="price[]" required placeholder="Price">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger del-button" data-row-id="row0">
                                            <i class="fa fa-trash"></i>
                                        </button>
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
                </div>

                <!-- Toppings -->
                <div class="row mt-3">
                    <div class="col-md-12 form-group ">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="hasToppings" name="hasToppings" value="1" {{ old('hasToppings') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="hasToppings">Has Toppings</label>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4 form-group">
                        <label>Number of Toppings : <span style="color:red">*</span></label>
                        <input type="number" step="1" id="nooftoppings" name="nooftoppings" class="form-control" min="0" value="{{ old('nooftoppings') }}">
                        <span class="text-danger">
                            @error('nooftoppings')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>

                <div class="row mt-3">
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

<script type="text/javascript" src="{{ asset('theme/js/summernote/dist/summernote-bs4.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/init_site/sides/add.js?v=' . time()) }}"></script>


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
