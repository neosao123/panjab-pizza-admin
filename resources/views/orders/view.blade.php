@extends('template.master', ['pageTitle' => 'Orders View'])
@section('content')
    <link rel="stylesheet" href="{{ asset('theme/css/orderView.css') }}" />
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Orders</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/orders/list') }}">Orders</a></li>
                            <li class="breadcrumb-item">View</li>
                        </ol>
                    </nav>
                </div>
            </div>
            @if ($viewRights == 1)
                <div class="col-7 align-self-center ">
                    <a href="{{ url('/orders/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
                </div>
            @endif
        </div>
    </div>
    <div class="container-fluid">
        @if ($queryresult)
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-10">
                            <h5 class="mb-0" data-anchor="data-anchor">View</h5>
                        </div>
                        <div class="col-sm-2 text-right d-none">
                            <a href="{{ url('orders/invoice/' . $queryresult->code) }}" title="Invoice pdf" target="_blank">
                                <button id="print" class="btn btn-primary btn-outline" type="button">
                                    <i class="fa fa-print"></i> <span>Print Invoice</span> </button>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $i = 1;
                        $orderCount = 0;
                        if ($orderlineentries && count($orderlineentries) > 0) {
                            $orderCount = count($orderlineentries);
                        }
                        $allowedPizzas = ['custom_pizza', 'signature_pizza', 'other_pizza'];
                        $allowedSpecialPizzas = ['special_pizza'];
                    @endphp

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Order Code :</label>
                            <input type="text" id="orderCode" name="orderCode" class="form-control-line"
                                value="{{ $queryresult->code }}" readonly>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Receipt Number :</label>
                            <input type="text" id="receiptNumber" name="receiptNumber" class="form-control-line"
                                value="{{ $queryresult->txnId }}" readonly>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Order Date :</label>
                            <input type="text" id="orderDate" name="orderDate" class="form-control-line"
                                value="{{ date('d-m-Y h:i A', strtotime($queryresult->orderDate)) }}" readonly>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Customer Name</label>
                            <input type="text" id="customerName" name="customerName" class="form-control-line"
                                value="{{ $queryresult->customerName }}" readonly>

                        </div>
                        <div class="col-md-4 form-group">
                            <label>Mobile Number</label>
                            <input type="text" name="mobileNumber" class="form-control-line"
                                value="{{ $queryresult->mobileNumber }}" readonly>

                        </div>
                        @if ($queryresult->deliveryType != 'pickup')
                            <div class="col-md-4 form-group">
                                <label>Postal Code</label>
                                <input type="text" name="zipCode" class="form-control-line"
                                    value="{{ $queryresult->zipCode }}" readonly>

                            </div>
                            <div class="col-md-4 form-group">
                                <label>Delivery Executive</label>
                                <input type="text" name="deliveryExecutive" class="form-control-line"
                                    value="{{ $queryresult->username }}" readonly>

                            </div>
                        @endif
                        <div class="col-md-4 form-group">
                            <label>Order Status</label>
                            <select class="form-control" id="orderStatus" name="orderStatus"
                                @if (in_array($queryresult->orderStatus, ['delivered', 'cancelled'])) disabled @endif>

                                <option value="">Select Order Status</option>

                                @if ($queryresult->orderFrom == 'store' && $queryresult->deliveryType == 'delivery')
                                    <option value="cancelled"
                                        {{ $queryresult->orderStatus == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled
                                    </option>
                                @endif

                                @if ($queryresult->orderFrom == 'store' && $queryresult->deliveryType == 'pickup')
                                    <option value="placed" {{ $queryresult->orderStatus == 'placed' ? 'selected' : '' }}>
                                        Placed
                                    </option>

                                    <option value="picked-up"
                                        {{ $queryresult->orderStatus == 'picked-up' ? 'selected' : '' }}>
                                        Picked Up
                                    </option>

                                    <option value="cancelled"
                                        {{ $queryresult->orderStatus == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled
                                    </option>
                                @endif

                                @if ($queryresult->orderFrom == 'online')
                                    <option value="placed" {{ $queryresult->orderStatus == 'placed' ? 'selected' : '' }}>
                                        Placed
                                    </option>

                                    <option value="shipping"
                                        {{ $queryresult->orderStatus == 'shipping' ? 'selected' : '' }}>
                                        Shipping
                                    </option>

                                    <option value="delivered"
                                        {{ $queryresult->orderStatus == 'delivered' ? 'selected' : '' }}>
                                        Delivered
                                    </option>

                                    <option value="cancelled"
                                        {{ $queryresult->orderStatus == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled
                                    </option>
                                @endif
                            </select>
                        </div>

                        @if ($queryresult->address != '')
                            <div class="col-md-12 form-group">
                                <label>Address : </label>
                                <textarea id="address" name="address" class="form-control" rows="4" readonly>{{ $queryresult->address }}</textarea>
                            </div>
                        @endif
                        <div class="col-md-4 form-group">
                            <label>Delivery Type</label>
                            <input type="text" name="deliveryType" class="form-control-line"
                                value="{{ $queryresult->deliveryType }}" readonly>

                        </div>
                        <div class="col-md-4 form-group">
                            <label>Store Location :</label>
                            <input type="text" id="storeLocation" name="storeLocation" class="form-control-line"
                                value="{{ $queryresult->storeLocation }}" readonly>

                        </div>
                        <div class="col-md-4 form-group">
                            <label>Client Type :</label>
                            <input type="text" id="clientType" name="clientType" class="form-control-line"
                                value="{{ $queryresult->clientType }}" readonly>

                        </div>

                        @if ($queryresult->comments != '')
                            <div class="col-md-12 form-group p-2">
                                <div class="proRows">
                                    <strong>
                                        <span>Credit Comments : </span>
                                    </strong>
                                    <br />
                                    <span class="py-1">{{ $queryresult->comments }}
                                </div>
                            </div>
                        @endif

                        <div class="mx-2">
                            <label>
                                <h5>Product Details </h5>
                            </label>
                        </div>

                        <div class="product-details container-fluid px-2">
                            <!-- Product Columns -->
                            <div class="product-columns d-flex justify-content-between border-bottom mb-2">
                                <div class="proCol mb-2">
                                    <strong><span>Product</span></strong>
                                </div>
                                <div class="quantityCol mb-2">
                                    <strong><span>Qty</span></strong>
                                </div>
                                <div class="amountCol mb-2">
                                    <strong><span>Amount</span></strong>
                                </div>
                            </div>

                            <!-- Product Details Rows  -->
                            @if ($orderCount > 0)
                                @for ($i = 0; $i < $orderCount; $i++)
                                    @php
                                        $data = json_decode($orderlineentries[$i]->config, true);
                                    @endphp

                                    <div class="product-columns d-flex justify-content-between">
                                        <div class="proRows">
                                            <strong><span>{{ $orderlineentries[$i]->productName }}</span></strong>
                                        </div>
                                        <div class="quantityRows">
                                            <strong><span>{{ $orderlineentries[$i]->quantity }}</span></strong>
                                        </div>
                                        <div class="amountRows">
                                            <strong>
                                                @if (
                                                    $orderlineentries[$i]->productType == 'custom_pizza' ||
                                                        $orderlineentries[$i]->productType == 'special_pizza' ||
                                                        $orderlineentries[$i]->productType == 'other_pizza')
                                                    <span>$ {{ $orderlineentries[$i]->pizzaPrice }}</span>
                                                @else
                                                    <span>$ {{ $orderlineentries[$i]->amount }}</span>
                                                @endif
                                            </strong>
                                        </div>
                                    </div>

                                    @if ($orderlineentries[$i]->productType == 'side' && isset($data['sidesSize']))
                                        <div class="product-columns d-flex justify-content-between">
                                            <div class="proRows">
                                                <strong></strong><span
                                                    style="font-weight: normal;">{{ $data['sidesSize'] }}</span>
                                            </div>
                                            <div class="quantityRows">
                                                <span></span>
                                            </div>
                                            <div class="amountRows">
                                                <span></span>
                                            </div>
                                        </div>
                                    @endif

                                    @if (in_array($orderlineentries[$i]->productType, $allowedPizzas))
                                        @if (isset($data['pizza']))
                                            @foreach ($data['pizza'] as $pizzaItem)
                                                {{-- Display Crust --}}
                                                @if (isset($pizzaItem['crust']) && isset($pizzaItem['crust']['crustName']))
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows">
                                                            <strong>Crust:</strong><span
                                                                style="font-weight: normal;">{{ $pizzaItem['crust']['crustName'] }}</span>
                                                        </div>
                                                        <div class="quantityRows">
                                                            <strong><span></span></strong>
                                                        </div>
                                                        <div class="amountRows">
                                                            <strong><span>
                                                                    @if (isset($pizzaItem['crust']['price']) && $pizzaItem['crust']['price'] != 0)
                                                                        $ {{ $pizzaItem['crust']['price'] }}
                                                                    @endif
                                                                </span></strong>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Display Crust Type --}}
                                                @if (isset($pizzaItem['crustType']) && isset($pizzaItem['crustType']['crustType']))
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows">
                                                            <strong>Crust Type:</strong><span
                                                                style="font-weight: normal;">{{ $pizzaItem['crustType']['crustType'] }}</span>
                                                        </div>
                                                        <div class="quantityRows">
                                                            <strong><span></span></strong>
                                                        </div>
                                                        <div class="amountRows">
                                                            <strong><span>
                                                                    @if (isset($pizzaItem['crustType']['price']) && $pizzaItem['crustType']['price'] != 0)
                                                                        $ {{ $pizzaItem['crustType']['price'] }}
                                                                    @endif
                                                                </span></strong>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Display Cheese --}}
                                                @if (isset($pizzaItem['cheese']) && isset($pizzaItem['cheese']['cheeseName']))
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows">
                                                            <strong>Cheese:</strong><span
                                                                style="font-weight: normal;">{{ $pizzaItem['cheese']['cheeseName'] }}</span>
                                                        </div>
                                                        <div class="quantityRows">
                                                            <strong><span></span></strong>
                                                        </div>
                                                        <div class="amountRows">
                                                            <strong><span>
                                                                    @if (isset($pizzaItem['cheese']['price']) && $pizzaItem['cheese']['price'] != 0)
                                                                        $ {{ $pizzaItem['cheese']['price'] }}
                                                                    @endif
                                                                </span></strong>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Display Special Bases --}}
                                                @if (isset($pizzaItem['specialBases']) && isset($pizzaItem['specialBases']['specialbaseName']))
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows">
                                                            <strong>Special Base:</strong><span
                                                                style="font-weight: normal;">
                                                                {{ $pizzaItem['specialBases']['specialbaseName'] }}
                                                            </span>
                                                        </div>
                                                        <div class="quantityRows">
                                                            <strong><span></span></strong>
                                                        </div>
                                                        <div class="amountRows">
                                                            <strong><span>
                                                                    @if (isset($pizzaItem['specialBases']['price']) && $pizzaItem['specialBases']['price'] != 0)
                                                                        $ {{ $pizzaItem['specialBases']['price'] }}
                                                                    @endif
                                                                </span></strong>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Display Spicy --}}
                                                @if (isset($pizzaItem['spicy']) && isset($pizzaItem['spicy']['spicy']))
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows">
                                                            <strong>Spicy:</strong><span style="font-weight: normal;">
                                                                {{ $pizzaItem['spicy']['spicy'] }}
                                                            </span>
                                                        </div>
                                                        <div class="quantityRows">
                                                            <strong><span></span></strong>
                                                        </div>
                                                        <div class="amountRows">
                                                            <strong><span>
                                                                    @if (isset($pizzaItem['spicy']['price']) && $pizzaItem['spicy']['price'] != 0)
                                                                        $ {{ $pizzaItem['spicy']['price'] }}
                                                                    @endif
                                                                </span></strong>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Display Sauce --}}
                                                @if (isset($pizzaItem['sauce']) && isset($pizzaItem['sauce']['sauce']))
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows">
                                                            <strong>Sauce:</strong><span style="font-weight: normal;">
                                                                {{ $pizzaItem['sauce']['sauce'] }}
                                                            </span>
                                                        </div>
                                                        <div class="quantityRows">
                                                            <strong><span></span></strong>
                                                        </div>
                                                        <div class="amountRows">
                                                            <strong><span>
                                                                    @if (isset($pizzaItem['sauce']['price']) && $pizzaItem['sauce']['price'] != 0)
                                                                        $ {{ $pizzaItem['sauce']['price'] }}
                                                                    @endif
                                                                </span></strong>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Display Cook --}}
                                                @if (isset($pizzaItem['cook']) && isset($pizzaItem['cook']['cook']))
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows">
                                                            <strong>Cook:</strong><span
                                                                style="font-weight: normal;">{{ $pizzaItem['cook']['cook'] }}</span>
                                                        </div>
                                                        <div class="quantityRows">
                                                            <strong><span></span></strong>
                                                        </div>
                                                        <div class="amountRows">
                                                            <strong><span>
                                                                    @if (isset($pizzaItem['cook']['price']) && $pizzaItem['cook']['price'] != 0)
                                                                        $ {{ $pizzaItem['cook']['price'] }}
                                                                    @endif
                                                                </span></strong>
                                                        </div>
                                                    </div>
                                                @endif

                                                {{-- Display Toppings --}}
                                                @if (isset($pizzaItem['toppings']))
                                                    {{-- Count as One Toppings --}}
                                                    @if (isset($pizzaItem['toppings']['countAsOneToppings']) && count($pizzaItem['toppings']['countAsOneToppings']) > 0)
                                                        <div class="product-columns d-flex justify-content-between">
                                                            <div class="proRows">
                                                                <strong>Toppings:</strong>
                                                            </div>
                                                            <div class="quantityRows">
                                                                <strong><span></span></strong>
                                                            </div>
                                                            <div class="amountRows">
                                                                <strong><span></span></strong>
                                                            </div>
                                                        </div>

                                                        @foreach ($pizzaItem['toppings']['countAsOneToppings'] as $topping)
                                                            <div class="product-columns d-flex justify-content-between">
                                                                <div class="proRows">
                                                                    <span
                                                                        style="font-weight: normal;">{{ $topping['toppingsName'] }}
                                                                        @if (isset($topping['toppingsPlacement']) && $topping['toppingsPlacement'] == 'whole')
                                                                            (W)
                                                                        @elseif (isset($topping['toppingsPlacement']) && $topping['toppingsPlacement'] == 'lefthalf')
                                                                            (L)
                                                                        @elseif (isset($topping['toppingsPlacement']) && $topping['toppingsPlacement'] == 'righthalf')
                                                                            (R)
                                                                        @elseif (isset($topping['toppingsPlacement']) && $topping['toppingsPlacement'] == '1/4')
                                                                            (1/4)
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="quantityRows">
                                                                    <strong><span></span></strong>
                                                                </div>
                                                                <div class="amountRows">
                                                                    <strong><span>
                                                                            @if (isset($topping['amount']) && $topping['amount'] != '0')
                                                                                $ {{ $topping['amount'] }}
                                                                            @endif
                                                                        </span></strong>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    {{-- Display Indian Style + Coriander --}}
                                                    @if (isset($pizzaItem['toppings']['isAllIndiansTps']) && $pizzaItem['toppings']['isAllIndiansTps'] == true)
                                                        <div class="product-columns d-flex justify-content-between">
                                                            <div class="proRows">
                                                                <strong><span>Indian Style + Coriander</span></strong>
                                                            </div>
                                                            <div class="quantityRows">
                                                                <span></span>
                                                            </div>
                                                            <div class="amountRows">
                                                                <span></span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif

                                        {{-- Display Sides --}}
                                        @if ((isset($data['sides']) && count($data['sides']) > 0) || (isset($data['side']) && count($data['side']) > 0))
                                            <div class="product-columns d-flex justify-content-between">
                                                <div class="proRows">
                                                    <strong>Sides:</strong>
                                                </div>
                                                <div class="quantityRows">
                                                    <strong><span></span></strong>
                                                </div>
                                                <div class="amountRows">
                                                    <strong><span></span></strong>
                                                </div>
                                            </div>
                                            @php
                                                $sidesArray = isset($data['sides'])
                                                    ? $data['sides']
                                                    : (isset($data['side'])
                                                        ? $data['side']
                                                        : []);
                                            @endphp
                                            @foreach ($sidesArray as $side)
                                                <div class="product-columns d-flex justify-content-between">
                                                    <div class="proRows">
                                                        <span style="font-weight: normal;">{{ $side['sideName'] ?? '' }}
                                                            @if (isset($side['sideSize']))
                                                                ({{ $side['sideSize'] }})
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="quantityRows">
                                                        <strong><span>{{ $side['quantity'] ?? 1 }}</span></strong>
                                                    </div>
                                                    <div class="amountRows">
                                                        @if (isset($side['totalPrice']) && $side['totalPrice'] != '0')
                                                            <strong><span>$ {{ $side['totalPrice'] }}</span></strong>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif

                                        {{-- Display Dips --}}
                                        @if (isset($data['dips']) && count($data['dips']) > 0)
                                            <div class="product-columns d-flex justify-content-between">
                                                <div class="proRows">
                                                    <strong>Dips:</strong>
                                                </div>
                                                <div class="quantityRows">
                                                    <strong><span></span></strong>
                                                </div>
                                                <div class="amountRows">
                                                    <strong><span></span></strong>
                                                </div>
                                            </div>
                                            @foreach ($data['dips'] as $dips)
                                                <div class="product-columns d-flex justify-content-between">
                                                    <div class="proRows">
                                                        <span
                                                            style="font-weight: normal;">{{ $dips['dipsName'] ?? '' }}</span>
                                                    </div>
                                                    <div class="quantityRows">
                                                        <strong><span>{{ $dips['quantity'] ?? 1 }}</span></strong>
                                                    </div>
                                                    <div class="amountRows">
                                                        @if (isset($dips['totalPrice']) && $dips['totalPrice'] != '0')
                                                            <strong><span>$ {{ $dips['totalPrice'] }}</span></strong>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif

                                        {{-- Display Drinks --}}
                                        @if (isset($data['drinks']) && count($data['drinks']) > 0)
                                            <div class="product-columns d-flex justify-content-between">
                                                <div class="proRows">
                                                    <strong>Drinks:</strong>
                                                </div>
                                                <div class="quantityRows">
                                                    <strong><span></span></strong>
                                                </div>
                                                <div class="amountRows">
                                                    <strong><span></span></strong>
                                                </div>
                                            </div>
                                            @foreach ($data['drinks'] as $drinks)
                                                <div class="product-columns d-flex justify-content-between">
                                                    <div class="proRows">
                                                        <span
                                                            style="font-weight: normal;">{{ $drinks['drinksName'] ?? '' }}</span>
                                                    </div>
                                                    <div class="quantityRows">
                                                        <strong><span>{{ $drinks['quantity'] ?? 1 }}</span></strong>
                                                    </div>
                                                    <div class="amountRows">
                                                        @if (isset($drinks['totalPrice']) && $drinks['totalPrice'] != '0')
                                                            <strong><span>$ {{ $drinks['totalPrice'] }}</span></strong>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif

                                        {{-- Display Comments --}}
                                        @if (isset($orderlineentries[$i]->comments) && $orderlineentries[$i]->comments != '')
                                            <div class="product-columns d-flex justify-content-between">
                                                <div class="proRows">
                                                    <strong>Comments:</strong><span
                                                        style="font-weight: normal;">{{ $orderlineentries[$i]->comments }}</span>
                                                </div>
                                                <div class="quantityRows">
                                                    <span></span>
                                                </div>
                                                <div class="amountRows">
                                                    <span></span>
                                                </div>
                                            </div>
                                            <hr>
                                        @endif
                                    @endif

                                    {{-- Special Pizza Section (unchanged) --}}
                                    @if (in_array($orderlineentries[$i]->productType, $allowedSpecialPizzas))
                                        @if (isset($data['pizza']))
                                            @foreach ($data['pizza'] as $index => $pizzaItem)
                                                {{-- Display Pizza Name --}}
                                                @if (isset($pizzaItem['signaturePizzaName']))
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows">
                                                            <strong><span>{{ $pizzaItem['signaturePizzaName'] }}</span></strong>
                                                        </div>
                                                        <div class="quantityRows"><strong><span></span></strong></div>
                                                        <div class="amountRows"><strong><span></span></strong></div>
                                                    </div>

                                                    {{-- Display Indian Style if enabled --}}
                                                    @if (isset($pizzaItem['toppings']['isAllIndiansTps']) && $pizzaItem['toppings']['isAllIndiansTps'] == true)
                                                        <div class="product-columns d-flex justify-content-between">
                                                            <div class="proRows">
                                                                <strong><span>Indian Style + Coriander</span></strong>
                                                            </div>
                                                            <div class="quantityRows"><span></span></div>
                                                            <div class="amountRows"><span></span></div>
                                                        </div>
                                                    @endif
                                                @endif

                                                {{-- Display Special Base if not "No Special (base)" --}}
                                                @if (isset($pizzaItem['specialBases']) &&
                                                        isset($pizzaItem['specialBases']['specialbaseName']) &&
                                                        $pizzaItem['specialBases']['specialbaseName'] != 'No Special (base)')
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows">
                                                            <strong>Special Base:</strong><span
                                                                style="font-weight: normal;">
                                                                {{ $pizzaItem['specialBases']['specialbaseName'] }}
                                                            </span>
                                                        </div>
                                                        <div class="quantityRows"><strong><span></span></strong></div>
                                                        <div class="amountRows">
                                                            <strong><span>
                                                                    @if ($pizzaItem['specialBases']['price'] != 0)
                                                                        $ {{ $pizzaItem['specialBases']['price'] }}
                                                                    @endif
                                                                </span></strong>
                                                        </div>
                                                    </div>
                                                @elseif (isset($pizzaItem['specialBases']) && isset($pizzaItem['specialBases']['specialbaseName']))
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows">
                                                            <strong>Special Base:</strong><span
                                                                style="font-weight: normal;">
                                                                {{ $pizzaItem['specialBases']['specialbaseName'] }}
                                                            </span>
                                                        </div>
                                                        <div class="quantityRows"><strong><span></span></strong></div>
                                                        <div class="amountRows"><strong><span></span></strong></div>
                                                    </div>
                                                @endif

                                                {{-- Display Toppings Section --}}
                                                @if (isset($pizzaItem['toppings']))
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows"><strong>Toppings:</strong></div>
                                                        <div class="quantityRows"><strong><span></span></strong></div>
                                                        <div class="amountRows"><strong><span></span></strong></div>
                                                    </div>

                                                    {{-- Count as Two Toppings --}}
                                                    @if (isset($pizzaItem['toppings']['countAsTwoToppings']) && count($pizzaItem['toppings']['countAsTwoToppings']) > 0)
                                                        @foreach ($pizzaItem['toppings']['countAsTwoToppings'] as $topping)
                                                            <div class="product-columns d-flex justify-content-between">
                                                                <div class="proRows">
                                                                    <strong>(2) </strong><span
                                                                        style="font-weight: normal;">{{ $topping['toppingsName'] }}
                                                                        @if ($topping['toppingsPlacement'] == 'lefthalf')
                                                                            (L)
                                                                        @elseif ($topping['toppingsPlacement'] == 'righthalf')
                                                                            (R)
                                                                        @elseif ($topping['toppingsPlacement'] == '1/4')
                                                                            (1/4)
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="quantityRows"><strong><span></span></strong>
                                                                </div>
                                                                <div class="amountRows">
                                                                    <strong><span>
                                                                            @if (isset($topping['amount']) && $topping['amount'] != '0')
                                                                                $ {{ $topping['amount'] }}
                                                                            @endif
                                                                        </span></strong>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    {{-- Count as One Toppings --}}
                                                    @if (isset($pizzaItem['toppings']['countAsOneToppings']) && count($pizzaItem['toppings']['countAsOneToppings']) > 0)
                                                        @foreach ($pizzaItem['toppings']['countAsOneToppings'] as $topping)
                                                            <div class="product-columns d-flex justify-content-between">
                                                                <div class="proRows">
                                                                    <span
                                                                        style="font-weight: normal;">{{ $topping['toppingsName'] }}
                                                                        @if ($topping['toppingsPlacement'] == 'lefthalf')
                                                                            (L)
                                                                        @elseif ($topping['toppingsPlacement'] == 'righthalf')
                                                                            (R)
                                                                        @elseif ($topping['toppingsPlacement'] == '1/4')
                                                                            (1/4)
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="quantityRows"><strong><span></span></strong>
                                                                </div>
                                                                <div class="amountRows">
                                                                    <strong><span>
                                                                            @if (isset($topping['amount']) && $topping['amount'] != '0')
                                                                                $ {{ $topping['amount'] }}
                                                                            @endif
                                                                        </span></strong>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    {{-- Display Free Toppings if not Indian Style --}}
                                                    @if (!isset($pizzaItem['toppings']['isAllIndiansTps']) || $pizzaItem['toppings']['isAllIndiansTps'] == false)
                                                        @if (isset($pizzaItem['toppings']['freeToppings']) && count($pizzaItem['toppings']['freeToppings']) > 0)
                                                            <div class="product-columns d-flex justify-content-between">
                                                                <div class="proRows"><strong>Free Toppings:</strong></div>
                                                                <div class="quantityRows"><strong><span></span></strong>
                                                                </div>
                                                                <div class="amountRows"><strong><span></span></strong>
                                                                </div>
                                                            </div>
                                                            @foreach ($pizzaItem['toppings']['freeToppings'] as $topping)
                                                                <div
                                                                    class="product-columns d-flex justify-content-between">
                                                                    <div class="proRows">
                                                                        <span
                                                                            style="font-weight: normal;">{{ $topping['toppingsName'] }}</span>
                                                                    </div>
                                                                    <div class="quantityRows">
                                                                        <strong><span></span></strong></div>
                                                                    <div class="amountRows"><strong><span></span></strong>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                @endif

                                                {{-- Add separator between multiple pizzas --}}
                                                @if (!$loop->last)
                                                    <div class="product-columns d-flex justify-content-between">
                                                        <div class="proRows">
                                                            <hr>
                                                        </div>
                                                        <div class="quantityRows">
                                                            <hr>
                                                        </div>
                                                        <div class="amountRows">
                                                            <hr>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endif

                                    @if (
                                        $orderlineentries[$i]->productType == 'side' ||
                                            $orderlineentries[$i]->productType == 'dips' ||
                                            $orderlineentries[$i]->productType == 'drinks')
                                        <div class="product-columns d-flex justify-content-between">
                                            <div class="proRows">
                                                @if ($orderlineentries[$i]->comments != '')
                                                    <strong>Comments:</strong><span
                                                        style="font-weight: normal;">{{ $orderlineentries[$i]->comments }}</span>
                                                @endif
                                            </div>
                                            <div class="quantityRows">
                                                <span></span>
                                            </div>
                                            <div class="amountRows">
                                                <span></span>
                                            </div>
                                        </div>
                                        <hr>
                                    @endif
                                @endfor
                            @endif

                            <div class="product-columns d-flex justify-content-between">
                                <div class="proRows">
                                    <strong> </strong>
                                </div>
                                <div class="quantityRows">
                                    <strong><span>Sub Total : </span></strong>
                                </div>
                                <div class="amountRows">
                                    <strong><span>$ {{ $queryresult->subTotal }}</span></strong>
                                </div>
                            </div>
                            <div class="product-columns d-flex justify-content-between">
                                <div class="proRows">
                                    <strong> </strong>
                                </div>
                                <div class="quantityRows">
                                    <strong><span>Discount : </span></strong>
                                </div>
                                <div class="amountRows">
                                    <strong><span>$ {{ $queryresult->discountAmount }}</span></strong>
                                </div>
                            </div>
                            <div class="product-columns d-flex justify-content-between">
                                <div class="proRows">
                                    <strong> </strong>
                                </div>
                                <div class="quantityRows">
                                    <strong><span>Tax Amount : </span></strong>
                                </div>
                                <div class="amountRows">
                                    <strong><span>$ {{ $queryresult->taxAmount }}</span></strong>
                                </div>
                            </div>
                            @if ($queryresult->deliveryType != 'pickup')
                                <div class="product-columns d-flex justify-content-between">
                                    <div class="proRows">
                                        <strong> </strong>
                                    </div>
                                    <div class="quantityRows">
                                        <strong><span>Delivery Charges : </span></strong>
                                    </div>
                                    <div class="amountRows">
                                        <strong><span>$ {{ $queryresult->deliveryCharges }}</span></strong>
                                    </div>
                                </div>
                            @endif
                            <hr>
                            <div class="product-columns d-flex justify-content-between">
                                <div class="proRows">
                                    <strong> </strong>
                                </div>
                                <div class="quantityRows">
                                    <strong><span>Grand Total : </span></strong>
                                </div>
                                <div class="amountRows">
                                    <strong><span>$ {{ $queryresult->grandTotal }}</span></strong>
                                </div>
                            </div>
                        </div>

                    </div>


                </div>
                <div class="card-footer text-right">

                </div>
            </div>
        @endif
    </div>
@endsection
@push('scripts')
    <script type="text/javascript" src="{{ asset('theme/init_site/orders/view.js?v=' . time()) }}"></script>
@endpush
