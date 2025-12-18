@extends('template.master', ['pageTitle' => 'Section - Edit'])
@push('styles')
<link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
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

    .line-entry-row {
        border: 1px solid #dee2e6;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 5px;
        background-color: #f8f9fa;
    }

    .image-error {
        color: red;
        font-size: 0.9rem;
        margin-top: 3px;
        display: block;
    }

    .image-preview img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        margin-top: 5px;
        border-radius: 5px;
    }

    .image-preview {
        margin-top: 5px;
    }

    .image-preview .remove-preview {
        margin-top: 3px;
    }
</style>
@endpush

@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Section</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/sections/list') }}">Sections</a></li>
                        <li class="breadcrumb-item">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
        <?php if ($viewRights == 1) { ?>
        <div class="col-7 align-self-center ">
            <a href="{{ url('/sections/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
        </div>
        <?php } ?>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Section</h5>
        </div>
        <div class="card-body">
            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif

            <form id="sectionForm" action="{{ url('/sections/update') }}" method="post" enctype="multipart/form-data"
                data-parsley-validate="">
                @csrf
                <input type="hidden" name="id" value="{{ $queryresult->id }}" />

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Title: <span style="color:red">*</span></label>
                        <input type="text" name="title" class="form-control" required
                            value="{{ old('title') ? old('title') : $queryresult->title }}"
                            data-parsley-required-message="Title is required" data-parsley-minlength="2"
                            data-parsley-minlength-message="You need to enter at least 2 characters"
                            data-parsley-trigger="change">
                        <span class="text-danger">@error('title'){{ $message }}@enderror</span>
                    </div>

                    <div class="col-md-6 form-group">
                        <label>Sub Title:</label>
                        <input type="text" name="subTitle" class="form-control"
                            value="{{ old('subTitle') ? old('subTitle') : $queryresult->subTitle }}">
                        <span class="text-danger">@error('subTitle'){{ $message }}@enderror</span>
                    </div>
                </div>

                <div class="col-sm-12 mt-4">
                    <h5>Section Line Entries (Strict 300×300)</h5>
                    <div id="lineEntriesContainer">
                        @foreach ($lineentries as $index => $data)
                        <div class="line-entry-row" id="lineRow{{ $index }}" data-index="{{ $index }}">
                            <input type="hidden" name="line_id[]" value="{{ $data->id }}" />
                            <div class="row">

                                <!-- Image Input -->
                                <div class="col-md-4 form-group">
                                    <label>Image: <span style="color:red">*</span></label>
                                    <input type="file" name="line_image[]" class="form-control section-image"
                                        accept=".jpg,.jpeg,.png">
                                    <span class="image-error"></span>
                                    <div class="image-preview {{ empty($data->image) ? 'd-none' : '' }}">
                                        @if(!empty($data->image))
                                        <img src="{{ url($data->image) . '?v=' . time() }}" alt="Preview">
                                        @endif
                                        <button type="button" class="btn btn-sm btn-danger remove-preview">Remove</button>
                                    </div>
                                </div>

                                <!-- Title -->
                                <div class="col-md-4 form-group">
                                    <label>Title: <span style="color:red">*</span></label>
                                    <input type="text" name="line_title[]" class="form-control" required
                                        value="{{ $data->title }}" data-parsley-required-message="Title is required"
                                        data-parsley-minlength="2" data-parsley-trigger="change">
                                </div>

                                <!-- Counter -->
                                <div class="col-md-3 form-group">
                                    <label>Counter: <span style="color:red">*</span></label>
                                    <input type="text" name="counter[]" class="form-control" value="{{ $data->counter }}"
                                        required data-parsley-required-message="Counter is required">
                                </div>

                                <div class="col-md-1 form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-block remove-line"
                                        data-row-id="lineRow{{ $index }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-outline-success" id="addLineEntry">
                        <i class="fa fa-plus"></i> Add Line Entry
                    </button>
                </div>

                <div class="col-md-12 form-group mt-4">
                    <button class="btn btn-primary" type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('theme/js/parsely.min.js') }}"></script>
<script src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
<script src="{{ asset('theme/js/select2.min.js') }}"></script>

<script>
$(document).ready(function(){

    // IMAGE PREVIEW + 300x300 VALIDATION
    $(document).on("change", ".section-image", function(){
        let input = this;
        let file = input.files[0];
        let $error = $(this).siblings(".image-error");
        let $previewBox = $(this).siblings(".image-preview");

        $error.text('');
        if(file){
            let img = new Image();
            let src = URL.createObjectURL(file);
            img.onload = function(){
                if(this.width !== 300 || this.height !== 300){
                    $error.text("Image must be exactly 300px × 300px.");
                    $(input).val('');
                    $previewBox.addClass("d-none");
                } else {
                    $previewBox.find("img").attr("src", src);
                    $previewBox.removeClass("d-none");
                }
                URL.revokeObjectURL(src);
            };
            img.src = src;
        }
    });

    // REMOVE EXISTING IMAGE
    $(document).on("click", ".remove-preview", function(){
        let $previewBox = $(this).closest(".image-preview");
        $previewBox.find("img").attr("src",'');
        $previewBox.addClass("d-none");
        $previewBox.siblings("input[type=file]").val('');
    });

    // BLOCK FORM SUBMIT IF ANY INVALID IMAGE
    $("#sectionForm").on("submit", function(e){
        let hasError = false;
        $(".image-error").each(function(){
            if($(this).text() !== '') hasError = true;
        });
        if(hasError){
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Fix Image Errors',
                text: 'Please upload valid 300×300 images before submitting.'
            });
        }
    });

});
</script>
@endpush
