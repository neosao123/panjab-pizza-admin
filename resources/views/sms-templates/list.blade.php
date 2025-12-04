@extends('template.master', ['pageTitle' => 'SMS Templates'])
@push('styles')
  <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('theme/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">SMS Templates</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="#">SMS Templates</a></li>
            </ol>
          </nav>
        </div>
      </div>
      @if($insertRights == 1)
      <div class="col-7 align-self-center">
        <a href="{{ url('/sms-templates/add') }}" class="btn btn-primary btn-sm float-right">
          <i class="fa fa-plus"></i> Add New
        </a>
      </div>
      @endif
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
        @if (session('error'))
          <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
            {{ session('error') }}
          </div>
        @endif
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-sm-6">
                <h5 class="mb-0" data-anchor="data-anchor">SMS Templates List</h5>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-sms-templates" style="width:100%" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th style="width:10%">Sr. No.</th>
                    <th style="width:10%">Action</th>
                    <th>Title</th>
                    <th>Template</th>
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
  <script type="text/javascript" src="{{ asset('theme/init_site/sms-templates/index.js?v=' . time()) }}"></script>
  @if (session('success') || session('error'))
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
