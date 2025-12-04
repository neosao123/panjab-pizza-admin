@extends('template.master', ['pageTitle' => 'Section - View'])
@push('styles')
  <style>
    .line-entry-row {
      border: 1px solid #dee2e6;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 5px;
      background-color: #f8f9fa;
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
              <li class="breadcrumb-item">View</li>
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
        <div class="row">
          <div class="col-sm-12">
            <h5 class="mb-0" data-anchor="data-anchor">View Section</h5>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 form-group">
            <label>Title:</label>
            <input type="text" class="form-control-line" value="{{ $queryresult->title }}" readonly>
          </div>
          <div class="col-md-6 form-group">
            <label>Sub Title:</label>
            <input type="text" class="form-control-line" 
              value="{{ $queryresult->subTitle ?? '-' }}" readonly>
          </div>
        </div>

        @if($lineentries && count($lineentries) > 0)
          <div class="col-sm-12 mt-4">
            <h5>Section Line Entries</h5>
            @foreach ($lineentries as $index => $data)
              <div class="line-entry-row">
                <div class="row">
                  @if (!empty($data->image))
                    <div class="col-md-3 form-group">
                      <label>Image:</label>
                      <div>
                        <img class="img-thumbnail" width="100" height="100" 
                          style="width: 150px; height:100px" 
                          src="{{ url($data->image) }}">
                      </div>
                    </div>
                  @endif
                  <div class="col-md-{{ empty($data->image) ? '6' : '5' }} form-group">
                    <label>Title:</label>
                    <input type="text" class="form-control-line" 
                      value="{{ $data->title }}" readonly>
                  </div>
                  <div class="col-md-4 form-group">
                    <label>Counter:</label>
                    <input type="text" class="form-control-line" 
                      value="{{ $data->counter }}" readonly>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="col-sm-12 mt-4">
            <div class="alert alert-info">
              No line entries found for this section.
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection