@extends('template.master', ['pageTitle' => 'SMS Template Add'])
@push('styles')
  <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
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
    .char-counter {
      font-size: 12px;
      color: #6c757d;
      margin-top: 5px;
    }
  </style>
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">SMS Template</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/sms-templates/list') }}">SMS Templates</a></li>
              <li class="breadcrumb-item">Add</li>
            </ol>
          </nav>
        </div>
      </div>
      @if($viewRights == 1)
      <div class="col-7 align-self-center ">
        <a href="{{ url('/sms-templates/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
      </div>
      @endif
    </div>
  </div>
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-12">
            <h5 class="mb-0" data-anchor="data-anchor">Add SMS Template</h5>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        <form id="inFrom" action="{{ url('/sms-templates/store') }}" method="post" data-parsley-validate="">
          @csrf
          <div class="row">
            <div class="col-md-12 form-group">
              <label>Title: <span style="color:red">*</span></label>
              <input type="text" id="title" name="title" class="form-control" required
                value="{{ old('title') }}" data-parsley-required-message="Title is required"
                data-parsley-minlength="2" data-parsley-minlength-message="You need to enter at least 2 characters"
                data-parsley-maxlength="255" data-parsley-maxlength-message="Maximum 255 characters allowed"
                data-parsley-trigger="change">
              <span class="text-danger">
                @error('title')
                  {{ $message }}
                @enderror
              </span>
            </div>

            <div class="col-md-12 form-group">
              <label>Template: <span style="color:red">*</span></label>
              <textarea id="template" name="template" class="form-control" rows="6" required
                data-parsley-required-message="Template is required"
                data-parsley-minlength="10"
                data-parsley-minlength-message="You need to enter at least 10 characters"
                data-parsley-trigger="change">{{ old('template') }}</textarea>
              <div class="char-counter">
                <span id="charCount">0</span> characters
              </div>
              <span class="text-danger">
                @error('template')
                  {{ $message }}
                @enderror
              </span>
            </div>
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
  <script type="text/javascript" src="{{ asset('theme/init_site/sms-templates/add.js?v=' . time()) }}"></script>
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
