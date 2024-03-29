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
                    <a class="navbar-item" id="logoutUser" href="#">
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
            <section class="hero is-danger is-bold is-small promo-block">
                <div class="hero-body">
                    <div class="container">
                        <h1 class="title">
                            <i class="fa fa-bell-o"></i>
                            Lorem dolor sed viverra
                        </h1>
                        <h2 class="subtitle">
                            Consequat id porta nibh venenatis cras sed felis eget
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
                            <p class="title article-title">Introducing2 a new feature for paid subscribers</p>
                            <p class="subtitle is-6 article-subtitle">
                                <a href="#">@daria</a> on February 24, 2018
                            </p>
                        </div>
                    </div>
                    <div class="content article-body">
                        <p>Non arcu risus quis varius quam quisque. Dictum varius duis at consectetur lorem. Posuere sollicitudin aliquam ultrices sagittis orci a scelerisque purus semper. </p>
                        <p>Metus aliquam eleifend mi in nulla posuere sollicitudin aliquam ultrices. In hac habitasse platea dictumst vestibulum rhoncus est pellentesque elit. Accumsan lacus vel facilisis volutpat. Non sodales neque sodales ut etiam. Est pellentesque elit ullamcorper dignissim cras tincidunt lobortis feugiat vivamus.</p>
                        <h3 class="has-text-centered">Feugiat sed lectus vestibulum mattis.</h3>
                        <p> Molestie ac feugiat sed lectus vestibulum. Feugiat sed lectus vestibulum mattis. Volutpat diam ut venenatis tellus in metus vulputate. Feugiat in fermentum posuere urna nec. Pharetra convallis posuere morbi leo urna molestie at. Accumsan lacus vel facilisis volutpat est velit egestas. Fermentum leo vel orci porta. Faucibus interdum posuere lorem ipsum.
                        </p>
                    </div>
                </div>
            </div>
            <!-- END ARTICLE -->
            <!-- START ARTICLE -->
            <div class="card article">
                <div class="card-content">
                    <div class="media">
                        <div class="media-center">
                            <img src="http://www.radfaces.com/images/avatars/wednesday-addams.jpg" class="author-image" alt="Placeholder image">
                        </div>
                        <div class="media-content has-text-centered">
                            <p class="title article-title">Sapien eget mi proin sed 🔱</p>
                            <p class="subtitle is-6 article-subtitle">
                                <a href="#">@skeetskeet</a> on December 17, 2017
                            </p>
                        </div>
                    </div>
                    <div class="content article-body">
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                            Accumsan lacus vel facilisis volutpat est velit egestas. Sapien eget mi proin sed. Sit amet mattis vulputate enim.
                        </p>
                        <p>
                            Commodo ullamcorper a lacus vestibulum sed arcu. Fermentum leo vel orci porta non. Proin fermentum leo vel orci porta non pulvinar. Imperdiet proin fermentum leo vel.
                            Tortor posuere ac ut consequat semper viverra. Vestibulum lectus mauris ultrices eros. </p>
                        <h3 class="has-text-centered">Lectus vestibulum mattis ullamcorper velit sed ullamcorper morbi. Cras tincidunt lobortis feugiat vivamus.</h3>
                        <p>
                            In eu mi bibendum neque egestas congue quisque egestas diam. Enim nec dui nunc mattis enim ut tellus. Ut morbi tincidunt augue interdum velit euismod in.
                            At in tellus integer feugiat scelerisque varius morbi enim nunc. Vitae suscipit tellus mauris a diam. Arcu non sodales neque sodales ut etiam sit amet.
                        </p>
                    </div>
                </div>
            </div>
            <!-- END ARTICLE -->
            <!-- START PROMO BLOCK -->
            <section class="hero is-info is-bold is-small promo-block">
                <div class="hero-body">
                    <div class="container">
                        <h1 class="title">
                            <i class="fa fa-bell-o"></i>
                            Nemo enim ipsam voluptatem quia.</h1>
                        <h2 class="subtitle">Natus error sit voluptatem</h2>
                    </div>
                </div>
            </section>
            <!-- END PROMO BLOCK -->
            <!-- START ARTICLE -->
            <div class="card article">
                <div class="card-content">
                    <div class="media">
                        <div class="media-center">
                            <img src="http://www.radfaces.com/images/avatars/angela-chase.jpg" class="author-image" alt="Placeholder image">
                        </div>
                        <div class="media-content has-text-centered">
                            <p class="title article-title">Cras tincidunt lobortis feugiat vivamus.</p>
                            <p class="subtitle is-6 article-subtitle">
                                <a href="#">@angela</a> on October 7, 2017
                            </p>
                        </div>
                    </div>
                    <div class="content article-body">
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Accumsan lacus vel facilisis volutpat est velit egestas. Sapien eget mi proin sed. Sit amet mattis vulputate enim.
                        </p>
                        <p>
                            Commodo ullamcorper a lacus vestibulum sed arcu. Fermentum leo vel orci porta non. Proin fermentum leo vel orci porta non pulvinar. Imperdiet proin fermentum leo vel. Tortor posuere ac ut consequat semper viverra. Vestibulum lectus mauris ultrices eros. </p>
                        <h3 class="has-text-centered">“Everyone should be able to do one card trick, tell two jokes, and recite three poems, in case they are ever trapped in an elevator.”</h3>
                        <p>
                            In eu mi bibendum neque egestas congue quisque egestas diam. Enim nec dui nunc mattis enim ut tellus. Ut morbi tincidunt augue interdum velit euismod in. At in tellus integer feugiat scelerisque varius morbi enim nunc. Vitae suscipit tellus mauris a diam. Arcu non sodales neque sodales ut etiam sit amet.
                        </p>
                    </div>
                </div>
            </div>
            <!-- END ARTICLE -->
    </section>
    <!-- END ARTICLE FEED -->
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
    $('#logoutUser').on('click', function(e){
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