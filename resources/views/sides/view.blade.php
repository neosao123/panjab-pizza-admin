@extends('template.master', ['pageTitle' => 'Sides View'])
@push('styles')

@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Side View</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/sides/list') }}">Side</a></li>
                        <li class="breadcrumb-item">View</li>
                    </ol>
                </nav>
            </div>
        </div>
        <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/sides/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
            <form>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>Sides Name :</label>
                        <input type="text" id="sideName" name="sideName" class="form-control-line" readonly value="{{ $queryresult->sidename }}">
                    </div>
                    <div class="col-md-6">
                        <label>Type : </label>
                        <div class="form-inline">
                            <div class="custom-control custom-radio mr-2">
                                <input type="radio" id="side" name="type" class="custom-control-input" value="side" {{ $queryresult->type == 'side' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="side">Side</label>
                            </div>
                            <div class="custom-control custom-radio mr-2">
                                <input type="radio" id="subs" name="type" class="custom-control-input" value="subs" {{ $queryresult->type == 'subs' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="subs">Subs</label>
                            </div>
                            <div class="custom-control custom-radio mr-2">
                                <input type="radio" id="poutine" name="type" class="custom-control-input" value="poutine" {{ $queryresult->type == 'poutine' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="poutine">Poutine</label>
                            </div>
                            <div class="custom-control custom-radio mr-2">
                                <input type="radio" id="plantbites" name="type" class="custom-control-input" value="plantbites" {{ $queryresult->type == 'plantbites' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="plantbites">Plant Bites</label>
                            </div>
                            <div class="custom-control custom-radio mr-2">
                                <input type="radio" id="tenders" name="type" class="custom-control-input" value="tenders" {{ $queryresult->type == 'tenders' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="tenders">Tenders</label>
                            </div>
                        </div>
                    </div>
                    @if (!empty($queryresult->image))
                    <div class="col-md-3 mb-3 form-group " id="eImage">
                        <label class="form-label">Side Image</label>
                        <div class="form-group">
                            <img class="img-thumbnail mb-2" width="100" height="100" id="showSideImg" src="{{ url('uploads/sides/' . $queryresult->image) }}" data-src="">
                            <a class="btn btn-danger text-white" onclick="deleteImage('{{ $queryresult->code }}','{{ $queryresult->image }}');"><i class="fa fa-trash"></i></a>
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

                @if($sidelineentries && count($sidelineentries)>0)
                @foreach($sidelineentries as $item)
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Size</label>
                        <input type="hidden" class="form-control-line" name="rowCode[]" value="{{$item->code}}">
                        <input type="text" class="form-control-line" name="size[]" readonly value="{{$item->size}}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Price</label>
                        <input type="text" class="form-control-line" name="price[]" value="{{$item->price}}">
                    </div>
                </div>
                @endforeach
                @endif

                <div class="row">
                    <div class="col-md-12 form-group ">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" onclick="return false" class="custom-control-input" id="hasToppings" name="hasToppings" value="1" {{ $queryresult->hasToppings == 1 ? 'checked' : '' }}>
                            <label class="custom-control-label" for="hasToppings">hasToppings</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Number of Toppings : <span style="color:red">*</span></label>
                        <input type="number" step="1" id="nooftoppings" name="nooftoppings" class="form-control-line" value="{{ $queryresult->nooftoppings }}" readonly>
                        <span class="text-danger">
                            @error('nooftoppings')
                            {{ $message }}
                            @enderror
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@endpush