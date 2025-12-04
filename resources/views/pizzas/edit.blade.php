@extends('template.master', ['pageTitle' => 'Pizzas Update'])
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
                            <li class="breadcrumb-item">Edit</li>
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
                    <form action="{{ url('/pizzas/update') }}" id="inFrom" method="post"
                        enctype="multipart/form-data" data-parsley-validate="">
                        @csrf
                        <div class="row">
                            
                            <input type="hidden" name="code" id="code" value="{{ $queryresult->code }}" readonly>
                            <input type="hidden" name="id" value="{{ $queryresult->id }}" readonly>
                            <div class="col-md-6 form-group">
                                <label> Name : <span style="color:red">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required
                                    value="{{ $queryresult->pizza_name }}" data-parsley-required-message="Name is required"
                                    maxlength='150' data-parsley-minlength="3"
                                    data-parsley-minlength-message="You need to enter at least 3 characters"
                                    data-parsley-trigger="change">
                                <span class="text-danger">
                                    @error('name')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
						
                            <div class="col-md-3 form-group">
                                <label>Sub-title :</label>
                                <input type="text" id="subtitle" name="subtitle" class="form-control"
                                    value="{{ $queryresult->pizza_subtitle }}" maxlength='150' data-parsley-minlength="3"
                                    data-parsley-minlength-message="You need to enter at least 3 characters"
                                    data-parsley-trigger="change" />
                                <span class="text-danger">
                                    @error('subtitle')
                                        {{ $message }}
                                    @enderror
                                </span>
                            </div>
                           <div class="col-md-3 form-group">
								<label>Category : <span style="color:red">*</span></label>
								<div class="form-group">
									<select class="select2 form-control custom-select" style="width: 100%;" name="category" id="category" required data-parsley-required-message="Category is required.">
                                         <option value="{{$queryresult->category_code}}">{{$queryresult->category_name}}</option>
									</select>
									 <span class="text-danger">
                                    @error('category')
                                        {{ $message }}
                                    @enderror
                                </span>
								</div>
							</div>
							 <div class="col-md-12 form-group">
                                <label> Description : </label>
                                <textarea name="description" class="form-control" rows="4"
                                    data-parsley-minlength="2" data-parsley-minlength-message="You need to enter at least 2 characters"
                                    data-parsley-trigger="change">{{ $queryresult->description }}</textarea>
                                <span class="text-danger">
                                    {{ $errors->first('description') }}
                                </span>
                            </div>
						@php
							$cnt = 0;
							$pizza_prices = json_decode($queryresult->pizza_prices, true) ?? []; 
						@endphp

						@foreach ($pizzaPrices as $item)
							@php
								$price = $item->price; // Set the default price from the table
								$isDefault = 0; // Default is not selected

								// Check if there’s a match in pizza_prices to set the default
								foreach ($pizza_prices as $price_item) {
									if ($item->shortcode === $price_item['shortcode']) {
										$price = $price_item['price'];
										$isDefault = $price_item['isdefault'] ?? 0;
										break; // Stop once we find the match
									}
								}
							@endphp

							<div class="col-sm-6 col-md-4 col-lg-3 form-group">
								<label>Price for {{ $item->size }}: <span style="color:red">*</span></label>
								
								<input type="hidden" name="pizzaPrice[{{ $cnt }}][size]" value="{{ $item->size }}">
								<input type="hidden" name="pizzaPrice[{{ $cnt }}][shortcode]" value="{{ $item->shortcode }}">

								<!-- Price input -->
								<input type="number" name="pizzaPrice[{{ $cnt }}][price]" step="0.01" min="0" max="9999"
									   class="form-control" value="{{ $price }}" required>
								
								<!-- Radio button for default selection -->
								<div class="custom-control custom-radio mt-2 d-none">
									<input class="custom-control-input" type="radio" 
										   id="isDefault_{{ $item->shortcode }}" 
										   name="isDefault" 
										   value="{{ $item->shortcode }}"
										   {{ $isDefault ? 'checked' : '' }}>
									<label class="custom-control-label" for="isDefault_{{ $item->shortcode }}">
										Set as default
									</label>
								</div>
							</div>

							@php $cnt++; @endphp
						@endforeach


					   <div class="col-md-4 form-group">
							<label>Cheese: <span style="color:red">*</span></label>
							<div class="form-group">
								<select class="select2 form-control" id="cheese" name="cheese" style="width: 100%;" required data-parsley-required-message="Cheese is required">
									<option value="">Select</option>
									@if(!empty($cheese))
										@foreach ($cheese as $item)
											 <option value="{{ json_encode(['code' => $item->code, 'title' => $item->cheese]) }}"
												{{ (json_decode($queryresult->cheese)->code ?? '') == $item->code ? 'selected' : '' }}>
												{{ $item->cheese }}
										@endforeach
									@endif
								</select>
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Crust: <span style="color:red">*</span></label>
							<div class="form-group">
								<select class="select2 form-control" id="crust" name="crust" style="width: 100%;" required data-parsley-required-message="Crust is required">
									<option value="">Select</option>
									@if(!empty($crust))
										@foreach ($crust as $item)
											<option value="{{ json_encode(['code' => $item->code, 'title' => $item->crust]) }}" 
												{{ (json_decode($queryresult->crust)->code ?? '') == $item->code ? 'selected' : '' }}>
												{{ $item->crust }}
											</option>
										@endforeach
									@endif
								</select>
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Crust Type: <span style="color:red">*</span></label>
							<div class="form-group">
								<select class="select2 form-control" id="crustType" name="crustType" style="width: 100%;" required data-parsley-required-message="Crust Type is required">
									<option value="">Select</option>
									@if(!empty($crustType))
										@foreach ($crustType as $item)
											<option value="{{ json_encode(['code' => $item->code, 'title' => $item->crustType]) }}" 
												{{ (json_decode($queryresult->crust_type)->code ?? '') == $item->code ? 'selected' : '' }}>
												{{ $item->crustType }}
											</option>
										@endforeach
									@endif
								</select>
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Special Base: <span style="color:red">*</span></label>
							<div class="form-group">
								<select class="select2 form-control" id="specialBase" name="specialBase" style="width: 100%;" required data-parsley-required-message="Special Base is required">
									<option value="">Select</option>
									@if(!empty($specialBase))
										@foreach ($specialBase as $item)
											<option value="{{ json_encode(['code' => $item->code, 'title' => $item->specialbase]) }}" 
												{{ (json_decode($queryresult->special_base)->code ?? '') == $item->code ? 'selected' : '' }}>
												{{ $item->specialbase }}
											</option>
										@endforeach
									@endif
								</select>
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Spices: <span style="color:red">*</span></label>
							<div class="form-group">
								<select class="select2 form-control" id="spices" name="spices" style="width: 100%;" required data-parsley-required-message="Spices is required">
									<option value="">Select</option>
									@if(!empty($spices))
										@foreach ($spices as $item)
											<option value="{{ json_encode(['code' => $item->code, 'title' => $item->spicy]) }}" 
												{{ (json_decode($queryresult->spices)->code ?? '') == $item->code ? 'selected' : '' }}>
												{{ $item->spicy }}
											</option>
										@endforeach
									@endif
								</select>
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Sauce: <span style="color:red">*</span></label>
							<div class="form-group">
								<select class="select2 form-control" id="sauce" name="sauce" style="width: 100%;" required data-parsley-required-message="Sauce is required">
									<option value="">Select</option>
									@if(!empty($sauce))
										@foreach ($sauce as $item)
											<option value="{{ json_encode(['code' => $item->code, 'title' => $item->sauce]) }}" 
												{{ (json_decode($queryresult->sauce)->code ?? '') == $item->code ? 'selected' : '' }}>
												{{ $item->sauce }}
											</option>
										@endforeach
									@endif
								</select>
							</div>
						</div>

						<div class="col-md-4 form-group">
							<label>Cook: <span style="color:red">*</span></label>
							<div class="form-group">
								<select class="select2 form-control" id="cook" name="cook" style="width: 100%;" required data-parsley-required-message="Cook is required">
									<option value="">Select</option>
									@if(!empty($cook))
										@foreach ($cook as $item)
											<option value="{{ json_encode(['code' => $item->code, 'title' => $item->cook]) }}" 
												{{ (json_decode($queryresult->cook)->code ?? '') == $item->code ? 'selected' : '' }}>
												{{ $item->cook }}
											</option>
										@endforeach
									@endif
								</select>
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
												   {{ in_array($topping->code, $selectedFreeToppings) ? 'checked' : '' }} onclick="return false;">
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
												value="{{ json_encode(['code' => $topping->code, 'title' => $topping->toppingsName,'price' => 0]) }}" {{ in_array($topping->code, $selectedToppingsOne) ? 'checked' : '' }}>
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
												value="{{ json_encode(['code' => $topping->code, 'title' => $topping->toppingsName,'price' => 0]) }}" {{ in_array($topping->code, $selectedToppingsTwo) ? 'checked' : '' }}>
											<label class="custom-control-label" for="topping_as_two_{{ $topping->code }}">
												{{ $topping->toppingsName }}
											</label>
										</div>
									@endforeach
								@endif
							</div>
						</div>
                            <div class="col-md-3 form-group">
                                <label class="form-label" for="form-wizard-progress-wizard-pizzaimage">Pizza
                                image: </label>
                                <input type="file" id="file" class="form-control " name="pizzaimage"
                                    accept=".jpg, .jpeg, .png">

                            </div>

                            @if (!empty($queryresult->pizza_image))
                                <div class="col-md-3 form-group mb-3" id="eImage">
                                    <img class="img-thumbnail mb-2" width="100" height="100" id="showProfileImg"
                                        src="{{ url('uploads/pizzas/' . $queryresult->pizza_image) . '?v=' . time() }}"
                                        data-src="">
                                    <a class="btn btn-danger text-white"
                                        onclick="deleteImage('{{ $queryresult->code }}','{{ $queryresult->pizza_image }}');"><i
                                            class="fa fa-trash"></i></a>
                                </div>
                            @endif

                            <div class="col-md-3 mb-3 d-none" id="eDisImage">
                                <img class="img-thumbnail mb-2" width="100" height="100" id="showImage"
                                    src="" data-src="">
                            </div>

                            <div class="col-md-12 form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="isActive" name="isActive"
                                        value="1" {{ $queryresult->isActive == 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="isActive"> Status</label>
                                </div>
                            </div>

                            <div class="col-sm-12 form-group">
                                <button class="btn btn-primary" type="submit" id="submit"> Update </button>
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
    <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/init_site/pizzas/edit.js?v=' . time()) }}"></script>

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
