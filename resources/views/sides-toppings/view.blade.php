@extends('template.master', ['pageTitle' => 'Sides-Toppings View'])
@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Sides-Toppings View</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/sides-toppings/list') }}">Sides-Toppings</a></li>
                        <li class="breadcrumb-item">View</li>
                    </ol>
                </nav>
            </div>
        </div>
        <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/sides-toppings/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif
            <form id="addressform">
                @csrf
                <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                <div class="row">
                    <div class="col-sm-12 form-group">
                        <label>Toppings Name</label>
                        <input type="text" id="toppingsName" name="toppingsName" class="form-control-line" value="{{ $queryresult->toppingsName }}" readonly>
                    </div>
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