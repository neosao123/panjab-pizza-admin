@extends('template.master', ['pageTitle' => 'Role Wise Rights'])
@push('styles')
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('theme/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('theme/css/sweetalert2.min.css') }}">
<link href="{{ asset('theme/css/toastr.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Role Wise Rights</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Role Wise Rights</a></li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-7 align-self-center">
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row g-3 mb-3">
        <div class="col-lg-12">
            @if (session('success'))
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                {{ session('success') }}
            </div>
            @endif
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-6">
                            <h5 class="mb-0" data-anchor="data-anchor">Filter</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3 form-group">
                            <label>Roles:</label>
                            <div class="form-group">
                                <select class="select2 form-control custom-select" style="width: 100%;" name="role" id="role">
                                    <option value="">Select</option>
                                    @foreach ($roles as $item)
                                    <option value="{{ $item->code }}">{{ $item->role }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-3 form-group mt-4">
                            <button type="button" onclick="getMenuList()" name="btnSearch" class="btn btn-success">Search</button>
                            <button type="button" onclick="clearSelection()" class="btn btn-outline-danger" id="btnClear">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card d-none" id="rightsDiv">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last" id="leftdiv">
                            <h5>View Management</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="menuHtml">

                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-success white me-1 mb-1 sub_1" onclick="updateMenuRights()" id="submitBtn">Submit</button>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript" src="{{ asset('theme/js/toastr.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/datatables.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/datatable-basic.init.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/init_site/rolewiserights/index.js?v=' . time()) }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.full.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.init.js') }}"></script>
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