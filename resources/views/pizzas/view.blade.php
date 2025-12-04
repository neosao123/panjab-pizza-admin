@extends('template.master', ['pageTitle' => 'Pizza View'])
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
		.form-check-container {
			display: flex;
			flex-wrap: wrap;  /* Ensures that checkboxes wrap when space runs out */
			gap: 15px;        /* Space between the checkboxes */
		}

		.form-check {
			display: inline-block;  /* Ensure each checkbox and label are displayed inline */
			margin-bottom: 10px;     /* Vertical margin between rows of checkboxes */
		}

		.form-check-input {
			margin-right: 10px;  /* Space between the checkbox and the label */
		}

		.form-check-label {
			display: inline-block;  /* Ensures the label aligns with the checkbox */
			white-space: nowrap;    /* Prevents text wrapping if the label is long */
		}

		@media (max-width: 768px) {
			.form-check {
				flex: 1 1 100%;  /* Stack checkboxes vertically on smaller screens */
			}
		}
    </style>
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Pizza</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/pizzas/list') }}">Pizza</a></li>
                            <li class="breadcrumb-item">View</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <?php if ($viewRights == 1) { ?>
            <div class="col-7 align-self-center ">
                <a href="{{ url('/pizzas/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
            </div>
            <?php } ?>
        </div>
    </div>
    @if ($queryresult)
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
              
                    <form  id="inFrom" method="post"
                        enctype="multipart/form-data">
                       
                        <div class="row">
                            
                            <input type="hidden" name="code" id="code" value="{{ $queryresult->code }}" readonly>
                            <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                            <div class="col-md-6 form-group">
                                <label> Name : </label>
                                <input type="text" id="name" name="name" class="form-control-line" value="{{ $queryresult->pizza_name }}"readonly>
                               
                            </div>
						
                            <div class="col-md-3 form-group">
                                <label>Sub-title :</label>
                                <input type="text" id="subtitle" name="subtitle" class="form-control-line"
                                    value="{{ $queryresult->pizza_subtitle }}" maxlength='150'  readonly/>
                               
                            </div>
                           <div class="col-md-3 form-group">
								<label>Category :</label>
								<div class="form-group">
									 <input type="text" id="subtitle" name="subtitle" class="form-control-line"
                                    value="{{$queryresult->category_name}}" readonly/>
                               
								</div>
							</div>
							<div class="col-md-12 form-group">
                                <label> Description : </label>
                                <textarea  name="description" class="form-control" rows="4" maxlength='300'
                                    data-parsley-minlength="2" data-parsley-minlength-message="You need to enter at least 2 characters"
                                    data-parsley-trigger="change">{{ $queryresult->description }}</textarea>
                                <span class="text-danger">
                                    {{ $errors->first('description') }}
                                </span>
                            </div>
                            @php
                                $cnt = 0;
                                $pizza_prices = json_decode($queryresult->pizza_prices, true);
                            @endphp

                            @foreach ($pizzaPrices as $item)
                                @php
                                    $price = $item->price; // Set the default price from the table
                                    if ($pizza_prices) {
                                        foreach ($pizza_prices as $price_item) {
                                            // Only update the price if the shortcode matches
                                            if ($item->shortcode == $price_item['shortcode']) {
                                                $price = $price_item['price'];
                                                break; // Break out of the loop once a match is found
                                            }
                                        }
                                    }
                                @endphp
                                <div class="col-sm-6 col-md-4 col-lg-3 form-group">
                                    <label>Price for {{ $item->size }}:</label>
                                 
                                    <input type="text" id="price_{{ $item->size }}"
                                        name="pizzaPrice[{{ $cnt }}][price]" step="0.01" min="0"
                                        max="9999" class="form-control-line" value="{{ $price }}">
                                </div>
                                @php
                                    $cnt++;
                                @endphp
                            @endforeach

					   <div class="col-md-4 form-group">
							<label>Cheese: </label>
							<div class="form-group">
								 <input type="text" id="cheese" name="chesse" class="form-control-line"
                                    value="{{(json_decode($queryresult->cheese)->title ?? '')}}" readonly/>
								
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Crust: </label>
							<div class="form-group">
								<input type="text" id="crust" name="crust" class="form-control-line"
                                    value="{{(json_decode($queryresult->crust)->title ?? '')}}" readonly/>
							
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Crust Type: </label>
							<div class="form-group">
								<input type="text" id="crustType" name="crustType" class="form-control-line"
                                    value="{{(json_decode($queryresult->crust_type)->title ?? '')}}" readonly/>							
								
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Special Base: </label>
							<div class="form-group">
								<input type="text" id="specialBase" name="specialBase" class="form-control-line"
                                    value="{{(json_decode($queryresult->special_base)->title ?? '')}}" readonly/>							
								
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Spices: </label>
							<div class="form-group">
								<input type="text" id="spices" name="spices" class="form-control-line"
                                    value="{{(json_decode($queryresult->spices)->title ?? '')}}" readonly/>							
								
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Sauce: </label>
							<div class="form-group">
								<input type="text" id="sauce" name="sauce" class="form-control-line"
                                    value="{{(json_decode($queryresult->sauce)->title ?? '')}}" readonly/>							
								
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Cook: </label>
							<div class="form-group">
								<input type="text" id="cook" name="cook" class="form-control-line"
                                    value="{{(json_decode($queryresult->cook)->title ?? '')}}" readonly/>							
								
							</div>
						</div>

						<div class="col-md-12 form-group">
							<label>Free Topping: </label>
							<div class="form-check-container">
								@if(!empty($toppingFree))
									@php
										// Decode the JSON data for selected free toppings and get only the codes
										$selectedFreeToppings = collect(json_decode($queryresult->topping_as_free ?? '[]'))->pluck('code')->all();
									@endphp

									@foreach ($toppingFree as $topping)
										<div class="custom-control custom-checkbox">
											<input type="checkbox" class="custom-control-input" 
												   id="free_topping_{{ $topping->code }}" 
												   name="free_topping[]"
												   value="{{ json_encode(['code' => $topping->code, 'title' => $topping->toppingsName]) }}"
												   {{ in_array($topping->code, $selectedFreeToppings) ? 'checked' : '' }} disabled>
											<label class="custom-control-label" for="free_topping_{{ $topping->code }}">
												{{ $topping->toppingsName }}
											</label>
										</div>
									@endforeach
								@endif
							</div>
						</div>



						<div class="col-md-12 form-group">
							<label>Topping as 1: </label>
							<small class="text-danger">(The price for Topping as 1 is zero for selected toppings.)</small>
							<div class="form-check-container">
								@if(!empty($toppingAsOne))
									@php
										// Decode the JSON data for selected toppings one and get only the codes
										$selectedToppingsOne = collect(json_decode($queryresult->topping_as_1 ?? '[]'))->pluck('code')->all();
									@endphp

									@foreach ($toppingAsOne as $topping)
										<div class="custom-control custom-checkbox">
											<input type="checkbox" class="custom-control-input" id="topping_as_one_{{ $topping->code }}" name="topping_as_one[]"
												value="{{ json_encode(['code' => $topping->code, 'title' => $topping->toppingsName]) }}" {{ in_array($topping->code, $selectedToppingsOne) ? 'checked' : '' }} disabled>
											<label class="custom-control-label" for="topping_as_one_{{ $topping->code }}">
												{{ $topping->toppingsName }}
											</label>
										</div>
									@endforeach
								@endif
							</div>
						</div>

						<div class="col-md-12 form-group">
							<label>Topping as 2: </label>
							<small class="text-danger">(The price for Topping as 2 is zero for selected toppings)</small>
							<div class="form-check-container">
								@if(!empty($toppingAsTwo))
								    @php
										// Decode the JSON data for selected toppings one and get only the codes
										$selectedToppingsTwo = collect(json_decode($queryresult->topping_as_2 ?? '[]'))->pluck('code')->all();
									@endphp
								  @foreach ($toppingAsTwo as $topping)
										<div class="custom-control custom-checkbox">
											<input type="checkbox" class="custom-control-input" id="topping_as_two_{{ $topping->code }}" name="topping_as_two[]"
												value="{{ json_encode(['code' => $topping->code, 'title' => $topping->toppingsName]) }}" {{ in_array($topping->code, $selectedToppingsTwo) ? 'checked' : '' }} disabled>
											<label class="custom-control-label" for="topping_as_two_{{ $topping->code }}">
												{{ $topping->toppingsName }}
											</label>
										</div>
									@endforeach
								@endif
							</div>
						</div>
                   
                            @if (!empty($queryresult->pizza_image))
                                <div class="col-md-3 form-group mb-3" id="eImage">
                                    <label class="form-label" for="form-wizard-progress-wizard-pizzaimage">Pizza
                                image: </label>
									<img class="img-thumbnail mb-2" width="100" height="100" id="showProfileImg"
                                        src="{{ url('uploads/pizzas/' . $queryresult->pizza_image) . '?v=' . time() }}"
                                        data-src="">
                                    
                                </div>
                            @endif

                         
                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="isActive" name="isActive"
                                        value="1" {{ $queryresult->isActive == 1 ? 'checked' : '' }} disabled>
                                    <label class="custom-control-label" for="isActive"> Status</label>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('scripts')
 <script type="text/javascript" src="{{ asset('theme/js/summernote/dist/summernote-bs4.min.js') }}"></script>
 <script>
     $(document).ready(function () {
		 	
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
    $('#description').summernote('disable');
	 });
  </script>
@endpush
