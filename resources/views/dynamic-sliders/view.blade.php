@extends('template.master', ['pageTitle' => 'Dynamic Slider - View'])
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
              <li class="breadcrumb-item">View</li>
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
            <h5 class="mb-0" data-anchor="data-anchor">View</h5>
          </div>
        </div>
      </div>
      <div class="card-body">

        <div class="row">
          <div class="col-md-12 form-group">
            <label>Title : </label>
            <input type="text" id="title" name="title" class="form-control-line"
              value="{{ $queryresult->title }}" readonly>
          </div>
          <div class="col-md-12 form-group d-none">
            <label>Sub Title : </label>
            <input type="text" id="subTitle" name="subTitle" class="form-control-line"
              value="{{ $queryresult->subTitle }}" readonly>
          </div>

          @if (!empty($queryresult->background_image))
            <div class="col-md-6 col-lg-4 form-group">
              <label>Large Slider Image: <small>1280px (w) X 480px (h) </small></label>
              <div id="eImage">
                <img class="img-thumbnail  mb-2" width="100" height="100" style="width: 320px; height:180px"
                  id="showImage" src="{{ url('uploads/slider-background/' . $queryresult->background_image) }}">
              </div>
            </div>
          @endif

          @if (!empty($queryresult->background_image_md))
            <div class="col-md-6 col-lg-4 form-group">
              <label>Medimum Slider Image: <small>720px (w) X 648px (h)</small></label>
              <div id="eImage">
                <img class="img-thumbnail  mb-2" width="100" height="100" style="width: 320px; height:180px"
                  id="showImage" src="{{ url('uploads/slider-background/' . $queryresult->background_image_md) }}">
              </div>
            </div>
          @endif

          @if (!empty($queryresult->background_image_sm))
            <div class="col-md-6 col-lg-4 form-group">
              <label>Small Slider Image: <small>480px (w) X 432px (h)</small></label>
              <div id="eImage">
                <img class="img-thumbnail  mb-2" width="100" height="100" style="width: 320px; height:180px"
                  id="showImage" src="{{ url('uploads/slider-background/' . $queryresult->background_image_sm) }}">
              </div>
            </div>
          @endif

          <div class="col-sm-12 d-none" id="showStore">
            <table class="table table-bordered" id="tbl-address" style="width:100%">
              <thead>
                <th style="width:40%">Store Address</th>
              </thead>
              <tbody id="tableBody">
                @foreach ($lineentries as $index => $data)
                  <tr id="row{{ $index }}" class="tblrows" data-type="">
                    <input type="hidden" id="addr_code{{ $index }}" name="addr_code[]"
                      value="{{ $data->code }}" />
                    <td>
                      <input type="text" id="store_address{{ $index }}" name="store_address[]"
                        class="form-control-line" required value="{{ $data->store_address }}" readonly>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script type="text/javascript" src="{{ asset('theme/js/summernote/dist/summernote-bs4.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
@endpush
