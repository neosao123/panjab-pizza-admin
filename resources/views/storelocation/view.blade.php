@extends('template.master', ['pageTitle' => 'Store Location View'])
@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
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
                        <li class="breadcrumb-item">View</li>
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
                    <h5 class="mb-0" data-anchor="data-anchor">View</h5>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label> Name :</label>
                        <input type="text" id="name" name="name" class="form-control-line" value="{{ $queryresult->storeLocation }}" readonly>
                    </div>
                     <div class="col-md-6 form-group">
                            <label>Tax (Province) :</label>
                            
							<input id="tax_province_id" name="tax_province_id" autocomplete="off" class="form-control-line mb-2"
                            value="{{$queryresult->tax_percent }}% ({{$queryresult->province_state}})" readonly>
						
                      </div>
					 <div class="col-md-6 form-group">
						  <label>Time Zone : </label>						 
						  <input id="timezone" name="timezone" autocomplete="off" class="form-control-line mb-2"
							value="{{$queryresult->timezone }}" readonly>
					 </div>
                    <div class="col-md-12 form-group">
                        <label> Store Address :</label>
                        <input id="storeAddress" name="storeAddress" class="form-control-line" value="{{ $queryresult->storeAddress }}" readonly>
                    </div>
                    <div class="col-md-12 mb-3">
                        <div id="myMap">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="latitude"> Latitude : <b style="color:red">*</b></label>
                        <input type="text" id="latitude" name="latitude" class="form-control" value="{{ $queryresult->latitude }}" step="any" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="longitude"> Longitude : <b style="color:red">*</b></label>
                        <input type="text" id="longitude" name="longitude" class="form-control" value="{{ $queryresult->longitude }}" step="any" readonly>
                    </div>


                    <div class="col-md-6 form-group" id="weekdaysCheckbox">
                        <div class="mb-3 border-bottom">
                            <label class="form-label mb-2">Select Weekdays Times <span class="text-danger">*</span></label>
                        </div>

                        <div class="row gx-3 mb-2">
                            <label class="form-col-label col-3">Start Time <span class="text-danger">*</span></label>
                            <div class="col-9">
                                <input class="form-control-line" value="{{ $queryresult->weekdays_start_time }}" name="wd_start_time" type="text" placeholder="Select Start Time.." id="wd_start_time" required data-parsley-required-message="The start time is required" readonly>
                            </div>
                        </div>
                        <div class="row gx-3 mb-2">
                            <label class="form-col-label col-3">End Time <span class="text-danger">*</span></label>
                            <div class="col-9">
                                <input class="form-control-line" value="{{ $queryresult->weekdays_end_time }}" name="wd_end_time" type="text" placeholder="Select End Time.." id="wd_end_time" required data-parsley-required-message="The end time is required" readonly>
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
                                <input class="form-control-line" value="{{ $queryresult->weekend_start_time }}" name="we_start_time" type="text" placeholder="Select Start Time.." id="we_start_time" required data-parsley-required-message="The start time is required" readonly>
                            </div>
                        </div>
                        <div class="row gx-3 mb-2">
                            <label class="form-col-label col-3">End Time <span class="text-danger">*</span></label>
                            <div class="col-9">
                                <input class="form-control-line" value="{{ $queryresult->weekend_end_time }}" name="we_end_time" type="text" placeholder="Select End Time.." id="we_end_time" required data-parsley-required-message="The end time is required" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 form-group">
                        <label>Status</label>
                        <div class="custom-control custom-checkbox">
                            @if ($queryresult->isActive == 1)
                            <div class="badge badge-success m-1">Active</div>
                            @else
                            <div class="badge badge-warning m-1">Inactive</div>
                            @endif

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBGE-XRIa2IwnWbdbmEPM-eCmGp8AnnOik"></script>
<!-- <script type="text/javascript" src="{{ asset('theme/init_site/storelocation/edit.js?v=' . time()) }}"></script> -->

<script>
    let map, infoWindow, marker, myLatlng, geocoder;
    let lati, lngi;
    let latitudeControl = document.querySelector("#latitude");
    let longitudeControl = document.querySelector("#longitude");

    function handlePermission() {
        navigator.permissions.query({
            name: "geolocation"
        }).then(function(result) {
            if (result.state == "granted") {
                report(result.state);
                // window.location.reload();
            } else if (result.state == "prompt") {
                report(result.state);
                //navigator.geolocation.getCurrentPosition(revealPosition, positionDenied, geoSettings);
            } else if (result.state == "denied") {
                report(result.state);
                toastr.error(
                    "Please allow location permission to show the map",
                    "Location?"
                );
            }
            result.onchange = function() {
                report(result.state);
            };
        });
    }

    function report(state) {
        console.log("Permission " + state);
    }

    let geolocationOptions = {
        enableHighAccuracy: true,
        maximumAge: 10000,
        timeout: 5000,
    };

    const successCallback = (geolocation) => {
        if (latitudeControl.value != "" && longitudeControl.value != "") {
            myLatlng = {
                lat: parseFloat(latitudeControl.value),
                lng: parseFloat(longitudeControl.value),
            };
        } else {
            myLatlng = {
                lat: parseFloat(geolocation.coords.latitude),
                lng: parseFloat(geolocation.coords.longitude),
            };
        }
        console.log("My Location Is ", myLatlng);
        initMap();
    };

    const errorCallback = (error) => {
        console.log(error);
    };

    function initMap() {
        map = new google.maps.Map(document.getElementById("myMap"), {
            center: myLatlng,
            zoom: 16,
        });

        infoWindow = new google.maps.InfoWindow();

        geocoder = new google.maps.Geocoder();

        marker = new google.maps.Marker({
            map: map,
            position: myLatlng,
            draggable: false,
        });
    }

    $(function() {
        setTimeout(() => {
            navigator.geolocation.getCurrentPosition(
                successCallback,
                errorCallback,
                geolocationOptions
            );
        }, 1000);
    });
</script>
@endpush