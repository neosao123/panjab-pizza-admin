@extends('template.master', ['pageTitle'=>"Users View"])
@push('styles')
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">User View</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/users/list') }}">User</a></li>
                        <li class="breadcrumb-item">View</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-7 align-self-center">
            <a href="{{ url('/users/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-sm-10">
                    <h5 class="mb-0" data-anchor="data-anchor">User View</h5>
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
            <form id="addressform" action="#" method="post" enctype="multipart/form-data" data-parsley-validate="">
                @csrf
                <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>User Name</label>
                        <input type="text" id="username" name="username" class="form-control-line" readonly value="{{ $queryresult->username }}">

                    </div>
                    <div class="col-sm-6 form-group">
                        <label>Role </label>

                        <input type="text" id="role" name="role" class="form-control-line" value="{{ $queryresult->roleName }}" readonly>


                    </div>
					 <div class="col-sm-4 form-group " id="storeLoc" @if($queryresult->role != "R_3") style="display:none;" @else style="display:block;"@endif>
							<label>Store Location:</label> 
							<div class="form-group">
							    <input type="text"  name="storeLoc" class="form-control-line" value="{{$queryresult->storeLocation}}" readonly>
							  
							</div> 
					  </div>
                    <div class="col-sm-4 form-group">
                        <label>First Name</label>
                        <input type="text" id="firstname" name="firstname" class="form-control-line" value="{{ $queryresult->firstname }}" readonly>

                    </div>
                    <div class="col-sm-4 form-group">
                        <label>Middle Name</label>
                        <input type="text" id="middlename" name="middlename" class="form-control-line" value="{{ $queryresult->middlename }}" readonly>

                    </div>
                    <div class="col-sm-4 form-group">
                        <label>Last Name</label>
                        <input type="text" id="lastname" name="lastname" class="form-control-line" value="{{ $queryresult->lastname }}" readonly>

                    </div>
                    <div class="col-sm-6 form-group">
                        <label>Contact Number </label>
                        <input type="text" id="mobilenumber" name="mobilenumber" value="{{ $queryresult->mobile }}" maxlength="12" class="form-control-line" readonly>

                    </div>
                    <div class="col-sm-6 form-group">
                        <label>Email</label>
                        <input type="email" id="email" name="email" value="{{ $queryresult->userEmail }}" class="form-control-line" readonly>

                    </div>

                    @if(!empty($queryresult->profilePhoto))
						
                    <div class="col-md-3 mb-3" id="eImage">
					     <label>Profile Photo</label>
						 <div class="form-group">
                        <img class="img-thumbnail mb-2" width="100" height="100" id="showProfileImg" src="{{ url('uploads/profile/'.$queryresult->profilePhoto)}}" data-src="">
                         </div>
                    </div>
                    @endif
                    <div class="col-sm-12 form-group">
                        <label>Status</label>
                        <div class="custom-control custom-checkbox">
                            @if($queryresult->isActive == 1)
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