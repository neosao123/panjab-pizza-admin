<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> {{ isset($pageTitle) ? $pageTitle . ' | ' : '' }} {{ config('app.name') }}</title>
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('/uploads/favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('/uploads/favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('/uploads/favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('/uploads/favicon/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('/uploads/favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('/uploads/favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('/uploads/favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('/uploads/favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/uploads/favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('/uploads/favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/uploads/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('/uploads/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/uploads/favicon/favicon-16x16.png') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/style.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/custom.css') }}">
</head>

<body style="overflow-y:hidden;">
    <div class="main-wrapper">
        <div class="preloader">
            <div class="lds-ripple">
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center"
            style="background: url({{ asset('uploads/bg-img.jpg') }}) ;background-position: center; background-repeat: no-repeat;background-size: cover;">
            <div class="auth-box">
                <div id="loginform">
                    <div class="logo m-4">
                        <h4 class="font-medium m-b-20 d-none">Admin Login</h4>
                        <span class="db"><img src="{{ asset('uploads/logo.png') }}" alt="" height="110" width="110" /></span>

                    </div>
                    @if (session('fail'))
                        <div class="alert alert-warning">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                            {{ session('fail') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-warning">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('message'))
                        <div class="alert alert-warning">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                            {{ session('message') }}
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Form -->
                    <div class="row">
                        <div class="col-12">
                            <p>{{ env('SOCKET_ORDER_API') }}</p>

                            <form class="form-horizontal m-t-20" id="loginform" action="{{ url('/login') }}" method="post" data-parsley-validate="">
                                @csrf
                                <div class="col-md-12 mb-3">

                                    <input type="text" name="username" class="form-control form-control-lg" placeholder="Username" aria-describedby="basic-addon1" required=""
                                        data-parsley-required-message="Username is required." value="{{ Cookie::get('username') }}" />
                                    <span class="text-danger">{{ $errors->first('username') }}</span>
                                </div>

                                <div class="col-md-12 mb-3">

                                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1"
                                        required="" data-parsley-required-message="Password is required." value="{{ Cookie::get('password') }}" />
                                    <span class="text-danger">{{ $errors->first('password') }}</span>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="rememberme" id="customCheck1" @if (Cookie::get('username')) checked @endif>
                                            <label class="custom-control-label" for="customCheck1">Remember me</label>
                                            <a href="{{ url('/reset-password') }}" class="text-dark float-right"><i class="fa fa-lock m-r-5"></i> Forgot password?</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group text-center">
                                    <div class="col-xs-12 p-b-20">
                                        <button class="btn btn-block btn-lg btn-info" type="submit">Log In</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('theme/js/jquery.min.js') }}"></script>
    <script src="{{ asset('theme/js/popper/popper.min.js') }}"></script>
    <script src="{{ asset('theme/js/bootstrap/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script>
        $('[data-toggle="tooltip"]').tooltip();
        $(".preloader").fadeOut();
        $('#to-recover').on("click", function() {
            $("#loginform").slideUp();
            $("#recoverform").fadeIn();
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
    @if (session('message'))
        <script>
            $(document).ready(function() {
                'use strict';
                setTimeout(() => {
                    $(".alert").remove();
                }, 5000);

            });
        </script>
    @endif
    @if (session('success'))
        <script>
            $(document).ready(function() {
                'use strict';
                setTimeout(() => {
                    $(".alert").remove();
                }, 5000);

            });
        </script>
    @endif
    @if (session('fail'))
        <script>
            $(document).ready(function() {
                'use strict';
                setTimeout(() => {
                    $(".alert").remove();
                }, 5000);

            });
        </script>
    @endif
</body>

</html>
