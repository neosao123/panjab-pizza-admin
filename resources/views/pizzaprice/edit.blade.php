<!-- Developer - Shreyas Mahamuni -->
<!-- Working Date - 22-11-2023 -->
<!-- This Page for pizza price edit fucntionality -->

@extends('template.master', ['pageTitle' => 'Pizza Price Edit'])
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Pizza Price Edit</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/pizzaprice/list') }}">Pizza Price</a></li>
                        <li class="breadcrumb-item">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
        <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/pizzaprice/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
            </div>
        <?php } ?>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-12">
                    <h5 class="mb-0" data-anchor="data-anchor">Edit</h5>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($queryresult)
            <form id="addressform" action="{{ url('/pizzaprice/update') }}" method="post" data-parsley-validate="">
                @csrf
                <div class="row">
                    <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                    <div class="col-md-6 form-group">
                        <label>Pizza Size : </label>
                        <input type="text" id="pizzasize" name="pizzasize" class="form-control-line" value="{{ $queryresult->size }}" required data-parsley-required-message="Large Size is required" data-parsley-trigger="change" readonly>
                        <span class="text-danger">
                            @error('pizzasize')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Pizza Price : </label>
                        <input type="number" id="pizzaprice" step="0.01" min="1" max="99" name="pizzaprice" class="form-control-line" value="{{ $queryresult->price }}" required data-parsley-required-message="Large Pizza Price is required" data-parsley-trigger="change">
                        <span class="text-danger">
                            @error('pizzaprice')
                            {{ $message }} 
                            @enderror
                        </span>
                    </div>
                </div>
                <div class="col-sm-12 form-group">
                    <button class="btn btn-primary" type="submit"> Update </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection