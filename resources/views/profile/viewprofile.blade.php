@extends('template.master', ['pageTitle'=>"Profile View"])
@push('styles')
<link rel="stylesheet" href="{{asset('theme/css/parsely.css') }}">
<link rel="stylesheet" href="{{ asset('theme/css/sweetalert2.min.css') }}">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Home</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">View Profile</a></li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-7 align-self-center">
        </div>
    </div>
</div>
<div class="container-fluid col-md-6">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-sm-10">Profile View</h5>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="#" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-sm-12 form-group">
                        <label class="form-label" for="form-wizard-progress-wizard-fullanme">First Name : </label>
                        <input class="form-control" type="text" name="firstname" value="{{ $details->firstname}}" readonly />

                    </div>
                    <div class="col-sm-12 form-group">
                        <label class="form-label">Middle Name : </label>
                        <input class="form-control" type="text" name="middlename" value="{{ $details->middlename}}" readonly />

                    </div>
                    <div class="col-sm-12 form-group">
                        <label class="form-label">Last Name : </label>
                        <input class="form-control" type="text" name="lastname" value="{{ $details->lastname}}" readonly />

                    </div>
                    <div class="col-sm-12 form-group">
                        <label class="form-label" for="form-wizard-progress-wizard-name">User Name : </label>
                        <input class="form-control" type="text" name="name" placeholder="Enter Name" id="form-wizard-progress-wizard-name" value="{{ $details->username}}" readonly />
                    </div>
                    <div class="col-sm-12 form-group">
                        <label class="form-label" for="form-wizard-progress-wizard-email">Email : </label>
                        <input class="form-control" type="email" name="userEmail" placeholder="Enter Email" id="form-wizard-progress-wizard-email" value="{{ $details->userEmail}}" readonly />

                    </div>
                    <div class="col-sm-12 form-group">
                        <label class="form-label">Contact Number: </label>
                        <input class="form-control" type="text" name="contact" value="{{ $details->mobile}}" readonly />

                    </div>
                    <div class="col-sm-12 form-group">
                        <label class="form-label" for="form-wizard-photo">Profile Photo: </label>
                        @if(!empty($details->profilePhoto))
                        <div class="col-sm-2">
                            <img class="img-radius" id="profile_image" src="{{ asset('uploads/profile/'.$details->profilePhoto)."?v=".time()}}" height="80" width="80" accept=".jpg,.png,.jpeg" />
                        </div>
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

<script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
@endpush