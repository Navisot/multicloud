<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MultiCloud Application</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <!-- Bulma Version 0.6.2-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.6.2/css/bulma.min.css" integrity="sha256-2k1KVsNPRXxZOsXQ8aqcZ9GOOwmJTMoOB5o5Qp1d6/s=" crossorigin="anonymous" /><link rel="stylesheet" type="text/css" href="{{  asset('/css/home.css') }}">
</head>
<body>
<!-- START NAV -->
<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <a class="navbar-item" href="{{route('homepage')}}">
                MultiCloudApp
            </a>
            <span class="navbar-burger burger" data-target="navbarMenu">
            <span></span>
            <span></span>
            <span></span>
          </span>
        </div>
        <div id="navbarMenu" class="navbar-menu">
            <div class="navbar-end">
                <a class="navbar-item is-active" href="{{route('homepage')}}">
                    Home
                </a>
                @if(!Auth::check())
                    <a class="navbar-item" href="{{route('login')}}">
                        Login / Register
                    </a>
                @else
                    <a class="navbar-item logoutUser" href="#">
                        Logout
                    </a>
                @endif
            </div>
        </div>
    </div>
</nav>
<!-- END NAV -->
<div class="container">
    <!-- START ARTICLE FEED -->
    <section class="articles">
        <div class="column is-8 is-offset-2">
            <!-- START PROMO BLOCK -->
            <section class="hero is-info is-bold is-small promo-block">
                <div class="hero-body">
                    <div class="container">
                        <h1 class="title">
                            MultiCloud Application
                        </h1>
                        <h2 class="subtitle">
                            Make your deployment easy, in a few clicks..
                        </h2>
                    </div>
                </div>
            </section>
            <!-- END PROMO BLOCK -->
            <!-- START ARTICLE -->
            <div class="card article">
                <div class="card-content">
                    <div class="media">
                        <div class="media-center">
                            <img src="{{asset('/css/development.png')}}" class="author-image" alt="Placeholder image">
                        </div>
                        <div class="media-content has-text-centered">
                            <p class="subtitle is-6 article-subtitle">
                                @if(Auth::check())
                                    <span class="has-text-centered" style="color: #000; font-weight:bold;">Dashboard</span><br>
                                <span>{{Auth::user()->name}}</span>,  <a class="logoutUser" href="#">Logout</a>
                                @else
                                <a href="#">Created By Nikos Avramidis (Navisot)</a>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="content article-body">
                        @if(!Auth::check())
                            <p class="has-text-centered">In order to use this application, you must be an active member.</p>
                        <div class="has-text-centered"><a class="button is-info" style="font-size:18px;" href="{{route('login')}}">Login / Register</a></div>
                            @else
                            <div class="has-text-centered">
                                <a class="button is-primary" href="{{route('vms')}}" style="font-size:18px;">List all VMs</a> || <a class="button is-info" href="{{route('deploy')}}" style="font-size:18px;">Application Deploy.</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
    </section>
    <!-- END ARTICLE FEED -->
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
    $('.logoutUser').on('click', function(e){
        var _token = '{{csrf_token()}}';
        $.ajax({
            method: 'post',
            url: '{{route('logout')}}',
            data: {'_token': _token},
            success: function(){
                location.reload();
            },
            error: function(){
                console.log('Ajaxian Error');
            }

        });
    });
</script>

</body>
</html>