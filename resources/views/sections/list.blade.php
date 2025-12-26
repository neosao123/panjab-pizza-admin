@extends('template.master', ['pageTitle' => 'Sections'])
@push('styles')
  <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('theme/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Sections</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Sections</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
        <a href="{{ url('sections/add') }}" class="btn btn-info btn-sm float-right">Add New</a>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row g-3 mb-3">
      <div class="col-lg-12">
        @if (session('success'))
          <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"> 
              <span aria-hidden="true">×</span> 
            </button>
            {{ session('success') }}
          </div>
        @endif
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-sm-6">
                <h5 class="mb-0" data-anchor="data-anchor">Sections List</h5>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-sections" style="width:100%" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th style="width:10%">Sr. No.</th>
                    <th style="width:10%">Action</th>
                    <th>Title</th>
                    <th>Sub Title</th>
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
@endsection
@push('scripts')
  <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/datatables.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/datatable-basic.init.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/init_site/sections/index.js?v=' . time()) }}"></script>
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