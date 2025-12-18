@extends('template.master', ['pageTitle'=>"Users Edit"])
@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">User</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/users/list') }}">User</a></li>
                        <li class="breadcrumb-item">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
        <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center">
                <a href="{{ url('/users/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
            </div>
        <?php } ?>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-sm-10">
                    <h5 class="mb-0" data-anchor="data-anchor">User Edit</h5>
                </div>
                <div class="col-sm-2">

                </div>
            </div>
        </div>
        <div class="card-body">
            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif
            <form id="addressform" action="{{ url('/users/update') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
                @csrf
                <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>User Name <span style="color:red">*</span></label>
                        <input type="text" id="username" name="username" class="form-control" required value="{{ $queryresult->username }}" data-parsley-required-message="User name is required" onkeypress="return ValidateAlpha(event)">
                        <span class="text-danger">
                            @error('username')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label>Role: <span style="color:red">*</span></label>
                        <div class="form-group">
                            <select class="form-control custom-select" style="width: 100%; height:36px;" name="role" id="role" required data-parsley-required-message="User role is required">                               
                                @foreach ($usersrole as $item)
                                <option value="{{ $item->code }}" {{ $item->code == $queryresult->role ? 'selected' : '' }}>{{ $item->role }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
					 <div class="col-sm-4 form-group">
							<label>Store Location:  <span style="color:red" id="storeLoc">*</span></label> 
							<div class="form-group">
							   <select class="select2 form-control custom-select" style="width: 100%;" name="storeLocation" id="storeLocation" required data-parsley-required-message="Store location is required.">
									<option value="{{$queryresult->storeLocationCode}}">{{$queryresult->storeLocation}}</option> 
							   </select>
							</div> 
					   </div>
                    <div class="col-sm-4 form-group">
                        <label>First Name <span style="color:red">*</span></label>
                        <input type="text" id="firstname" name="firstname" class="form-control" required value="{{ $queryresult->firstname }}" data-parsley-required-message="First name is required" onkeypress="return ValidateAlpha(event)">
                        <span class="text-danger">
                            @error('firstname')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="col-sm-4 form-group">
                        <label>Middle Name</label>
                        <input type="text" id="middlename" name="middlename" class="form-control" value="{{ $queryresult->middlename }}" onkeypress="return ValidateAlpha(event)">
                        <span class="text-danger">
                            @error('middlename')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="col-sm-4 form-group">
                        <label>Last Name <span style="color:red">*</span></label>
                        <input type="text" id="lastname" name="lastname" class="form-control" required value="{{ $queryresult->lastname }}" data-parsley-required-message="Last name is required" onkeypress="return ValidateAlpha(event)">
                        <span class="text-danger">
                            @error('lastname')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label>Mobile Number<span style="color:red">*</span></label>
                        <input type="text" id="mobilenumber" name="mobilenumber" value="{{ $queryresult->mobile }}" class="form-control" required data-parsley-required-message="Mobile number is required" onkeypress="return isNumberKey(event)" >
                        <span class="text-danger">
                            @error('mobilenumber')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label>Email<span style="color:red">*</span></label>
                        <input type="email" id="email" name="email" value="{{ $queryresult->userEmail }}" class="form-control" required data-parsley-required-message="Email is required" pattern="^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$" data-parsley-type="email" data-parsley-type-message="Valid Email is required">
                        <span class="text-danger">
                            @error('email')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label"> Password</label>
                        <input class="form-control" name="password" type="password" id="password" maxlength='16' data-parsley-minlength="6" data-parsley-minlength-message="Password must be 6 characters long." data-parsley-trigger="change" onchange="checkPasswordMatch();" />
                        <span class="text-danger text-center">
                            @error('password')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">Confirm Password</label>
                        <input class="form-control" name="password_confirmation" type="password" id="password_confirmation" maxlength='16' data-parsley-minlength="6" data-parsley-minlength-message="Confirm Password must be 6 characters long." data-parsley-trigger="change" onchange="checkPasswordMatch();" />
                        <span class="text-danger text-center">
                            @error('password')
                            {{ $message }}
                            @enderror
                        </span>
                        <div id="CheckPasswordMatch" style="color:#e66060;"></div>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label" for="form-wizard-progress-wizard-profilephoto">Profile Photo</label>
                        <input type="file" id="file" class="form-control " name="profilephoto" accept=".jpg, .jpeg, .png">

                    </div>
                    @if(!empty($queryresult->profilePhoto))
                    <div class="col-md-3 mb-3" id="eImage">
                        <img class="img-thumbnail mb-2" width="100" height="100" id="showProfileImg" src="{{ url('uploads/profile/'.$queryresult->profilePhoto)."?v=".time()}}" data-src="">
                        <a class="btn btn-danger" onclick="deleteImage('{{$queryresult->code}}','{{$queryresult->profilePhoto}}');"><i class="fa fa-trash"></i></a>
                    </div>
                    @endif
                    <div class="col-md-3 mb-3 d-none" id="eDisImage">
                        <img class="img-thumbnail mb-2" width="100" height="100" id="showImage" src="" data-src="">
                    </div>
                    <div class="col-sm-12 form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1" {{ $queryresult->isActive == 1 ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isActive"> Status</label>
                        </div>
                    </div>

                    <div class="col-sm-12 form-group">
                        <button class="btn btn-primary" id="submit"> Update </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/init_site/users/edit.js?v=' . time()) }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.full.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.init.js') }}"></script>

@if(session('error'))
<script>
    $(document).ready(function() {
        'use strict';
        setTimeout(() => {
            $(".alert").remove();
        }, 5000);

        $('#tradeform').submit(function(e) {
            debugger
            //check atleat 1 checkbox is checked
            if (!$('.checkSelect').is(':checked')) {
                //prevent the default form submit if it is not checked
                e.preventDefault();
            }
        });


    });
</script>
@endif
@endpush