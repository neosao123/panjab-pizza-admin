@extends('template.master', ['pageTitle' => 'Background Images'])
@push('styles')
  <link href="{{ asset('theme/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-12 align-self-center">
        <h4 class="page-title">Background Images</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
              <li class="breadcrumb-item active">Background Images</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row g-3 mb-3">
      <div class="col-lg-12">
        @if (session('success'))
          <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
            {{ session('success') }}
          </div>
        @endif
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Background Images List</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th style="width:10%">Sr. No.</th>
                    <th>Image</th>
                    <th style="width:15%">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($images as $index => $image)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                      @if($image->image_path)
                        <img src="{{ url($image->image_path) . '?v=' . time() }}" 
                             alt="Background Image" 
                             style="max-width: 200px; height: auto;">
                      @else
                        <span class="text-muted">No image</span>
                      @endif
                    </td>
                    <td>
                      <a href="{{ url('background-image/edit/' . $image->id) }}" 
                         class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Update
                      </a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  @if (session('success'))
    <script>
      $(document).ready(function() {
        setTimeout(() => {
          $(".alert").remove();
        }, 5000);
      });
    </script>
  @endif
@endpush