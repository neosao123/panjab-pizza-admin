@extends('template.master', ['pageTitle' => 'Store Location'])
@push('styles')
  <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
  <style>
    #myMap {
      height: 300px;
      width: 680px;
    }
  </style>
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Store Location</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/storelocation/list') }}">Store Location</a></li>
              <li class="breadcrumb-item">Add</li>
            </ol> 
          </nav>
        </div>
      </div>
      <?php if ($viewRights == 1) { ?>
      <div class="col-7 align-self-center ">
        <a href="{{ url('/storelocation/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
        <form action="{{ url('/storelocation/store') }}" method="post" enctype="multipart/form-data"
          data-parsley-validate="">
          @csrf
          <div class="row">
            <div class="col-md-6 form-group">
              <label>Name : <span style="color:red">*</span></label>
              <input type="text" id="name" name="name" class="form-control mb-2" required
                value="{{ old('name') }}" data-parsley-required-message="Name is required" maxlength='150'
                data-parsley-minlength="3" data-parsley-minlength-message="You need to enter at least 3 characters"
                data-parsley-trigger="change">
              <span class="text-danger">
                @error('name')
                  <span class="text-danger">
                    {{ $message }}
                  </span>
                @enderror
              </span>
            </div>
             <div class="col-md-6 form-group">
              <label>Pickup Mobile Number : <span style="color:red">*</span></label>
              <input type="text" id="pickupNumber" name="pickupNumber" class="form-control mb-2" >
              <span class="text-danger">
                @error('pickupNumber')
                  <span class="text-danger">
                    {{ $message }}
                  </span>
                @enderror
              </span>
            </div>
            <div class="col-md-6 form-group">
              <label>Tax (Province) : <span style="color:red">*</span></label>
              <select id="tax_province_id" name="tax_province_id" autocomplete="off" class="form-control mb-2" required>
                @foreach ($provinces as $item)
                  <option value="{{ $item->id }}" data-val="{{ $item->timezone }}">
                    {{ $item->tax_percent . '% (' . $item->province_state . ')' }}</option>
                @endforeach
              </select>
              <span class="text-danger">
                @error('tax_province_id')
                  <span class="text-danger">
                    {{ $message }}
                  </span>
                @enderror
              </span>
            </div>
            <div class="col-md-6 form-group">
              <label>Time Zone : <span style="color:red">*</span></label>
              <x-timezone-select name="timezone" :selected="old('timezone')" />
            </div>
            <div class="col-md-6 form-group">
              <label>City : <span style="color:red">*</span></label>
              <input list="cities" id="city" name="city" autocomplete="off" class="form-control mb-2" required
                value="{{ old('city') }}" data-parsley-required-message="City is required" maxlength='50'
                data-parsley-minlength="3" data-parsley-minlength-message="You need to enter at least 3 characters"
                data-parsley-trigger="change">
              <datalist id="cities">
                @foreach ($prevCities as $item)
                  <option value="{{ $item->city }}"></option>
                @endforeach
              </datalist>
              <span class="text-danger">
                @error('city')
                  <span class="text-danger">
                    {{ $message }}
                  </span>
                @enderror
              </span>
            </div>
            <div class="col-md-12 mb-3">
              <label for="storeAddress"> Store Address : <b style="color:red">*</b></label>
              <textarea id="storeAddress" name="storeAddress" class="form-control mb-2" required=""
                data-parsley-required-message="Store Address is required">{{ old('storeAddress') }}</textarea>
              @error('storeAddress')
                <span class="text-danger">
                  {{ $message }}
                </span>
              @enderror
            </div>

            <div class="col-md-12 mb-3">
              <div id="myMap">
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="latitude"> Latitude : <b style="color:red">*</b></label>
              <input type="text" id="latitude" name="latitude" class="form-control" value="{{ old('latitude') }}"
                step="any" required data-parsley-required-message="Latitude is required">
            </div>
            <div class="col-md-6 mb-3">
              <label for="longitude"> Longitude : <b style="color:red">*</b></label>
              <input type="text" id="longitude" name="longitude" class="form-control"
                value="{{ old('longitude') }}" step="any" required
                data-parsley-required-message="Longitude is required">
            </div>
            <div class="col-md-6 form-group" id="weekdaysCheckbox">
              <div class="mb-3 border-bottom">
                <label class="form-label mb-2">Select Weekdays Times <span class="text-danger">*</span></label>
              </div>

              <div class="row gx-3 mb-2">
                <label class="form-col-label col-3">Start Time <span class="text-danger">*</span></label>
                <div class="col-9">
                  <input class="form-control" value="{{ old('wd_start_time') }}" name="wd_start_time" type="text"
                    placeholder="Select Start Time.." id="wd_start_time" required
                    data-parsley-required-message="The start time is required">
                </div>
              </div>
              <div class="row gx-3 mb-2">
                <label class="form-col-label col-3">End Time <span class="text-danger">*</span></label>
                <div class="col-9">
                  <input class="form-control" value="{{ old('wd_end_time') }}" name="wd_end_time" type="text"
                    placeholder="Select End Time.." id="wd_end_time" required
                    data-parsley-required-message="The end time is required">
                </div>
              </div>
            </div>

            <div class="col-md-6 form-group">
              <div class="mb-3 border-bottom">
                <label class="form-label mb-2">Select Weekend Times <span class="text-danger">*</span></label>
              </div>

              <div class="row gx-3 mb-2">
                <label class="form-col-label col-3">Start Time <span class="text-danger">*</span></label>
                <div class="col-9">
                  <input class="form-control" value="{{ old('we_start_time') }}" name="we_start_time" type="text"
                    placeholder="Select Start Time.." id="we_start_time" required
                    data-parsley-required-message="The start time is required">
                </div>
              </div>
              <div class="row gx-3 mb-2">
                <label class="form-col-label col-3">End Time <span class="text-danger">*</span></label>
                <div class="col-9">
                  <input class="form-control" value="{{ old('we_end_time') }}" name="we_end_time" type="text"
                    placeholder="Select End Time.." id="we_end_time" required
                    data-parsley-required-message="The end time is required">
                </div>
              </div>
            </div>

            <div class="col-md-12 form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1"
                  checked>
                <label class="custom-control-label" for="isActive"> Status</label>
              </div>
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
  <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBGE-XRIa2IwnWbdbmEPM-eCmGp8AnnOik"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"
    integrity="sha512-K/oyQtMXpxI4+K0W7H25UopjM8pzq0yrVdFdG21Fh5dBe91I40pDd9A4lzNlHPHBIP2cwZuoxaUSX0GJSObvGA=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/init_site/storelocation/add.js?v=' . time()) }}"></script>


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
