<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-image: radial-gradient(circle 610px at 5.2% 51.6%, rgba(5, 8, 114, 1) 0%, rgba(7, 3, 53, 1) 97.5%);
            color: #ffffff;
        }

        a {
            color: #ffffff;
            text-decoration: none
        }

        .card {
            width: 40%;
            padding: 15px;
            border: 2px solid rgb(252, 251, 251);
            text-align: center;
            border-radius: 20px;
        }
    </style>

</head>

<body id="app">
    <div class="card">
        <h3>{{ config('app.name') }}</h3>
        <a href="{{ url('/login') }}">Click Here To Login</a>
    </div>
    <script src="http://cdn.bootcss.com/jquery/2.2.4/jquery.min.js"></script>
    <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
    {!! Toastr::message() !!}
</body>

</html>
