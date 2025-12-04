@extends('template.master', ['pageTitle' => 'Background Image - Edit'])
@push('styles')
  <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Background Image</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/background-images/list') }}">Background Images</a></li>
              <li class="breadcrumb-item active">Edit</li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
        <a href="{{ url('/background-image/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Update Background Image</h5>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        <form action="{{ url('/background-image/update') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
          @csrf
          <div class="row">
            <input type="hidden" name="id" value="{{ $queryresult->id }}" />
            
            <div class="col-md-6 form-group">
              <label>Background Image: <span style="color:red">*</span></label>
              <input type="file" id="image_path" name="image_path" accept=".jpg, .png, .jpeg" 
                     class="form-control" required data-parsley-fileextension="jpg,png,jpeg">
              <span class="text-danger">
                @error('image_path')
                  {{ $message }}
                @enderror
              </span>
            </div>
            
            @if(!empty($queryresult->image_path))
            <div class="col-auto" id="eImage">
              <img class="img-thumbnail mb-2" width="300" height="auto" id="showImage" 
                    src={{ url($queryresult->image_path) . '?v=' . time() }}>
              <br>
              <button type="button" class="btn btn-danger text-white" id="deleteImageBtn">
                <i class="fa fa-trash"></i> Delete Image
              </button>
            </div>
            @endif
            
            <div class="col-md-12 form-group mt-3">
              <button class="btn btn-primary" type="submit">Update Image</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
  <script>
    const baseUrl = document.getElementsByTagName("meta").baseurl.content;
    
    $(document).ready(function() {
      $('#deleteImageBtn').on('click', function() {
        const id = {{ $queryresult->id }};
        const fullPath = "{{ $queryresult->image_path }}";
        
        Swal.fire({
          title: 'Are you sure?',
          text: "You want to delete this image",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: baseUrl + "/background-image/delete-image",
              type: "post",
              data: {
                _token: "{{ csrf_token() }}",
                id: id,
                value: fullPath
              },
              success: function (data) {
                if (data == 'true') {
                  $('#eImage').addClass('d-none');
                  $('#showImage').attr('src', '');
                  Swal.fire('Deleted!', 'Image has been deleted.', 'success');
                }
              },
              error: function(xhr, status, error) {
                Swal.fire('Error!', 'Failed to delete image.', 'error');
              }
            });
          }
        });
      });
    });
  </script>
  
  @if (session('error'))
    <script>
      $(document).ready(function() {
        setTimeout(() => {
          $(".alert").remove();
        }, 5000);
      });
    </script>
  @endif
@endpush