@extends('template.master', ['pageTitle' => 'Section Add'])
@push('styles')
  <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
  <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
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
    .line-entry-row {
      border: 1px solid #dee2e6;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 5px;
      background-color: #f8f9fa;
    }
  </style>
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Section</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/sections/list') }}">Sections</a></li>
              <li class="breadcrumb-item">Add</li>
            </ol>
          </nav>
        </div>
      </div>
      <?php if ($viewRights == 1) { ?>
      <div class="col-7 align-self-center ">
        <a href="{{ url('/sections/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-12">
            <h5 class="mb-0" data-anchor="data-anchor">Add Section</h5>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        <form id="inFrom" action="{{ url('/sections/store') }}" method="post" enctype="multipart/form-data"
          data-parsley-validate="">
          @csrf
          <div class="row">
            <div class="col-md-6 form-group">
              <label>Title: <span style="color:red">*</span></label>
              <input type="text" id="title" name="title" class="form-control" required
                value="{{ old('title') }}" data-parsley-required-message="Title is required" 
                data-parsley-minlength="2" data-parsley-minlength-message="You need to enter at least 2 characters" 
                data-parsley-trigger="change">
              <span class="text-danger">
                @error('title')
                  {{ $message }}
                @enderror
              </span>
            </div>

            <div class="col-md-6 form-group">
              <label>Sub Title:</label>
              <input type="text" id="subTitle" name="subTitle" class="form-control" value="{{ old('subTitle') }}">
              <span class="text-danger">
                @error('subTitle')
                  {{ $message }}
                @enderror
              </span>
            </div>
          </div>

          <div class="col-sm-12 mt-4">
            <h5>Section Line Entries</h5>
            <div id="lineEntriesContainer">
              <div class="line-entry-row" id="lineRow0" data-index="0">
                <div class="row">
                  <div class="col-md-4 form-group">
                    <label>Image: <span style="color:red">*</span></label>
                    <input type="file" name="line_image[]" accept=".jpg, .png, .jpeg"
                      class="form-control line-image" data-parsley-fileextension="jpg,png,jpeg" 
                      data-parsley-trigger="change" required data-parsley-required-message="Image is required">
                    <div class="mt-2 d-none image-preview" id="preview0">
                      <img class="img-thumbnail" width="100" height="100" 
                        style="width: 150px; height:100px" src="" >
                    </div>
                  </div>
                  <div class="col-md-4 form-group">
                    <label>Title: <span style="color:red">*</span></label>
                    <input type="text" name="line_title[]" class="form-control" required
                      data-parsley-required-message="Title is required" 
                      data-parsley-minlength="2" 
                      data-parsley-trigger="change">
                  </div>
                  <div class="col-md-3 form-group">
                    <label>Counter: <span style="color:red">*</span></label>
                    <input type="text" name="counter[]" class="form-control" 
                     required
                      data-parsley-required-message="Counter is required" >
                  </div>
                  <div class="col-md-1 form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-block remove-line" 
                      data-row-id="lineRow0">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <button type="button" class="btn btn-outline-success" id="addLineEntry">
              <i class="fa fa-plus"></i> Add Line Entry
            </button>
          </div>

          <div class="col-md-12 form-group mt-4">
            <button class="btn btn-primary" type="submit" id="submit">Submit</button>
            <button type="button" class="btn btn-danger" onclick="window.location.reload();">Reset</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/init_site/sections/add.js?v=' . time()) }}"></script>
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