@extends('template.master', ['pageTitle'=>"Profile Update"])
@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
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
                        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Edit Profile</a></li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-7 align-self-center">
            <a href="{{ url('dashboard') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
        </div>
    </div>
</div>
<div class="container-fluid col-md-6">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-sm-10">Profile Edit</h5>
                </div>

            </div>
        </div>
        <div class="card-body pt-3 pb-2">
            <form id="profileform" method="post" action="{{ url('profile-update/'.$details->code)}}" enctype="multipart/form-data" data-parsley-validate="">
                @csrf
                <input type="hidden" value="" name="code">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="mb-3">
                            <input class="form-control" type="hidden" name="code" id="form-wizard-progress-wizard-contactno" value="{{ $details->code}}" readonly />
                            <label class="form-label" for="form-wizard-progress-wizard-fullanme">First Name : <b style="color:red">*</b></label>
                            <input class="form-control" type="text" name="firstname" placeholder="Enter First Name" value="{{ $details->firstname}}" onkeypress="return ValidateAlpha(event)" required="" data-parsley-required-message="First Name is required" />
                            <span class="text-danger text-center">{{ $errors->first('firstname') }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label" for="form-wizard-progress-wizard-fullanme">Middle Name : </label>
                            <input class="form-control" type="text" name="middlename" placeholder="Enter Middle Name" value="{{ $details->middlename}}" onkeypress="return ValidateAlpha(event)" />
                            <span class="text-danger text-center">{{ $errors->first('middlename') }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label" for="form-wizard-progress-wizard-fullanme">Last Name : <b style="color:red">*</b></label>
                            <input class="form-control" type="text" name="lastname" placeholder="Enter Last Name" value="{{ $details->lastname}}" onkeypress="return ValidateAlpha(event)" required="" data-parsley-required-message="Last Name is required" />
                            <span class="text-danger text-center">{{ $errors->first('lastname') }}</span>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label" for="form-wizard-progress-wizard-name">Username : <b style="color:red">*</b></label>
                            <input class="form-control" type="text" name="name" placeholder="Enter Name" id="form-wizard-progress-wizard-name" value="{{ $details->username}}" onkeypress="return ValidateAlpha(event)" required="" data-parsley-required-message="Name is required" />
                            <span class="text-danger text-center">{{ $errors->first('name') }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label" for="form-wizard-progress-wizard-email">Email : <b style="color:red">*</b></label>
                            <input class="form-control" type="email" name="userEmail" placeholder="Enter Email" id="form-wizard-progress-wizard-email" value="{{ $details->userEmail}}" data-parsley-type="email" required="" data-parsley-required-message="Email id is required" maxlength="50" />
                            <span class="text-danger text-center">{{ $errors->first('userEmail') }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label>Contact Number<span style="color:red">*</span></label>
                            <input type="number" id="mobilenumber" name="mobilenumber" value="{{ $details->mobile }}" maxlength="10" class="form-control" onkeypress="if(this.value.length==10) return false;" required data-parsley-required-message="Contact number is required" data-parsley-minlength="10" data-parsley-minlength-message="Mobile number must be 10 digits." data-parsley-trigger="change">
                            <span class="text-danger text-center">
                                @error('mobilenumber')
                                {{ $message }}
                                @enderror
                            </span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label" for="form-wizard-progress-wizard-password">Password</label>
                            <input class="form-control" name="password" type="password" autocomplete="on" id="password" placeholder="Password" maxlength='16' data-parsley-minlength="6" data-parsley-minlength-message="Password must be 6 characters long." data-parsley-trigger="change" />
                            <span class="text-danger text-center">{{ $errors->first('password') }}</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label" for="form-wizard-progress-wizard-cpassword">Confirm Password</label>
                            <input class="form-control" name="password_confirmation" type="password" autocomplete="on" id="password_confirmation" placeholder="Confirm Password" maxlength='16' data-parsley-minlength="6" data-parsley-minlength-message="Confirm Password must be 6 characters long." data-parsley-trigger="change" />
                            <span class="text-danger text-center">{{ $errors->first('password') }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="mb-3">
                            <label class="form-label" for="form-wizard-progress-wizard-profilephoto">Profile Photo</label>
                            <input type="file" id="file" class="form-control " name="profilephoto" accept=".jpg, .jpeg, .png">
                        </div>
                    </div>

                    @if(!empty($details->profilePhoto))
                    <div class="col-sm-2" id="eImage">
                        <img class="img-radius" id="profile_image" src="{{ asset('uploads/profile/'.$details->profilePhoto)."?v=".time()}}" height="80" width="80" accept=".jpg,.png,.jpeg" />
                    </div>
                    @endif
                     <div class="col-md-3 mb-3 d-none" id="eDisImage">
                        <img class="img-thumbnail mb-2" width="100" height="100" id="showImage" src="" data-src="">
                    </div>
                </div>
                <div class="mt-5 mb-5 text-center">
                    <button type="submit" class="btn btn-primary btnsubmit">Update Profile</button>
                </div>

            </form>
        </div>
    </div>
</div>
</div>
</div>
@endsection
@push('scripts')
<script type="text/javascript" src="{{ asset('theme/js/toastr.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/init_site/profile/profile.js?v=' . time()) }}"></script>
<script type="text/javascript">
          notification = @json(session()->pull("status"));
		  function message() {
		  Swal.fire({
			  icon: 'success',
			  text: notification.message, 
			});
		  }
		  window.onload = message;
		  @php 
			  session()->forget('status'); 
		   @endphp
		 </script>

@endpush