<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MultiCloud Application</title>
    <link rel="stylesheet" href="css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.6.1/css/bulma.min.css">
    <link rel="stylesheet" href="{{URL::to('css/fontawesome-all.min.css')}}">
    <link rel="stylesheet" href="{{URL::to('css/custom.css')}}">
</head>
<body>
    <div id="app" class="container">
        <vms></vms>
    </div>
    <script src="js/app.js" charset="UTF-8"></script>
</body>
</html>