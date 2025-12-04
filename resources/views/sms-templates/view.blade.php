@extends('template.master', ['pageTitle' => 'SMS Template - View'])
@push('styles')
  <style>
    .info-box {
      border: 1px solid #dee2e6;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 5px;
      background-color: #f8f9fa;
    }
    .template-box {
      background-color: #ffffff;
      border: 1px solid #dee2e6;
      padding: 15px;
      border-radius: 5px;
      min-height: 150px;
      white-space: pre-wrap;
      word-wrap: break-word;
    }
  </style>
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">SMS Template</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/sms-templates/list') }}">SMS Templates</a></li>
              <li class="breadcrumb-item">View</li>
            </ol>
          </nav>
        </div>
      </div>
      @if($viewRights == 1)
      <div class="col-7 align-self-center ">
        <a href="{{ url('/sms-templates/list') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
      </div>
      @endif
    </div>
  </div>
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-12">
            <h5 class="mb-0" data-anchor="data-anchor">View SMS Template</h5>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 form-group">
            <label><strong>Title:</strong></label>
            <div class="info-box">{{ $queryresult->title }}</div>
          </div>
          <div class="col-md-3 form-group">
            <label><strong>Status:</strong></label>
            <div class="info-box">
              @if($queryresult->isActive == 1)
                <span class="badge badge-success">Active</span>
              @else
                <span class="badge badge-danger">Inactive</span>
              @endif
            </div>
          </div>
          <div class="col-md-3 form-group">
            <label><strong>Created At:</strong></label>
            <div class="info-box">{{ $queryresult->created_at->format('d-m-Y H:i') }}</div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 form-group">
            <label><strong>Template:</strong></label>
            <div class="template-box">{{ $queryresult->template }}</div>
            <small class="text-muted">
              Character Count: {{ strlen($queryresult->template) }}
            </small>
          </div>
        </div>

        @if($queryresult->updated_at && $queryresult->updated_at != $queryresult->created_at)
        <div class="row">
          <div class="col-md-12 form-group">
            <label><strong>Last Updated:</strong></label>
            <div class="info-box">{{ $queryresult->updated_at->format('d-m-Y H:i') }}</div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
@endsection
