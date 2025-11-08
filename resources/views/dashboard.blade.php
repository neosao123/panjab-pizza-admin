@extends('template.master', ['pageTitle'=>"dashboard"])
@push('styles')
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Dashboard</h4>
         </div>
        <div class="col-7 align-self-center">
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card bg-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="m-r-10">
                            <h1 class="m-b-0"><i class="mdi mdi-account-switch text-white"></i></h1>
                        </div>
                        <div>
                            <h6 class="font-14 text-white m-b-5 op-7">Cashier</h6>
                        </div>
                        <div class="ml-auto">
                            <div class="crypto">
                                <h4 class="text-white font-medium m-b-0">{{$cashiers}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right">
                        <div class="col-12"><a class="loadOrders text-white" data-id="dbs" href="{{ url('users/list') }}">View</a></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card bg-alternate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="m-r-10">
                            <h1 class="m-b-0"><i class="mdi mdi-vector-combine text-white"></i></h1>
                        </div>
                        <div>
                            <h6 class="font-14 text-white m-b-5 op-7">Specials</h6>
                        </div>
                        <div class="ml-auto">
                            <div class="crypto">
                                <h4 class="text-white font-medium m-b-0">{{$specials}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right text-white">
                        <div class="col-12"><a class="loadOrders text-white" data-id="" href="{{ url('specialoffer/list') }}">View</a></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card bg-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="m-r-10">
                            <h1 class="m-b-0"><i class="mdi mdi-account-switch text-white"></i></h1>
                        </div>
                        <div>
                            <h6 class="font-14 text-white m-b-5 op-7">Customers</h6>
                        </div>
                        <div class="ml-auto">
                            <div class="crypto">
                                <h4 class="text-white font-medium m-b-0">{{$customers}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right text-white">
                        <div class="col-12"><a class="text-white" data-id="dbs" href="{{ url('customers/list') }}">View</a></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card bg-alternate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="m-r-10">
                            <h1 class="m-b-0"><i class="mdi mdi-view-list text-white"></i></h1>
                        </div>
                        <div>
                            <h6 class="font-14 text-white m-b-5 op-7">Total Orders</h6>
                        </div>
                        <div class="ml-auto">
                            <div class="crypto">
                                <h4 class="text-white font-medium m-b-0">{{$storeorders+$onlineorders}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right text-white">
                        <div class="col-12"><a class="text-white" data-id="dbs" href="{{url('orders/list') }}">View</a></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card bg-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="m-r-10">
                            <h1 class="m-b-0"><i class="mdi mdi-view-list text-white"></i></h1>
                        </div>
                        <div>
                            <h6 class="font-14 text-white m-b-5 op-7">Total Store Orders</h6>
                        </div>
                        <div class="ml-auto">
                            <div class="crypto">
                                <h4 class="text-white font-medium m-b-0">{{$storeorders}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right text-white">
                        <div class="col-12"><a class="text-white" data-id="dbs" href="{{url('orders/list') }}">View</a></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card bg-alternate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="m-r-10">
                            <h1 class="m-b-0"><i class="mdi mdi-view-list text-white"></i></h1>
                        </div>
                        <div>
                            <h6 class="font-14 text-white m-b-5 op-7">Total Online Orders</h6>
                        </div>
                        <div class="ml-auto">
                            <div class="crypto">
                                <h4 class="text-white font-medium m-b-0">{{$onlineorders}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right text-white">
                        <div class="col-12"><a class="text-white" data-id="dbs" href="{{url('orders/list') }}">View</a></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card bg-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="m-r-10">
                            <h1 class="m-b-0"><i class="mdi mdi-food text-white"></i></h1>
                        </div>
                        <div>
                            <h6 class="font-14 text-white m-b-5 op-7">Toppings</h6>
                        </div>
                        <div class="ml-auto">
                            <div class="crypto">
                                <h4 class="text-white font-medium m-b-0">{{$toppings}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right text-white">
                        <div class="col-12"><a class="text-white" data-id="dbs" href="{{ url('toppings/list') }}">View</a></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card bg-alternate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="m-r-10">
                            <h1 class="m-b-0"><i class="mdi mdi-food text-white"></i></h1>
                        </div>
                        <div>
                            <h6 class="font-14 text-white m-b-5 op-7">Sides</h6>
                        </div>
                        <div class="ml-auto">
                            <div class="crypto">
                                <h4 class="text-white font-medium m-b-0">{{$sides}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right text-white">
                        <div class="col-12"><a class="text-white" data-id="dbs" href="{{ url('sides/list') }}">View</a></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card bg-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="m-r-10">
                            <h1 class="m-b-0"><i class="mdi mdi-food text-white"></i></h1>
                        </div>
                        <div>
                            <h6 class="font-14 text-white m-b-5 op-7">Poutines</h6>
                        </div>
                        <div class="ml-auto">
                            <div class="crypto">
                                <h4 class="text-white font-medium m-b-0">{{$poutine}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right text-white">
                        <div class="col-12"><a class="text-white" data-id="dbs" href="{{ url('sides/list') }}">View</a></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card bg-alternate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="m-r-10">
                            <h1 class="m-b-0"><i class="mdi mdi-food text-white"></i></h1>
                        </div>
                        <div>
                            <h6 class="font-14 text-white m-b-5 op-7">Subs</h6>
                        </div>
                        <div class="ml-auto">
                            <div class="crypto">
                                <h4 class="text-white font-medium m-b-0">{{$subs}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row text-right text-white">
                        <div class="col-12"><a class="text-white" data-id="dbs" href="{{ url('sides/list') }}">View</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')

@endpush