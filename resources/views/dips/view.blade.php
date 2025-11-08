@extends('template.master', ['pageTitle' => 'Dips View'])
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Dips View</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/dips/list') }}">Dips</a></li>
                        <li class="breadcrumb-item">View</li>
                    </ol>
                </nav>
            </div>
        </div>
        <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/dips/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
			@if($queryresult)
            <form >               
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>Name :</label>
                        <input type="text" id="dips" name="dips" class="form-control-line" readonly value="{{ $queryresult->dips }}" >
                       
                    </div>
                    
                    <div class="col-sm-6 form-group">
                        <label>Price :</label>
                        <input type="text" id="price" name="price"  class="form-control-line" value="{{ $queryresult->price }}" readonly>
                       
                    </div>
                   
                    @if (!empty($queryresult->dipsImage)) 
                    <div class="col-md-3 mb-3" id="eImage">
				        <label>Dips Image :</label>
                        <img class="img-thumbnail mb-2" width="100" height="100" id="showProfileImg" src="{{ url('uploads/dips/' . $queryresult->dipsImage). '?v=' . time() }}" data-src=""> 
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
			@endif
        </div>
    </div>
</div>
@endsection
