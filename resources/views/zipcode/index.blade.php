@extends('template.master', ['pageTitle' => 'Postal Codes List'])
@push('styles')
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link rel="stylesheet" href="{{ asset('theme/init_site/zipcode/index.css') }}">
<link href="{{ asset('theme/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Postal Codes</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Postal Codes</a></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" data-anchor="data-anchor">Postal Codes</h5>
                </div>
                <div class="card-body pt-0">
                    <form id="zipcodeForm" class="" data-parsley-validate="">
                        @csrf
                        <input type="hidden" value="" name="code" id="code">
                        <div class="mb-3">
                            <label class="form-label">Postal Code</label>
                            <input class="form-control" type="text" id="zipcode" name="zipcode" value="" placeholder="Ex. A1A1A1" required="" data-parsley-required-message="Postal Code is required." />
                            <span class="text-danger">
                                {{ $errors->first('zipcode') }}
                            </span>
                        </div>
                        <div class="mb-3 w-100">
                            <select class="form-control w-100" name="storeLocation" id="storeLocation">
                                <option value="">--- Choose Store Location ---</option>
                                @foreach ($storelocation as $storelocations)
                                <option value="{{ $storelocations->code }}">{{ $storelocations->storeLocation }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger">
                                {{ $errors->first('storeLocation') }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" name="isActive" id="isActive" type="checkbox" value="1" />
                                <label class="custom-control-label" for="isActive">Active</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-success btnsubmit">Submit</button>
                            <button type="button" class="btn btn-danger" onclick="window.location.reload();">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="row card-header justify-content-between align-items-center">
                    <div class="">
                        <h5 class="mb-0" data-anchor="data-anchor">Postal Codes List</h5>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ url('/storage/app/public/template/PostalCode_Template.xlsx') }}" class="me-4 btn btn-sm btn-secondary" download="">Download Excel Format</a>
                        <a href="{{ url('/zipcode/import') }}" class="btn btn-sm btn-primary">Import </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="dataTable-delieverable-zipcode" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>Sr. No. </th>
                                    <th>Postal Code</th>
                                    <th>Store Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/datatables.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/parsley-fields-comparison-validators.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/datatable-basic.init.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/init_site/zipcode/index.js?v=' . time()) }}"></script>
@if (session('success'))
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