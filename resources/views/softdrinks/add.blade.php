@extends('template.master', ['pageTitle' => 'Soft Drink Add'])

@push('styles')
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


    </style>
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/js/summernote/dist/summernote-bs4.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Soft Drink Add</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ url('/softdrinks/list') }}">Soft Drink</a></li>
                            <li class="breadcrumb-item active">Add</li>
                        </ol>
                    </nav>
                </div>
            </div>
            @if ($viewRights == 1)
                <div class="col-7 align-self-center">
                    <a href="{{ url('/softdrinks/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
                </div>
            @endif
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h5 class="mb-0">Add</h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form id="softdrinkForm" action="{{ url('/softdrinks/store') }}" method="post"
                    enctype="multipart/form-data" data-parsley-validate>
                    @csrf
                    <input type="hidden" name="code" value="">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" id="nonzero" value="0">

                    <div class="row">
                        <!-- Name -->
                        <div class="col-sm-6 form-group">
                            <label>Name : <span style="color:red">*</span></label>
                            <input type="text" id="softdrink" name="softdrink" class="form-control" required
                                value="{{ old('softdrink') }}" data-parsley-required-message="Soft Drink name is required"
                                maxlength='150' data-parsley-minlength="3"
                                data-parsley-minlength-message="You need to enter at least 3 characters"
                                data-parsley-trigger="change">
                            <span class="text-danger">
                                @error('softdrink')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <!-- Price -->
                        <div class="col-sm-6 form-group">
                            <label>Price : <span style="color:red">*</span></label>
                            <input type="number" id="price" name="price" step="0.01" min="0.01" max="9999999"
                                class="form-control" value="{{ old('price') }}" required
                                data-parsley-required-message="Price is required" data-parsley-trigger="change"
                                data-parsley-gt="#nonzero">
                            <span class="text-danger">
                                @error('price')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <!-- Drinks Count -->
                        <div class="col-sm-6 form-group">
                            <label>Drinks Count : <span style="color:red">*</span></label>
                            <input type="number" id="drinksCount" name="drinksCount" step="0.01" min="1"
                                max="9999999" class="form-control" value="{{ old('drinksCount') }}" required
                                data-parsley-required-message="Drinks Count is required" data-parsley-trigger="change"
                                data-parsley-gt="#nonzero">
                            <span class="text-danger">
                                @error('drinksCount')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <!-- Type -->
                        <div class="col-md-6 form-group">
                            <label>Type : <span style="color:red">*</span></label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="">Select</option>
                                <option value="pop" {{ old('type') == 'pop' ? 'selected' : '' }}>Pop</option>
                                <option value="bottle" {{ old('type') == 'bottle' ? 'selected' : '' }}>Bottle</option>
                                 <option value="juice" {{ old('type') == 'juice' ? 'selected' : '' }}>Juice</option>
                            </select>
                            <span class="text-danger">
                                @error('drinksType')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description"  rows="3" class="form-control ">{{ old('description') }}</textarea>
                            <span class="text-danger">
                                @error('description')
                                    {{ $message }}
                                @enderror
                            </span>
                        </div>

                        <!-- Image -->
                        <div class="col-md-6 form-group">
                            <label>Soft Drink Image</label>
                            <input type="file" id="file" class="form-control" name="softDrinkImage"
                                accept=".jpg, .jpeg, .png">
                        </div>

                        <!-- Submit -->
                        <div class="col-sm-12 form-group">
                            <button class="btn btn-primary" type="submit"> Save </button>
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
