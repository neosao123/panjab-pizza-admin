@extends('template.master', ['pageTitle' => 'Postal Codes List'])
@push('styles')
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
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
                        <li class="breadcrumb-item"><a href="{{ url('/deliverable/zipcode') }}">Postal Codes</a></li>
                        <li class="breadcrumb-item"><a href="#">Import Postal Code</a></li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-7 align-self-center ">
            <a href="{{ url('/deliverable/zipcode') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row g-3 mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" data-anchor="data-anchor">Import Postal Codes</h5>
                </div>
                <div class="card-body pt-0 py-2">
                    <form id="zipcodeForm" class="" data-parsley-validate="">
                        @csrf
                        <div class="row gx-3">
                            <div class="col-6 ">
                                <label class="form-label">Store Location</label>
                                <select class="form-control w-100" name="storeLocation" id="storeLocation">
                                </select>
                                <span class="text-danger">
                                    {{ $errors->first('storeLocation') }}
                                </span>
                            </div>
                            <div class="col-6 ">
                                <label class="form-label">Postal Code</label>
                                <input class="form-control" type="file" id="zipcodeFile" name="zipcodeFile" accept=".xls, .xlsx" required="" data-parsley-required-message="Postal Codes is required." />
                                <span class="text-danger">
                                    {{ $errors->first('zipcode') }}
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <button id="btn-import" type="button" class="btn btn-success btn-import">Import</button>
                            <button type="button" class="btn btn-danger" onclick="window.location.reload();">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12" id="xlx-section" style="display:none">
            <div class="card">
                <div class="card-header">
                    <h4 id="imported-title"></h4>
                </div>
                <div class="card-body">
                    <div class="text-danger" id="errors"></div>
                    <div id="imported-excel" class="table-responsive" style="max-height:400px;"></div>
                </div>
                <div class="card-footer">
                    <button id="btn-upload" class="btn btn-primary" type="button">Upload Data</button>
                </div>
            </div>
        </div>
    </div>
    @endsection
    @push('scripts')
    <script>
        const baseUrl = "{{ url('/') }}";
    </script>
    <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/datatables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/parsley-fields-comparison-validators.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/datatable-basic.init.js') }}"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.5/xlsx.full.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.8.0/xlsx.js"></script> -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.5/jszip.js"></script>
    <script type="text/javascript" src="{{ asset('theme/init_site/zipcode/import.js?v=' . time()) }}"></script>
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