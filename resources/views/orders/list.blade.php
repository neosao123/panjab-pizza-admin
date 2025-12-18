@extends('template.master', ['pageTitle'=>"Orders List"])
@push('styles')
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('theme/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Orders</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Orders</a></li>
                    </ol>
                </nav>
            </div>
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
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3 form-group">
                            <label>Orders Code:</label>
                            <div class="form-group">
                                <select class="select2 form-control custom-select" style="width: 100%;" name="orders" id="orders">

                                </select>
                            </div>
                        </div>
						<div class="col-sm-3 form-group">
                            <label>Order From:</label>
                            <div class="form-group">
                                <select class="select2 form-control" id="orderfrom" name="orderfrom" style="width: 100%;">
                                       <option value="">Select</option>
									   <option value="store">store</option>
									   <option value="online">online</option>
                                </select>
                            </div>
                        </div>
						<div class="col-sm-3 form-group">
                            <label>Order Status:</label>
                            <div class="form-group">
                                <select class="select2 form-control" id="orderSta" name="orderStatus" style="width: 100%;">
                                       <option value="">Select</option>
									   <option value="placed">Placed</option>
									   <option value="pending">pending</option>
									   <option value="picked-up">Picked Up</option>
									   <option value="delivered">delivered</option>
									   <option value="shipping">shipping</option>
                                       <option value="cancelled">cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" id="btnSearch" name="btnSearch" class="btn btn-success">Search</button>
                            <button type="Reset" class="btn btn-outline-danger" id="btnClear">Clear</button>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-6">
                                <h5 class="mb-0" data-anchor="data-anchor">Orders list</h5>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTable-Orders" style="width:100%" class="table table-striped table-bordered nowrap">
                                <thead>
                                    <tr>
                                        <th style="width:5%">Sr. No. </th>
										<th style="width:5%">Action</th>
                                        <th style="width:10%">Order Number</th>
										<th style="width:15%">Date</th>
                                        <th style="width:10%">Order Code</th>
										<th style="width:15%">Customer Name</th>
										<th style="width:10%">Mobile Number</th>
                                        <th style="width:10%">Postal Code</th>
                                        <th style="width:10%">Order From</th>
                                        <th style="width:15%">Store Location</th>
                                        <th style="width:10%">Grand Total</th>
                                        <th style="width:5%">Order Status</th>
                                        <th style="width:5%">Payment Status</th>
										<th style="width:5%">Client Type</th>
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
	<script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/datatables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/datatable-basic.init.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/init_site/orders/index.js?v=' . time()) }}"></script>
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
