@extends('layouts.menu')

@section('content')
<div class="container-fluid" style="margin-left: 65px">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <h1 class="page-header" style="color: #7FFFD4; font-size: 3em; text-shadow: 0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 0 4px 0 #b9b9b9, 0 5px 0 #aaa, 0 6px 1px rgba(0,0,0,.1), 0 0 5px rgba(0,0,0,.1), 0 1px 3px rgba(0,0,0,.3), 0 3px 5px rgba(0,0,0,.2), 0 5px 10px rgba(0,0,0,.25), 0 10px 10px rgba(0,0,0,.2),0 20px 20px rgba(0,0,0,.15); font-family: 'Cinzel', serif;">Pantalla de Inicio</h1>  
    
            <div class="panel panel-default" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">

                @if (Auth::user()->roleid == 1)
                <div class="panel-heading" style="font-family: 'Poiret One', cursive; font-size: 20px">Bienvenido Administrador</div>
                

                <div class="panel-body" style="font-family: 'Jura', sans-serif; font-size: 16px">
                 
                    Desde su perfil podrá Gestionar Usuarios, Cargar Archivos y Generar Reportes. 
                    <br><br>
                    <p>
                        En el menú desplegable que se encuentra en la parte izquierda de la 
                        pantalla encontrará el acceso a las secciones a través de las cuales
                        podrá gestionar los archivos de la FVL.
                    </p>

                </div>
                @endif

                @if (Auth::user()->roleid == 2)
                <div class="panel-heading">Bienvenido Invitado</div>
                

                <div class="panel-body" style="font-family: 'Jura', sans-serif; font-size: 16px">

                    Le recordamos que desde su perfil solo podrá hacer Carga de Archivos. 
                    Para tener acceso a otras funcionalidades, ingrese como Administrador. 
                    <br><br>
                    <p>
                        En el menú desplegable que se encuentra en la parte izquierda de la 
                        pantalla encontrará el acceso a las secciones a través de las cuales
                        podrá gestionar los archivos de la FVL.
                    </p>

                </div>
                @endif



            </div>

        </div>
    </div>
</div>
@endsection
