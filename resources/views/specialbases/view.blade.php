@extends('template.master', ['pageTitle' => 'Special Base View'])
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Special Base View</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/specialbases/list') }}">Special Base</a></li>
                        <li class="breadcrumb-item">View</li>
                    </ol>
                </nav>
            </div>
        </div>
        <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/specialbases/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
		@if($queryresult)
            <form >
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>Name : </label>
                        <input type="text" id="specialbase" name="specialbase" class="form-control-line" value="{{ $queryresult->specialbase }}" readonly>
                       
                    </div>
					 <div class="col-sm-6 form-group">
                        <label>Price: </label>
                        <input type="text" id="price" name="price" class="form-control-line" value="{{ $queryresult->price }}" readonly>
                       
                    </div>
                    <div class="col-sm-6 form-group">
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
