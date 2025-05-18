@php
    if(Auth::check()) {
        $user = Auth::user();
        $user_id = $user->id;
        $user_name = $user->name;
        $user_email = $user->email;
        $user_acc = $user->access_level;
    } else {
        $user_id = NULL;
        $user_name = NULL;
        $user_email = NULL;
        $user_acc = NULL;
    }
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="{{ asset('js/app.js') }}"></script>
        <script src="{{ asset('js/jquery.toast.js') }}"></script>
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/jquery.toast.css') }}" rel="stylesheet">
        <title>PHP Analyzer</title>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand" href="/">PHP Analyzer</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Alterna navegação">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="/">Submit File <i class="fa fa-file" aria-hidden="true"></i><span class="sr-only"></span></a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="/github">Submit Github Repository <i class="fa fa-github" aria-hidden="true"></i><span class="sr-only"></span></a>
                    </li>
                    @if (Auth::check())    
                        <li class="nav-item active">
                            <a class="nav-link" href="/yourfiles">Your Files <i class="fa fa-files-o" aria-hidden="true"></i></i><span class="sr-only"></span></a>
                        </li>
                    @endif
                    @if ($user_acc == 'A')
                        <li class="nav-item dropdown active">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Manage
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="/term_types">Term Categories</a>
                                <a class="dropdown-item" href="/terms">Terms</a>
                            </div>
                        </li>
                    @endif
                </ul>
                <ul class="navbar-nav ml-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown active">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                    </ul>
            </div>
        </nav>
        <div class="container-fluid mt-2 content">
            @yield('content')
        </div>
        <br><br><br><br><br><br><br>
        <div class="mt-4 footer">
            <a href="https://www.uniacademia.edu.br/" target="_blank">
                <img src="{{asset('img/logo_uniacademia.png')}}" alt="" width="184" height="60">
            </a>
        </div>
        <script src="{{ asset('js/jquery-input-file.js') }}"></script>
        <script type="text/javascript">
            j = jQuery.noConflict()

            j(document).ready(function() {
                if(j(":file").length) {
                    j(":file").filestyle()
                }

                j('#btn_scroll').click(function() {
                    window.scrollTo({top:0, behavior: 'smooth'})
                })
            })
        </script>
        @if (!empty($msg))
        <script>
            j.toast({
                heading: 'Info!',
                icon: "{{$msg['type']}}",
                text: "{{$msg['text']}}",
                showHideTransition: 'slide',
                position: 'top-center',
            })
        </script>
        @endif
    </body>
</html>
