@if (Auth::guard('admin')->check())
@php
$code = Auth::guard('admin')->user()->code;
$profilephoto = Auth::guard('admin')->user()->profilePhoto;
$avatar = asset('images/avatar.png');
if ($profilephoto != '' && $profilephoto != null) {
$avatar = asset("uploads/profile/$profilephoto");
}
@endphp
<header class="topbar">
    <nav class="navbar top-navbar navbar-expand-md navbar-dark">
        <div class="navbar-header">
            <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
            <a class="navbar-brand" href="#">
                @if($settings['logo'] != '' && $settings['logo'] != null)
                <b class="logo-icon">
                    <!-- Dark Logo icon -->
                    <img src="{{ asset('storage/'.$settings['logo']) }}" alt="{{ $settings['site_title'] }}" class="dark-logo" height="48" width="48" />
                    <!-- Light Logo icon -->
                    <img src="{{ asset('storage/'.$settings['logo']) }}" alt="{{ $settings['site_title'] }}" class="light-logo" height="48" width="48" />
                </b>
                @endif
                @if($settings['site_title'] != '' && $settings['site_title'] != null)
                <span class="logo-text">
                    <span style="font-size:18px">{{ $settings['site_title'] ?? config('app.name') }}</span>
                </span>
                @endif
            </a>
            <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
        </div>
        <div class="navbar-collapse collapse" id="navbarSupportedContent">
            <ul class="navbar-nav float-left mr-auto">
                <li class="nav-item d-none d-md-block"><a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
                <li class="nav-item dropdown">
                </li>
            </ul>
            <ul class="navbar-nav float-right">
                <li class="nav-item dropdown">

                </li>
                <li class="nav-item dropdown">

                </li>
                <li class="nav-item d-none">
                    <a type="button" href="javascript:void" class="nav-link" id="change-theme" title="Change Theme">
                        <input type="checkbox" class="d-none" name="theme-view" id="theme-view">
                        <i class="fas fa-moon" id="theme-tag"></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="{{ $avatar }}" alt="user" class="rounded-circle" width="31"></a>
                    <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                        <span class="with-arrow"><span class="bg-primary"></span></span>
                        <div class="d-flex no-block align-items-center p-15 bg-primary text-white m-b-10">
                            <div class=""><img src="{{ $avatar }}" alt="user" class="img-circle" width="60"></div>
                            <div class="ml-2">
                                @if (session('USER_LOGIN'))
                                <h4 class="m-b-0">{{ Auth::guard('admin')->user()->username }}</h4>
                                <p class=" m-b-0">{{ Auth::guard('admin')->user()->userEmail }}</p>
                                @endif
                            </div>
                        </div>
                        <a class="dropdown-item" href="{{ url('/profile/' . $code) }}" style="cursor:pointer;"><i class="ti-user m-r-5 m-l-5"></i> My Profile</a>
                        <a class="dropdown-item" href="{{ url('/logout') }}"><i class="fa fa-power-off m-r-5 m-l-5"></i> Logout</a>
                        <div class="dropdown-divider"></div>
                        <div class="px-2"><a href="{{ url('/profileshow/' . $code) }}" class="btn btn-sm btn-success btn-rounded">View Profile</a></div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>
@endif