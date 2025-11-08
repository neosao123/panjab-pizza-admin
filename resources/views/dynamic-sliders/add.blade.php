@extends('template.master', ['pageTitle' => 'Dynamic Slider Add'])
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
        <h4 class="page-title">Dynamic Slider</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/dynamic-sliders/list') }}">Dynamic Slider</a></li>
              <li class="breadcrumb-item">Add</li>
            </ol>
          </nav>
        </div>
      </div>
      <?php if ($viewRights == 1) { ?>
      <div class="col-7 align-self-center ">
        <a href="{{ url('/dynamic-sliders/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-12">
            <h5 class="mb-0" data-anchor="data-anchor">Add</h5>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        <form id="inFrom" action="{{ url('/dynamic-sliders/store') }}" method="post" enctype="multipart/form-data"
          data-parsley-validate="">
          @csrf
          <div class="row">
            <div class="col-md-6 form-group">
              <label>Title : <span style="color:red">*</span></label>
              <input type="text" id="title" name="title" class="form-control" required
                value="{{ old('title') }}" data-parsley-required-message="Title is required" data-parsley-minlength="2"
                data-parsley-minlength-message="You need to enter at least 2 characters" data-parsley-trigger="change">
              <span class="text-danger">
                @error('title')
                  {{ $message }}
                @enderror
              </span>
            </div>

            <div class="col-md-6 form-group d-none">
              <label>Sub Title : </label>
              <input type="text" id="subTitle" name="subTitle" class="form-control" value="{{ old('subTitle') }}">
              <span class="text-danger">
                @error('subTitle')
                  {{ $message }}
                @enderror
              </span>
            </div>

            <div class="col-sm-12 mt-3">
              <h5>Home Slider Images (Large, Medimum & Small screens)</h5>
              <div><small>Accepts only JPG, JPEG, and PNG formats.</small></div>
              <div class="row mt-3">
                <div class="col-md-6 col-lg-4 form-group">
                  <label>Large Slider Image: <small>1280px (w) X 480px (h) </small></label>
                  <input type="file" id="background_image" name="background_image" accept=".jpg, .png, .jpeg"
                    class="form-control" data-parsley-fileextension="jpg,png,jpeg" data-parsley-trigger="change" required
                    data-parsley-required-message="Background Image is required.">
                  @error('background_image')
                    {{ $message }}
                  @enderror
                  </span>
                </div>
                <div class="col-auto d-none" id="eImage">
                  <img class="img-thumbnail  mb-2" width="100" height="100" style="width: 320px; height:180px"
                    id="showImage" src="" data-src="">
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-md-6 col-lg-4 form-group">
                  <label>Medimum Slider Image: <small>720px (w) X 648px (h)</small></label>
                  <input type="file" id="background_image_md" name="background_image_md" accept=".jpg, .png, .jpeg"
                    class="form-control" data-parsley-fileextension="jpg,png,jpeg" data-parsley-trigger="change" required
                    data-parsley-required-message="Background Image is required.">
                  @error('background_image')
                    {{ $message }}
                  @enderror
                  </span>
                </div>
                <div class="col-auto d-none" id="eImageMd">
                  <img class="img-thumbnail  mb-2" width="100" height="100" style="width: 320px; height:180px"
                    id="showImageMd" src="" data-src="">
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-md-6 col-lg-4 form-group">
                  <label>Small Slider Image: <small>480px (w) X 432px (h)</small></label>
                  <input type="file" id="background_image_sm" name="background_image_sm" accept=".jpg, .png, .jpeg"
                    class="form-control" data-parsley-fileextension="jpg,png,jpeg" data-parsley-trigger="change"
                    required data-parsley-required-message="Background Image is required.">
                  @error('background_image')
                    {{ $message }}
                  @enderror
                  </span>
                </div>
                <div class="col-auto d-none" id="eImageSm">
                  <img class="img-thumbnail  mb-2" width="100" height="100" style="width: 320px; height:180px"
                    id="showImageSm" src="" data-src="">
                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-12 d-none" id="showStore">
            <table class="table table-bordered" id="tbl-address" style="width:100%">
              <thead>
                <th style="width:40%">Store Address</th>
                <th style="width:20%">Action</th>
              </thead>
              <tbody id="tableBody">
                <tr id="row0" class="tblrows" data-type="">
                  <td>
                    <input type="text" id="store_address0" name="store_address[]" class="form-control"
                      value="{{ old('store_address') }}" data-parsley-minlength="2"
                      data-parsley-minlength-message="You need to enter at least 2 characters"
                      data-parsley-trigger="change">
                  </td>
                  <td>
                    <input type="hidden" id="rowCode0" name="rowCode[]" value="-" readonly>
                    <button type="button" class="btn btn-sm btn-outline-danger del-button" data-row-id="row0"><i
                        class="fa fa-trash"></i></button>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2">
                    <button type="button" class="btn btn-outline-success add-address">Add</button>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="col-md-12 form-group">
            <button class="btn btn-primary" type="submit" id="submit"> Submit </button>
            <button type="button" class="btn btn-danger" onclick="window.location.reload();">Reset</button>
          </div>
      </div>
      </form>
    </div>
  </div>
  </div>
@endsection
@push('scripts')
  <script>
    var defaultSrc = "{{ asset('uploads/default-img.jpg') }}"
  </script>
  <script type="text/javascript" src="{{ asset('theme/js/summernote/dist/summernote-bs4.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/init_site/dynamic-slider/add.js?v=' . time()) }}"></script>
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
