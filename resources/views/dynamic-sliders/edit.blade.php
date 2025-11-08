@extends('template.master', ['pageTitle' => 'Dynamic Slider - Edit'])
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
              <li class="breadcrumb-item">Edit</li>
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
            <h5 class="mb-0" data-anchor="data-anchor">Edit</h5>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        <form id="inFrom" action="{{ url('/dynamic-sliders/update') }}" method="post" enctype="multipart/form-data"
          data-parsley-validate="">
          @csrf
          <div class="row">
            <input type="hidden" name="code" id="code" value="{{ $queryresult->code }}" />
            <div class="col-md-6 form-group">
              <label>Title : <span style="color:red">*</span></label>
              <input type="text" id="title" name="title" class="form-control" required
                value="{{ old('title') ? old('title') : $queryresult->title }}"
                data-parsley-required-message="Title is required" data-parsley-minlength="2"
                data-parsley-minlength-message="You need to enter at least 2 characters" data-parsley-trigger="change">
              <span class="text-danger">
                @error('title')
                  {{ $message }}
                @enderror
              </span>
            </div>
            <div class="col-md-6 form-group d-none">
              <label>Sub Title : </label>
              <input type="text" id="subTitle" name="subTitle" class="form-control"
                value="{{ old('subTitle') ? old('subTitle') : $queryresult->subTitle }}">
              <span class="text-danger">
                @error('subTitle')
                  {{ $message }}
                @enderror
              </span>
            </div>
            <div class="col-md-12">

              <h5>Home Slider Images (Large, Medimum & Small screens)</h5>
              <div><small>Accepts only JPG, JPEG, and PNG formats.</small></div>

              <div class="row mt-3">
                <div class="col-md-6 col-lg-4 form-group">
                  <label>Large Slider Image: <small>1280px (w) X 480px (h) </small></label>
                  <input type="file" id="background_image" name="background_image" accept=".jpg, .png, .jpeg"
                    class="form-control" data-parsley-fileextension="jpg,png,jpeg" data-parsley-trigger="change">
                  @error('background_image')
                    {{ $message }}
                  @enderror
                  </span>
                </div>
                <div class="col-auto {{ empty($queryresult->background_image) ? 'd-none' : '' }}" id="eImage">
                  <img class="img-thumbnail  mb-2" width="100" height="100" style="width: 320px; height:180px"
                    id="showImage" src="{{ url('uploads/slider-background/' . $queryresult->background_image) }}">
                  @if (!empty($queryresult->background_image))
                    <a class="btn btn-danger text-white" id="deleteImage"
                      onclick="deleteImage('{{ $queryresult->code }}','{{ $queryresult->background_image }}','background_image','deleteImage','showImage','lg');"><i
                        class="fa fa-trash"></i></a>
                  @endif
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-6 col-lg-4 form-group">
                  <label>Medimum Slider Image: <small>720px (w) X 648px (h)</small></label>
                  <input type="file" id="background_image_md" name="background_image_md" accept=".jpg, .png, .jpeg"
                    class="form-control" data-parsley-fileextension="jpg,png,jpeg" data-parsley-trigger="change">
                  @error('background_image_md')
                    {{ $message }}
                  @enderror
                  </span>
                </div>
                <div class="col-auto {{ empty($queryresult->background_image_md) ? 'd-none' : '' }}" id="eImageMd">
                  <img class="img-thumbnail  mb-2" width="100" height="100" style="width: 320px; height:180px"
                    id="showImageMd" src="{{ url('uploads/slider-background/' . $queryresult->background_image_md) }}">
                  @if (!empty($queryresult->background_image_md))
                    <a class="btn btn-danger text-white" id="deleteImageMd"
                      onclick="deleteImage('{{ $queryresult->code }}','{{ $queryresult->background_image_md }}','background_image_md','deleteImageMd','showImageMd','md');"><i
                        class="fa fa-trash"></i></a>
                  @endif
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-6 col-lg-4 form-group">
                  <label>Small Slider Image: <small>480px (w) X 432px (h)</small></label>
                  <input type="file" id="background_image_sm" name="background_image_sm" accept=".jpg, .png, .jpeg"
                    class="form-control" data-parsley-fileextension="jpg,png,jpeg" data-parsley-trigger="change">
                  @error('background_image_sm')
                    {{ $message }}
                  @enderror
                  </span>
                </div>
                <div class="col-auto {{ empty($queryresult->background_image_sm) ? 'd-none' : '' }}" id="eImageSm">
                  <img class="img-thumbnail  mb-2" width="100" height="100" style="width: 320px; height:180px"
                    id="showImageSm" src="{{ url('uploads/slider-background/' . $queryresult->background_image_sm) }}">
                  @if (!empty($queryresult->background_image_sm))
                    <a class="btn btn-danger text-white" id="deleteImageSm"
                      onclick="deleteImage('{{ $queryresult->code }}','{{ $queryresult->background_image_sm }}','background_image_sm','deleteImageSm','showImageSm','sm');"><i
                        class="fa fa-trash"></i></a>
                  @endif
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
                  @foreach ($lineentries as $index => $data)
                    <tr id="row{{ $index }}" class="tblrows" data-type="">
                      <input type="hidden" id="addr_code{{ $index }}" name="addr_code[]"
                        value="{{ $data->code }}" />
                      <td>
                        <input type="text" id="store_address{{ $index }}" name="store_address[]"
                          class="form-control"
                          value="{{ old('store_address') ? old('store_address') : $data->store_address }}"
                          data-parsley-minlength="2"
                          data-parsley-minlength-message="You need to enter at least 2 characters"
                          data-parsley-trigger="change">
                      </td>
                      <td>
                        <input type="hidden" id="rowCode{{ $index }}" name="rowCode[]" value="-"
                          readonly>
                        <button type="button" class="btn btn-sm btn-outline-danger del-button"
                          data-value="{{ $data->code }}" data-row-id="row{{ $index }}"><i
                            class="fa fa-trash"></i></button>
                      </td>
                    </tr>
                  @endforeach
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
              <button class="btn btn-primary" type="submit" id="submit"> Update </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script>
    var image_code = "{{ $queryresult->code }}";
    var image_value = "{{ $queryresult->background_image }}";
  </script>
  <script type="text/javascript" src="{{ asset('theme/js/summernote/dist/summernote-bs4.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/init_site/dynamic-slider/edit.js?v=' . time()) }}"></script>

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
