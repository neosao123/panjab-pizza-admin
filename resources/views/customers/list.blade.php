@extends('template.master', ['pageTitle'=>"Customer List"])
@push('styles')
 <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('theme/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.2/css/buttons.dataTables.min.css">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Customer</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Customer</a></li>
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
		  </div>
          <div class="card-body">
			<div class="row">
			   <div class="col-sm-3 form-group">
					<label>Customer:</label>
					<div class="form-group">
					   <select class="select2 form-control custom-select" name="customercode" id="customercode" style="width: 100%; height:36px;">
							
					   </select>
					</div>
			   </div>
			   <div class="col-sm-3 form-group">
					<label>Email:</label>
					<div class="form-group">
					   <select class="select2 form-control custom-select" name="email" id="email" style="width: 100%; height:36px;">
							
					   </select>
					</div>
			   </div>
			   <div class="col-md-3 form-group">
					<label>Mobile Number:</label>
					<div class="form-group">
					   <select class="select2 form-control custom-select" name="mobile" id="mobile" style="width: 100%; height:36px;">
							
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
                <h5 class="mb-0" data-anchor="data-anchor">Customer list</h5>
              </div>
            </div>
		 </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-Customer" style="width:100%" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th style="width:5%" >Sr. No. </th>
					<th style="width:5%">Operations</th>
                    <th style="width:50%">Name</th>
                    <th style="width:20%">Email</th>
					<th style="width:10%">Mobile Number</th>					
					<th style="width:10%">Status</th>					
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <table id="customerReport" class="table table-striped table-bordered d-none">
	<thead>
		<tr>
			<th>Sr. No. </th>
			  <th>Name</th>
			  <th>Email</th>
			  <th>Contact Number</th>			 
			  <th>Status</th>	                    
		</tr>
	</thead>
</table> 
@endsection
@push('scripts')
 <script type="text/javascript" src="{{ asset('theme/js/datatables.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/datatable-basic.init.js') }}"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js"></script>
<script type="text/javascript" src="{{ asset('theme/init_site/customers/index.js?v=' . time()) }}"></script>
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
