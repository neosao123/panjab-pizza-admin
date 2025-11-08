@extends('template.master', ['pageTitle' => 'Setting View'])
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Setting</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/setting/list') }}">Setting</a></li>
                        <li class="breadcrumb-item">View</li>
                    </ol>
                </nav>
            </div>
        </div>
        
            <div class="col-7 align-self-center ">
                <a href="{{ url('/setting/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
            </div>
        
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
		@if ($setting)
          <form>
            <div class="row">
              <div class="col-sm-12 form-group">
                <label for="settingName">Setting Name</label>
                <input type="text" id="settingName" name="settingName" class="form-control-line" readonly value="{{ $setting->settingName }}">
               
              </div>
              <div class="col-sm-12 form-group">
                <label for="settingValue">Setting Value</label>
                <input type="text" id="settingValue" name="settingValue" class="form-control-line" readonly value="{{ $setting->settingValue }}">
               
              </div>			 
              
            </div>
          </form>
        @endif
        </div>
    </div>
</div>
@endsection
