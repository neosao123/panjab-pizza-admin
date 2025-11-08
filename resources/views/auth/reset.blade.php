<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> {{ isset($pageTitle) ? $pageTitle .' | ' :  "" }} {{ config("app.name") }}</title>
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('/uploads/favicon/apple-icon-57x57.png')}}">
	<link rel="apple-touch-icon" sizes="60x60" href="{{ asset('/uploads/favicon/apple-icon-60x60.png')}}">
	<link rel="apple-touch-icon" sizes="72x72" href="{{ asset('/uploads/favicon/apple-icon-72x72.png')}}">
	<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('/uploads/favicon/apple-icon-76x76.png')}}">
	<link rel="apple-touch-icon" sizes="114x114" href="{{ asset('/uploads/favicon/apple-icon-114x114.png')}}">
	<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('/uploads/favicon/apple-icon-120x120.png')}}">
	<link rel="apple-touch-icon" sizes="144x144" href="{{ asset('/uploads/favicon/apple-icon-144x144.png')}}">
	<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('/uploads/favicon/apple-icon-152x152.png')}}">
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/uploads/favicon/apple-icon-180x180.png')}}">
	<link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('/uploads/favicon/android-icon-192x192.png')}}">
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/uploads/favicon/favicon-32x32.png')}}">
	<link rel="icon" type="image/png" sizes="96x96" href="{{ asset('/uploads/favicon/favicon-96x96.png')}}">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/uploads/favicon/favicon-16x16.png')}}">
	<link rel="stylesheet" href="{{ asset('theme/css/style.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/parsely.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/css/custom.css') }}">
</head>

<body>
    <div class="main-wrapper">
        <div class="preloader">
            <div class="lds-ripple">
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center" style="background: url({{ asset('uploads/bg-img.jpg') }}) ;background-position: center; background-repeat: no-repeat;background-size: cover;">
            <div class="auth-box">
                <div>
                    <div class="logo">
                        <h4 class="font-medium m-b-20 d-none">Admin</h4>
                         <span class="db"><img src="{{ asset('uploads/mr-singhs-pizza-logo.png') }}" alt="" height="110" width="110" /></span>
                        @if (session('message'))
                        <p class="text-danger">{{ session('message') }} </p>
                        @endif
                    </div>
                    <div class="row m-t-20">
                        <div id="altbx" class="text-center text-danger"></div>
                        <!-- Form -->
                        <form class="col-12" action="{{ url('/recover-password') }}" data-parsley-validate="" method="post" id="resetpassword">
                            <!-- email -->
                            @csrf
                            <div class="form-group row">
                                <div class="col-12">
                                    <label>New Password</label>
                                    <input type="hidden" name="code" value="{{ $result->code }}" readonly class="form-control">
                                    <input type="hidden" name="token" value="{{ $result->resetToken }}" readonly class="form-control">
                                    <input type="text" name="password" id="password" class="form-control" type="password" required="" data-parsley-required-message="Password is required">
                                    <span class="text-center">{{ $errors->first('password') }}</span>
                                </div>
                                <div class="col-12">
                                    <label>Confirm Password</label>
                                    <input type="text" name="password_confirmation" id="password_confirmation" class="form-control" type="password" required="" data-parsley-required-message="Confirm Password is required">
                                    <span class="text-center">{{ $errors->first('password') }}</span>
                                </div>
                            </div>
                            <!-- pwd -->
                            <div class="row m-t-20">
                                <div class="col-12">
                                    <button class="btn btn-block btn-lg btn-info" type="submit" id="submit" name="submit">Reset Password</button>
                                </div>
                            </div>
                            <p class="f-w-600 text-right"><a href="{{ url('/login') }}">Back to Login.</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{asset('theme/js/jquery.min.js')}}"></script>
    <script src="{{asset('theme/js/popper.min.js')}}"></script>
    <script src="{{asset('theme/js/bootstrap.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('theme/js/parsely.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".preloader").fadeOut();

            $("#password_confirmation").on("change", function() {
                var cpassword = $(this).val();
                var password = $('#password').val();
                if (cpassword != password) {
                    $("#altbx").text("Password does not match.");
                    setTimeout(() => {
                        $("#altbx").empty();
                    }, 5000);
                    $('#submit').prop("disabled", true);
                    return false;
                } else {
                    $('#submit').prop("disabled", false);
                }
            });

            $("#password").on("change", function() {
                var cpassword = $(this).val();
                var password = $('#password_confirmation').val();
                if (cpassword != password && password != '') {
                    $("#altbx").text("Password does not match.");
                    setTimeout(() => {
                        $("#altbx").empty();
                    }, 5000);
                    $('#submit').prop("disabled", true);
                    return false;
                } else {
                    $('#submit').prop("disabled", false);
                }
            });

        });
    </script>
</body>

</html>