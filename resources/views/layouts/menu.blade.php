<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Scripts -->
        
        {{ Html::script(asset("js/jquery-3.1.0.min.js")) }}
        {{ Html::script(asset("css/bootstrap-3.3.7-dist/js/bootstrap.min.js")) }}
        <!-- {{ Html::script(asset("js/menu.js")) }} -->
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts Design and Styles-->
        
        <link href="https://fonts.googleapis.com/css?family=Poiret+One" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Cinzel" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Jura" rel="stylesheet">

        {{ Html::style(asset("css/bootstrap-3.3.7-dist/css/bootstrap.min.css")) }}
        {{ Html::style(asset("css/font-awesome.css")) }}
        {{ Html::style(asset("css/font-awesome.min.css")) }}
        {{ Html::style(asset("css/menu.css")) }}

        <script src="https://use.fontawesome.com/fa8c2f9ced.js"></script>
        
        <!-- Scripts -->
        <script>
            window.Laravel = {!! json_encode([
                'csrfToken' => csrf_token(),
            ]) !!};
        </script>

    </head>
    <body class="body-background" style="background-image: url('{{ asset('images/Fondo2.png') }}'); background-size: cover; background-repeat: repeat-y;">

        <nav class="main-menu" style="z-index: 50 !important; height: 100%; position: fixed; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">

        <!-- <div class="settings"></div> -->
            <div class="scrollbar" id="style-1" style="z-index: 50 !important;">
      
            <li>                                   
                <a href="{{ url('/home') }}">
                <i class="fa fa-home fa-lg" style="font-size: 2.2em; margin: auto auto 7px auto"></i>
                <span class="nav-text"> Inicio </span>
                </a>
            </li>

            @if (Auth::user()->roleid == 1)
            <li>                                   
                <a href="{{ url('/registro') }}">
                <i class="fa fa-user-circle fa-lg" style="font-size: 2.2em; margin: auto auto 7px auto"></i>
                <span class="nav-text"> Crear Usuarios </span>
                </a>
            </li>
            @endif

            <li>                                   
                <a href="{{ url('/upload_files') }}">
                <i class="fa fa-upload fa-lg" style="font-size: 2.2em; margin: auto auto 7px auto"></i>
                <span class="nav-text">Cargar archivos</span>
                </a>
            </li>

            @if (Auth::user()->roleid == 1)
            <li>                                   
                <a href="{{ url('/generar_reportes') }}">
                <i class="fa fa-file-text fa-lg" style="font-size: 2.2em; margin: auto auto 7px auto"></i>
                <span class="nav-text"> Generar Reporte </span>
                </a>
            </li>
            @endif

            <li>                                   
                <a href="{{ url('/help') }}">
                <i class="fa fa-question-circle fa-lg" style="font-size: 2.2em; margin: auto auto 7px auto"></i>
                <span class="nav-text"> Ayuda </span>
                </a>
            </li>
    
  
            <ul class="logout">
                <li>
                   <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                         <i class="fa fa-sign-out fa-lg" style="font-size: 2.2em; margin: auto auto 7px auto"></i>
                        <span class="nav-text">
                            Salir
                        </span>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;"> {{ csrf_field() }} </form>
                        
                    </a>
                </li>  
            </ul>

        </nav>
    
    @yield('content')
    
    </body>
</html>