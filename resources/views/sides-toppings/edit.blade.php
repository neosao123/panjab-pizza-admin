@extends('template.master', ['pageTitle' => 'Sides-Toppings Update'])
@push('styles')
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Sides-Toppings Edit</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/sides-toppings/list') }}">Sides-Toppings</a></li>
                        <li class="breadcrumb-item">Edit</li>
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
            <form id="addressform" action="{{ url('/sides-toppings/update') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
                @csrf
                <input type="hidden" id="nonzero" value="0">
                <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                <div class="row">
                    <div class="col-sm-12 form-group">
                        <label>Toppings Name : <span style="color:red">*</span></label>
                        <input type="text" id="toppingsName" name="toppingsName" class="form-control" value="{{ $queryresult->toppingsName }}">
                        <span class="text-danger">
                            @error('toppingsName')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="col-md-12 form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1" {{ $queryresult->isActive == 1 ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isActive"> Status</label>
                        </div>
                    </div>

                    <div class="col-sm-12 form-group">
                        <button class="btn btn-primary" type="submit"> Update </button>

                    </div>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>

@if (session('error'))
<script>
    $(document).ready(function() {
        'use strict';
        setTimeout(() => {
            $(".alert").remove();
        }, 5000);

        $('#tradeform').submit(function(e) {
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