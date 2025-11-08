@extends('template.master', ['pageTitle' => 'Customers View'])
@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Customers View</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/customers/list') }}">Customers</a></li>
                        <li class="breadcrumb-item">View</li>
                    </ol>
                </nav>
            </div>
        </div>
        <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/customers/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
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
                    <div class="col-md-6 form-group">
                        <label>Customer Name</label>
                        <input type="text" id="customerName" name="customerName" class="form-control-line" value="{{ $queryresult->customerName }}" readonly>
                    </div>
					
					<div class="col-md-6 form-group">
                        <label>Email</label>
                        <input type="text" id="email" name="email" class="form-control-line" value="{{ $queryresult->email }}" readonly>

                    </div>
					<div class="col-md-6 form-group">
                        <label>Mobile Number</label>
                        <input type="text" id="mobileNumber" name="mobileNumber" class="form-control-line" value="{{ $queryresult->mobileNumber }}" readonly>

                    </div>
				
                    @if (!empty($queryresult->profilePhoto))
                    <div class="col-md-3 mb-3" id="eImage">
                        <img class="img-thumbnail mb-2" width="100" height="100" src="{{ url('uploads/customer/' . $queryresult->profilePhoto). '?v=' . time() }}" data-src="">
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
            </form>
        </div>
    </div>
	@if($customerAddress && count($customerAddress)>0)
	<div class="card">
	  <div class="card-header">
		<h5 class="mb-0" data-anchor="data-anchor">Customer Address</h5>
	  </div>
	  <div class="card-body">
		<div class="table-responsive">
		  <table class="table table-striped table-bordered nowrap">
			<thead>
			  <tr>
				<th>Sr. No.</th>			
				<th>Street</th>
				<th>City</th>
				<th>Landmark</th>
				<th>Zipcode</th>
			  </tr>
			</thead>
			  <tbody>
			  @php $i = 0; @endphp
				 @foreach($customerAddress as $item)
					<tr>
						<td>{{ $i + 1 }}</td>
						<td>{{$item->street}}</td>
						<td>{{$item->city}}</td>
						<td>{{$item->landmark}}</td>
						<td>{{$item->zipcode}}</td>
					</tr>
				 @endforeach
			  </tbody>
		  </table>
		</div>
	  </div>
	</div>
	@endif
</div>
@endsection