<!DOCTYPE html>
<html lang="en" class="chrome windows fontawesome-i2svg-active fontawesome-i2svg-complete">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="baseurl" content="{{ url('/') }}">
    <title>{{ $settings['meta_site_title'] ?? config('app.name') }}</title>

    <meta name="description" content="{{ $settings['meta_site_description'] ?? '' }}">


    <link href="{{ asset('storage/'.$settings['favicon']) }}" rel="shortcut icon" />

    {{-- <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('/uploads/favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('/uploads/favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('/uploads/favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('/uploads/favicon/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('/uploads/favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('/uploads/favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('/uploads/favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('/uploads/favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/uploads/favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192"
        href="{{ asset('/uploads/favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/uploads/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('/uploads/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/uploads/favicon/favicon-16x16.png') }}"> --}}
    <link rel="stylesheet" href="{{ asset('theme/css/style.min.css?v=' . time()) }}">
    <link rel="stylesheet" href="{{ asset('theme/css/custom.css?v=' . time()) }}">
    <link rel="stylesheet" href="{{ asset('theme/css/sweetalert2.min.css') }}">
    <link href="{{ asset('theme/css/toastr.min.css') }}" rel="stylesheet">
    <style>
        body .p-15 {
            padding: 15px;
        }

        body .p-l-30 {
            padding-left: 30px;
        }

        body .p-10 {
            padding: 10px;
        }

        .logo-icon img {
            border-radius: 10px;
        }

        .logo-text span {
            font-weight: bold;
            font-size: 24px;
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- Main App -->
    <div id="main-wrapper">
        @include('template.topnav')
        @include('template.sidebar')
        <div class="page-wrapper">
            @yield('content')
            @include('template.footer')
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('theme/js/jquery.min.js') }}"></script>
    <script src="{{ asset('theme/js/popper/popper.min.js') }}"></script>
    <script src="{{ asset('theme/js/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('theme/js/app.min.js') }}"></script>
    <script src="{{ asset('theme/js/app.init.js') }}"></script>
    <script src="{{ asset('theme/js/perfect-scrollbar/perfect-scrollbar.jquery.min.js') }}"></script>
    <script src="{{ asset('theme/js/waves.js') }}"></script>
    <script src="{{ asset('theme/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('theme/js/custom.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/toastr.min.js') }}"></script>
    @stack('scripts')
</body>

</html>
