<!-- Developer - Shreyas Mahamuni -->
<!-- Working Date - 22-11-2023 -->
<!-- This Page for Pizza Prices View -->

@extends('template.master', ['pageTitle' => 'Pizza Price View'])
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Pizza Price View</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/pizzaprice/list') }}">Pizza Price</a></li>
                        <li class="breadcrumb-item">View</li>
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
                    <h5 class="mb-0" data-anchor="data-anchor">View</h5>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($queryresult)
            <form>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Pizza Size : </label>
                        <input type="text" id="pizzaname" name="pizzaname" class="form-control-line" value="{{ $queryresult->size }}" readonly>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Pizza Price : </label>
                        <input type="number" id="pizzaprice" name="pizzaprice" class="form-control-line" value="{{ $queryresult->price }}" readonly>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection