@extends('template.master', ['pageTitle'=>"Picture List"])
@push('styles')
<link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ asset('theme/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
    <div class="row">
        <div class="col-5 align-self-center">
            <h4 class="page-title">Pictures</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Configuration</a></li>
                        <li class="breadcrumb-item"><a href="#">Pictures</a></li>
                    </ol>
                </nav>
            </div>
        </div>
<div class="col-7 align-self-center">
    @if ($pictureCount < 3 && $rights['insert'] == 1)
        <a href="{{ url('pictures/add') }}" class="btn btn-info btn-sm float-right">Add New</a>
    @endif
</div>

    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0" data-anchor="data-anchor">Picture List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable-Pictures" style="width:100%" class="table table-striped table-bordered nowrap">
                    <thead>
                        <tr>
                            <th style="width:5%">Sr. No.</th>
                            <th style="width:10%">Action</th>
                            <th style="width:70%">Title</th>
                            <th style="width:15%">Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript" src="{{ asset('theme/js/datatables.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/datatable-basic.init.js') }}"></script>
<script type="text/javascript" src="{{ asset('theme/init_site/picture/index.js?v=' . time()) }}"></script>
<script type="text/javascript" src="{{ asset('theme/js/select2.min.js') }}"></script>
@endpush
