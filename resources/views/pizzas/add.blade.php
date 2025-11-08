@extends('template.master', ['pageTitle' => 'Pizzas add'])
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
                            <li class="breadcrumb-item">Add</li>
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
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h5 class="mb-0" data-anchor="data-anchor">Add</h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <form id="inFrom" action="{{ url('/pizzas/store') }}" method="post" enctype="multipart/form-data"
                    data-parsley-validate="">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Name : <span style="color:red">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required
                                value="{{ old('name') }}" data-parsley-required-message="Name is required" maxlength='150'
                                data-parsley-minlength="3"
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
                                value="{{ old('subtitle') }}" maxlength='150' data-parsley-minlength="3"
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

                                </select>
                            </div>
						</div>
						<div class="col-md-12 form-group">
                            <label> Description : </label>
                            <textarea id="description" name="description" class="form-control summernote" rows="4"
                                data-parsley-minlength="2" data-parsley-minlength-message="You need to enter at least 2 characters"
                                data-parsley-trigger="change">{{ old('description') }}</textarea>
                            <span class="text-danger">
                                {{ $errors->first('description') }}
                            </span>
                        </div>
                        @php
                            $cnt = 0;
                        @endphp

                        @foreach ($pizzaPrices as $item)
                            <div class="col-sm-6 col-md-4 col-lg-3 form-group">
                                <label>Price for {{ $item->size }}: <span style="color:red">*</span></label>
                                <input type="hidden" id="size_{{ $item->size }}"
                                    name="pizzaPrice[{{$cnt}}][size]" value="{{ $item->size }}" readonly>
                                <input type="hidden" id="size_{{ $item->shortcode }}"
                                    name="pizzaPrice[{{$cnt}}][shortcode]" value="{{ $item->shortcode }}" readonly>
                                <input type="number" id="{{ $item->size }}"
                                    name="pizzaPrice[{{$cnt}}][price]" step="0.01" min="0"
                                    max="9999" class="form-control" value="{{ $item->price }}" required
                                    data-parsley-required-message="Price is required">
                              
								  <!-- Radio button to determine the default pizza size -->
								<div class="custom-control custom-radio mt-2 d-none">
									<input class="custom-control-input" type="radio" id="isDefault_{{ $item->shortcode }}" 
										name="pizzaPrice[{{$cnt}}][isDefault]" 
										value="1"
										{{ old('pizzaPrice.'.$cnt.'.isDefault', $item->isDefault ?? 0) == 1 ? 'checked' : '' }}>
									<label class="custom-control-label" for="isDefault_{{ $item->shortcode }}">
										Set as default
									</label>
								</div>

								<span class="text-danger">
                                    @error("pizzaPrice[{{$cnt}}][price]")
                                        {{ $message }}
                                    @enderror 
                                </span>
                            </div>
                            @php
                                $cnt++;
                            @endphp
                        @endforeach           
                        <div class="col-md-4 form-group">
								<label>Cheese: <span style="color:red">*</span></label>
								<div class="form-group">
									<select class="select2 form-control" id="cheese" name="cheese" style="width: 100%;" required data-parsley-required-message="Cheese is required">
										<option value="">Select</option>
										@if(!empty($cheese))
											@foreach ($cheese as $item)
												<option value="{{ json_encode(['code' => $item->code, 'title' => $item->cheese]) }}"
													{{ old('cheese') == json_encode(['code' => $item->code, 'title' => $item->cheese]) ? 'selected' : '' }}>
													{{ $item->cheese }}
												</option>
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
													{{ old('crust') == json_encode(['code' => $item->code, 'title' => $item->crust]) ? 'selected' : '' }}>
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
													{{ old('crustType') == json_encode(['code' => $item->code, 'title' => $item->crustType]) ? 'selected' : '' }}>
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
													{{ old('specialBase') == json_encode(['code' => $item->code, 'title' => $item->specialbase]) ? 'selected' : '' }}>
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
													{{ old('spices') == json_encode(['code' => $item->code, 'title' => $item->spicy]) ? 'selected' : '' }}>
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
													{{ old('sauce') == json_encode(['code' => $item->code, 'title' => $item->sauce]) ? 'selected' : '' }}>
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
													{{ old('cook') == json_encode(['code' => $item->code, 'title' => $item->cook]) ? 'selected' : '' }}>
													{{ $item->cook }}
												</option>
											@endforeach
										@endif
									</select>
								</div>
							</div>

							<!-- Add more fields as necessary -->

						<div class="col-md-12 form-group">
							<label>Free Topping: </label>
							<div class="form-check-container">
								@if (!empty($toppingFree))
									@foreach ($toppingFree as $topping)
										<div class="custom-control custom-checkbox">
											<input type="checkbox" class="custom-control-input" id="free_topping_{{ $topping->code }}" name="free_topping[]"
												value="{{ json_encode(['code' => $topping->code, 'title' => $topping->toppingsName]) }}" checked onclick="return false;">
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
								@foreach ($toppingAsOne as $topping)
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input" id="topping_as_one_{{ $topping->code }}" name="topping_as_one[]"
											value="{{ json_encode(['code' => $topping->code, 'title' => $topping->toppingsName, 'price' => 0]) }}"
											{{ in_array(json_encode(['code' => $topping->code, 'title' => $topping->toppingsName, 'price' => 0]), old('topping_as_one', [])) ? 'checked' : '' }}>
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
								@foreach ($toppingAsTwo as $topping)
									<div class="custom-control custom-checkbox">
										<input type="checkbox" class="custom-control-input" id="topping_as_two_{{ $topping->code }}" name="topping_as_two[]"
											value="{{ json_encode(['code' => $topping->code, 'title' => $topping->toppingsName, 'price' => 0]) }}"
											{{ in_array(json_encode(['code' => $topping->code, 'title' => $topping->toppingsName, 'price' => 0]), old('topping_as_two', [])) ? 'checked' : '' }}>
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
                                image:</label>
                            <input type="file" id="file" class="form-control" name="pizzaimage"
                                accept=".jpg, .jpeg, .png">

                        </div>

                        <div class="col-md-3" id="eImage">
                            <img class="img-thumbnail mb-2" width="100" height="100" id="showImage"
                                src="{{ asset('images/default-user.png') }}" data-src="">
                        </div>

                        <div class="col-md-12 form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="isActive" name="isActive"
                                    value="1" checked>
                                <label class="custom-control-label" for="isActive"> Status</label>
                            </div>
                        </div>

                        
                        <div class="col-md-12 form-group">
                            <button class="btn btn-primary" type="submit" id="submit"> Submit </button>
                            <button type="button" class="btn btn-danger"
                                onclick="window.location.reload();">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')

    <script type="text/javascript" src="{{ asset('theme/js/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/init_site/pizzas/add.js?v=' . time()) }}"></script>

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
