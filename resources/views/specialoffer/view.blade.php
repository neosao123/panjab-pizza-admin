@extends('template.master', ['pageTitle' => 'Special Offer View'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/js/summernote/dist/summernote-bs4.css') }}" rel="stylesheet">
    <style>
        .btn-group>.btn:first-child,
        .dropdown-toggle-split::after,
        .dropright .dropdown-toggle-split::after,
        .dropup .dropdown-toggle-split::after {
            margin-left: 0;
            background-color: white;
            color: #040404;
            border: 0;
        }
        .badge {
            font-size: 1.0rem;
        }
    </style>
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Special Offer</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/specialoffer/list') }}">Special Offer</a></li>
                            <li class="breadcrumb-item">View</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/specialoffer/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
                @php
                    $i = 1;
                    $specialOfferCount = 0;
                    if ($specialofferline && count($specialofferline) > 0) {
                        $specialOfferCount = count($specialofferline);
                    }
                @endphp
                <form>
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label> Name :</label>
                            <input type="text" id="name" name="name" class="form-control-line"
                                value="{{ $queryresult->name }}" readonly>
                        </div>

                        <div class="col-md-4 form-group">
                            <label> Subtitle :</label>
                            <input type="text" id="subtitle" name="subtitle" class="form-control-line"
                                value="{{ $queryresult->subtitle }}" readonly>
                        </div>

                        {{-- <div class="col-md-6 form-group">
                            <label>Price for Large Pizza:</label>
                            <input type="text" id="price" name="price" class="form-control-line"
                                value="{{ $queryresult->price }}" readonly>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Price for Extra Large Pizza: </label>
                            <input type="text" id="extraLargePrice" name="extraLargePrice" class="form-control-line"
                                value="{{ $queryresult->extraLargePrice }}" readonly>
                        </div> --}}

                        @php
                            $pizza_prices = json_decode($queryresult->pizza_prices, true);
                        @endphp
                        @if ($pizza_prices)
                            @foreach ($pizza_prices as $item)
                                <div class="col-sm-6 col-md-4 col-lg-3 form-group">
                                    <label>Price for {{ $item['size'] }}:</label>
                                    <input type="number" id="price_{{ $item['size'] }}" name="size_{{ $item['size'] }}"
                                        step="0.01" min="0" max="9999" class="form-control-line"
                                        value="{{ $item['price'] }}" readonly>
                                </div>
                            @endforeach
                        @endif

                        <div class="col-md-4 form-group">
                            @php
                                $dealType = '';
                                if ($queryresult->dealType == 'pickupdeal') {
                                    $dealType = 'Pickup Deal';
                                } elseif ($queryresult->dealType == 'deliverydeal') {
                                    $dealType = 'Delivery Deal';
                                } else {
                                    $dealType = 'Others';
                                }
                            @endphp
                            <label> Deal Type :</label>
                            <input type="text" id="dealType" name="dealType" class="form-control-line"
                                value="{{ $dealType }}" readonly>
                        </div>

                        <div class="col-md-12 form-group">
                            <label> Description : </label>
                            <textarea id="description" name="description" class="form-control summernote" rows="4">{{ $queryresult->description }}</textarea>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Number of pizza :</label>
                            <input type="text" id="noofPizza" name="noofPizza" class="form-control-line"
                                value="{{ $queryresult->noofPizza }}" readonly>
                        </div>

                        <div class="col-md-3 form-group d-none">
                            <label>Number of toppings :</label>
                            <input type="text" id="noofToppings" name="noofToppings" class="form-control-line"
                                value="{{ $queryresult->noofToppings }}" readonly>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Number of dips :</label>
                            <input type="text" id="noofDips" name="noofDips" class="form-control-line"
                                value="{{ $queryresult->noofDips }}" readonly>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Number of side :</label>
                            <input type="text" id="noofSides" name="noofSides" class="form-control-line"
                                value="{{ $queryresult->noofSides }}" readonly>

                        </div>

                        <div class="col-md-3 form-group">
                            <label>Pops:</label>
                            <div class="form-group">
                                <input type="text" id="pops" name="pops" class="form-control-line"
                                    value="{{ $queryresult->pops }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Bottle:</label>
                            <div class="form-group">
                                <input type="text" id="bottle" name="bottle" class="form-control-line"
                                    value="{{ $queryresult->bottle }}" readonly>

                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Types:</label>
                            <div class="form-group">
                                @php
                                    $typeCode = json_decode($queryresult->type, true);

                                @endphp
                                @if ($typeCode)
                                    @foreach ($typeCode as $item)
                                        <span>{{ $item }},</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        @if (!empty($queryresult->specialofferphoto))
                            <div class="col-md-3 mb-3" id="eImage">
                                <label>Special Offer Image:</label>
                                <div class="form-group">
                                    <img class="img-thumbnail mb-2" width="100" height="100" id="showProfileImg"
                                        src="{{ url('uploads/specialoffer/' . $queryresult->specialofferphoto) . '?v=' . time() }}"
                                        data-src="">
                                </div>
                            </div>
                        @endif

                        <div class="col-md-12">
                            <h5>Sides List</h5>
                            <table class="table table-bordered" id="tbl-labour" style="width:100%">
                                <thead>
                                    <th width="60%">Sides</th>
                                    <th width="40%">Size</th>
                                </thead>
                                <tbody id="tableBody">
                                    @if ($specialOfferCount > 0)
                                        @for ($i = 0; $i < $specialOfferCount; $i++)
                                            <tr id="row{{ $i }}" class="tblrows">
                                                <td>
                                                    {{ $specialofferline[$i]->sidename }}
                                                </td>
                                                <td>
                                                    {{ $specialofferline[$i]->size }} {{ $specialofferline[$i]->price }}
                                                </td>
                                            </tr>
                                        @endfor
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="col-sm-2 form-group">
                            <label>Status</label>
                            @if ($queryresult->isActive == 1)
                                <div class="d-block badge badge-success m-1">Active</div>
                            @else
                                <div class="d-block badge badge-warning m-1">Inactive</div>
                            @endif
                        </div>

                        <div class="col-sm-2 form-group">
                            <label>Show On Client</label>
                            @if ($queryresult->showOnClient == 1)
                                <div class="d-block badge badge-success m-1">Yes</div>
                            @else
                                <div class="d-block badge badge-warning m-1">No</div>
                            @endif
                        </div>

                        <div class="col-sm-2 form-group">
                            <label>Limited Offer</label>
                            @if ($queryresult->limited_offer == 1)
                                <div class="d-block badge badge-success m-1">Yes</div>
                            @else
                                <div class="d-block badge badge-warning m-1">No</div>
                            @endif
                        </div>

                        <div class="col-md-4 col-lg-3 form-group">
                            <label> Start Date :</label>
                            <input type="text" id="start_date" name="start_date" class="form-control-line"
                                value="{{ $queryresult->start_date !== '' ? date('d-m-Y h:i A', strtotime($queryresult->start_date)) : '' }}"
                                readonly>
                        </div>

                        <div class="col-md-4 col-lg-3 form-group">
                            <label> End Date :</label>
                            <input type="text" id="end_date" name="end_date" class="form-control-line"
                                value="{{ $queryresult->end_date !== '' ? date('d-m-Y h:i A', strtotime($queryresult->end_date)) : '' }}"
                                readonly>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript" src="{{ asset('theme/js/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#description").summernote({
                height: 100,
                styleTags: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
                toolbar: [

                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']]
                ]
            });
        });
    </script>
@endpush
