@extends('template.master', ['pageTitle' => 'Dips Update'])
@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Dips Edit</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/dips/list') }}">Dips</a></li>
                        <li class="breadcrumb-item">Edit</li>
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
            <form action="{{ url('/dips/update') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
                @csrf
				<input type="hidden" id="nonzero" value="0">
                <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                <div class="row">
                    <div class="col-sm-12 form-group">
                        <label>Name : <span style="color:red">*</span></label>
                        <input type="text" id="dips" name="dips" class="form-control" required value="{{ $queryresult->dips }}" data-parsley-required-message="Dips is required" maxlength='150' data-parsley-minlength="3" data-parsley-minlength-message="You need to enter at least 3 characters" data-parsley-trigger="change">
                        <span class="text-danger">
                            @error('dips')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                    
                    <div class="col-sm-4 form-group">
                        <label>Price : <span style="color:red">*</span></label>
                        <input type="number" id="price" name="price" step="0.01" min="1" class="form-control" value="{{ $queryresult->price }}" required data-parsley-required-message="Price is required" data-parsley-trigger="change" data-parsley-gt="#nonzero">
                        <span class="text-danger">
                            @error('price')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                   
                    <div class="col-md-3">
                        <label class="form-label" for="form-wizard-progress-wizard-dipsimage">Dips Image</label>
                        <input type="file" id="file" class="form-control " name="dipsImage" accept=".jpg, .jpeg, .png">

                    </div>
                    @if (!empty($queryresult->dipsImage)) 
                    <div class="col-md-3 mb-3" id="eImage">
                        <img class="img-thumbnail mb-2" width="100" height="100" id="showProfileImg" src="{{ url('uploads/dips/' . $queryresult->dipsImage). '?v=' . time() }}" data-src="">
                        <a class="btn btn-danger text-white" onclick="deleteImage('{{ $queryresult->code }}','{{ $queryresult->dipsImage }}');"><i class="fa fa-trash"></i></a>
                    </div>
                    @endif
                    <div class="col-md-3 mb-3 d-none" id="eDisImage">
                        <img class="img-thumbnail mb-2" width="100" height="100" id="showImage" src="" data-src="">
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
<script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/parsley-fields-comparison-validators.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/init_site/dips/edit.js?v=' . time()) }}"></script>

@if (session('error'))
<script>
    $(document).ready(function() {
        'use strict';
        setTimeout(() => {
            $(".alert").remove();
        }, 5000);

    });
</script>
@endif
@endpush