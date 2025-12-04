@extends('template.master', ['pageTitle' => 'Offer Card View'])
@php $viewRights = $viewRights ?? 1; @endphp
@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">View Offer Card</h4>
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

                @if ($queryresult)
                    <form>
                        <input type="hidden" name="code" value="{{ $queryresult->code }}" readonly>
                        <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>

                        <div class="row">
                            @if (!empty($queryresult->image))
                                <div class="col-md-3 mb-3" id="eImage">
                                    <label>Picture Image :</label>
                                    <img class="img-thumbnail mb-2" width="100" height="100"
                                        src="{{ url('uploads/picture/' . $queryresult->image) . '?v=' . time() }}">
                                </div>
                            @endif

                            <div class="col-sm-12 form-group">
                                <label>Title :</label>
                                <input type="text" name="title" class="form-control" readonly
                                    value="{{ $queryresult->title }}">
                            </div>

                            <!-- Pizza Type (Category) -->
                            <div class="col-sm-6 form-group">
                                <label>Pizza Type :</label>
                                <select id="pizza_type" name="pizza_type" class="form-control" disabled
                                    data-parsley-required-message="Pizza type is required">
                                    <option value="">Select Pizza Type</option>
                                    <option value="special_offers"
                                        {{ $queryresult->pizza_type == 'special_offers' ? 'selected' : '' }}>Special Offers
                                    </option>
                                    <option value="signature_pizzas"
                                        {{ $queryresult->pizza_type == 'signature_pizzas' ? 'selected' : '' }}>Signature
                                        Pizzas</option>
                                    <option value="other_pizzas"
                                        {{ $queryresult->pizza_type == 'other_pizzas' ? 'selected' : '' }}>Other Pizzas
                                    </option>
                                    <option value="sides" {{ $queryresult->pizza_type == 'sides' ? 'selected' : '' }}>
                                        Sides</option>
                                </select>
                                <span class="text-danger">
                                    @error('pizza_type')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>

                            <!-- Product -->
                            <div class="col-sm-6 form-group">
                                <label>Product : </label>
                                <select id="product_id" name="product_id" class="form-control select2"
                                    data-parsley-required-message="Product is required" disabled>
                                    <option value="">Loading...</option>
                                </select>
                                <span class="text-danger">
                                    @error('product_id')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>


                            <div class="col-sm-12 form-group">
                                <label>Product URL :</label>
                                <input type="text" name="product_url" class="form-control" readonly
                                    value="{{ $queryresult->product_url ?? '-' }}">
                            </div>


                            <div class="col-sm-12 form-group">
                                <label>Status :</label>
                                @if ($queryresult->isActive == 1)
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
@push('scripts')
<script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/parsley-fields-comparison-validators.js') }}"></script>
<script>
    // Pass existing product_id to JavaScript
    var existingProductId = '{{ $queryresult->product_id ?? '' }}';
    var existingPizzaType = '{{ $queryresult->pizza_type ?? '' }}';
</script>
<script type="text/javascript" src="{{ asset('theme/init_site/picture/edit.js?v=' . time()) }}"></script>


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
