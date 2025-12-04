@extends('template.master', ['pageTitle' => 'Send SMS'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link href="{{ asset('theme/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
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

        .nav-tabs .nav-link {
            color: #495057;
        }

        .nav-tabs .nav-link.active {
            color: #28a745;
            font-weight: 600;
        }

        .template-preview-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            min-height: 120px;
            margin-top: 10px;
        }

        .length-counter {
            font-size: 13px;
            color: #6c757d;
            margin-top: 5px;
        }

        .sms-log-card {
            border-left: 3px solid #28a745;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .sms-log-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .sms-log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .sms-log-time {
            font-size: 11px;
            color: #999;
        }

        .sms-log-message {
            font-size: 13px;
            color: #495057;
            margin-bottom: 8px;
        }

        .history-empty {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .history-empty i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
@endpush

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">SMS</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item">Messages</li>
                            <li class="breadcrumb-item">SMS</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-7 align-self-center">
                <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary btn-sm float-right">Back</a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">

            <!-- SMS Send Form -->
            <div class="col-md-7 col-lg-7">
                <div class="card">
                    <div class="card-body">

                        <!-- Tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#textSms" role="tab">
                                    <span>Text SMS</span>
                                </a>
                            </li>

                            <!-- 🔥 New Tiwloos/Twillio Settings Tab -->
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#twillioSettings" role="tab">
                                    <span>Twillio Settings</span>
                                </a>
                            </li>
                        </ul>

                        @if (session('success'))
                            <div class="alert alert-success mt-3">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger mt-3">
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- Tab Content -->
                        <div class="tab-content mt-4">

                            <!-- TEXT SMS TAB -->
                            <!-- TEXT SMS TAB -->
                            <div class="tab-pane active" id="textSms">
                                <form id="smsForm" action="{{ url('/customers/send-sms') }}" method="post">
                                    @csrf

                                    <div class="form-group">
                                        <label>Select Template <span class="text-danger">*</span></label>
                                        <select class="form-control select2-ajax" id="template" name="template">
                                            <option value="">-- Select Template --</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Template Preview</label>
                                        <div class="template-preview-box" id="templatePreview">
                                            <em class="text-muted">Select template to preview</em>
                                        </div>
                                        <div class="length-counter">
                                            Length: <span id="messageLength">0</span> |
                                            SMS Parts: <span id="messageCount">0</span>
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        This will send SMS to <strong>all active customers</strong>
                                    </div>

                                    <button type="submit" class="btn btn-success px-4" id="sendBtn">
                                        <i class="fa fa-paper-plane"></i> Send SMS to All Customers
                                    </button>
                                </form>
                            </div>

                            <!-- 🔥 NEW TWILLIO SETTINGS TAB -->
                            <!-- TWILLIO SETTINGS TAB -->
                            <div class="tab-pane" id="twillioSettings" role="tabpanel">
                                <form action="{{ url('/customers/twillio-settings-save') }}" method="post">
                                    @csrf

                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        Get your Twilio credentials from <a href="https://www.twilio.com/console"
                                            target="_blank">Twilio Console</a>
                                    </div>

                                    <div class="form-group mt-3">
                                        <label>Twilio Account SID <span class="text-danger">*</span></label>
                                        <input type="text" name="twilio_sid"
                                            class="form-control @error('twilio_sid') is-invalid @enderror"
                                            value="{{ old('twilio_sid', $twilioSettings->twilio_session_id ?? '') }}"
                                            placeholder="AC...">
                                        @error('twilio_sid')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Twilio Auth Token <span class="text-danger">*</span></label>
                                        <input type="text" name="twilio_auth_token"
                                            class="form-control @error('twilio_auth_token') is-invalid @enderror"
                                            value="{{ old('twilio_auth_token', $twilioSettings->twilio_auth_id ?? '') }}"
                                            placeholder="Enter Auth Token">
                                        @error('twilio_auth_token')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Twilio Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" name="twilio_from_number"
                                            class="form-control @error('twilio_from_number') is-invalid @enderror"
                                            value="{{ old('twilio_from_number', $twilioSettings->twilio_number ?? '') }}"
                                            placeholder="+1234567890">
                                        @error('twilio_from_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Format: +1234567890 (with country code)</small>
                                    </div>

                                    <button type="submit" class="btn btn-primary mt-2">
                                        <i class="fa fa-save"></i> Save Settings
                                    </button>

                                    @if ($twilioSettings)
                                        <span class="badge badge-success ml-2">Settings Active</span>
                                    @endif
                                </form>
                            </div>

                        </div><!-- tab content end -->
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE HISTORY (UNCHANGED) -->
            <div class="col-md-5 col-lg-5">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fa fa-history"></i> Recent SMS
                        </h5>
                    </div>

                    <div class="card-body">
                        <table id="dataTable-sms-logs" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Mobile</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Sent At</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('theme/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/datatables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/datatable-basic.init.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/init_site/sms/index.js?v=' . time()) }}"></script>
@endpush
