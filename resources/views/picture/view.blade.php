@extends('template.master', ['pageTitle' => 'Picture View'])
@php $viewRights = $viewRights ?? 1; @endphp 
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Picture View</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/pictures/list') }}">Pictures</a></li>
                        <li class="breadcrumb-item">View</li>
                    </ol>
                </nav>
            </div>
        </div>
        @if ($viewRights == 1)
            <div class="col-7 align-self-center">
                <a href="{{ url('/pictures/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
            </div>
        @endif
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

            @if($queryresult)
            <form>
                <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>

                <div class="row">
                    @if (!empty($queryresult->image))
                    <div class="col-md-3 mb-3" id="eImage">
                        <label>Picture Image :</label>
                        <img class="img-thumbnail mb-2" width="100" height="100" src="{{ url('uploads/picture/' . $queryresult->image) . '?v=' . time() }}">
                    </div>
                    @endif

                    <div class="col-sm-12 form-group">
                        <label>Title :</label>
                        <input type="text" name="title" class="form-control-line" readonly value="{{ $queryresult->title }}">
                    </div>

                    <div class="col-sm-12 form-group">
                        <label>Product URL :</label>
                        <input type="text" name="product_url" class="form-control-line" readonly value="{{ $queryresult->product_url ?? '-' }}">
                    </div>


                    <div class="col-sm-12 form-group">
                        <label>Status :</label>
                        @if($queryresult->isActive == 1)
                            <div class="badge badge-success m-1">Active</div>
                        @else
                            <div class="badge badge-warning m-1">Inactive</div>
                        @endif
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
