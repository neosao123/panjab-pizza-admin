@extends('template.master', ['pageTitle' => 'Toppings View'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Toppings View</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/toppings/list') }}">Toppings</a></li>
                            <li class="breadcrumb-item">View</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/toppings/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
                <form id="addressform" action="{{ url('/toppings/update') }}" method="post" enctype="multipart/form-data"
                    data-parsley-validate="">
                    @csrf
                    <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                    <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                    <div class="row">
                        <div class="col-sm-12 form-group">
                            <label>Toppings Name</label>
                            <input type="text" id="toppingsName" name="toppingsName" class="form-control-line"
                                value="{{ $queryresult->toppingsName }}" readonly>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Topping Section</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="regular" name="topping_type" class="custom-control-input"
                                    value="regular" data-count-as="1"
                                    {{ $queryresult->topping_type == 'regular' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="regular">{{ $settingRegular->settingValue }}
                                    (1)</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="non-regular" name="topping_type" class="custom-control-input"
                                    value="non-regular" data-count-as="{{ $setting->settingValue }}"
                                    {{ $queryresult->topping_type == 'non-regular' ? 'checked' : '' }}>
                                <label class="custom-control-label"
                                    for="non-regular">{{ $settingNonRegular->settingValue }}
                                    ({{ $setting->settingValue }})</label>
                            </div>
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Price :</label>
                            <input type="text" id="price" name="price" class="form-control-line"
                                value="{{ $queryresult->price }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label>Type</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="isPaid" name="isPaid" class="custom-control-input"
                                    value="1" {{ $queryresult->isPaid == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="isPaid">Paid</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="isFree" name="isPaid" class="custom-control-input"
                                    value="0" {{ $queryresult->isPaid == '0' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="isFree">Free</label>
                            </div>
                        </div>
                        @if (!empty($queryresult->toppingsImage))
                            <div class="col-md-3 mb-3" id="eImage">
                                <label>Image :</label>
                                <img class="img-thumbnail mb-2" width="100" height="100" id="showProfileImg"
                                    src="{{ url('uploads/toppings/' . $queryresult->toppingsImage) . '?v=' . time() }}"
                                    data-src="">
                            </div>
                        @endif

                        <div class="col-sm-12 form-group">
                            <label>Status</label>
                            <div class="custom-control custom-checkbox">
                                @if ($queryresult->isActive == 1)
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
