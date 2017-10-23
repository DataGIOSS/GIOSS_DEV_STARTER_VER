@extends('layouts.menu')

@section('content')
<div class="container-fluid" style="margin-left: 65px">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            <h1 class="page-header" style="color: #7FFFD4; font-size: 3em; text-shadow: 0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 0 4px 0 #b9b9b9, 0 5px 0 #aaa, 0 6px 1px rgba(0,0,0,.1), 0 0 5px rgba(0,0,0,.1), 0 1px 3px rgba(0,0,0,.3), 0 3px 5px rgba(0,0,0,.2), 0 5px 10px rgba(0,0,0,.25), 0 10px 10px rgba(0,0,0,.2),0 20px 20px rgba(0,0,0,.15); font-family: 'Cinzel Decorative', serif;">Gestión De Usuarios</h1>
            <div class="panel panel-default" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">
                <div class="panel-heading" id="panel" style="font-family: 'Poiret One', cursive; font-size: 20px"> Creación de Usuarios del Aplicativo</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('registro') }}" novalidate>
                        {{ csrf_field() }}

                        <div class="row" id="info_usuario" style="margin: auto auto auto auto; font-family: 'Jura', sans-serif; font-size: 16px">
                            <h3><kbd> Información de Usuario </kbd></h3>
                            <div class="form-group" style="margin: auto auto auto auto;">
                                @if(session()->has('success'))
                                    <div class="alert alert-success fade in" style="margin: auto auto auto auto">
                                    <strong>El Usuario fue creado con EXITO!</strong>
                                  </div>
                                @elseif(session()->has('error'))
                                    <div class="alert alert-danger fade in" style="margin: auto auto auto auto">
                                        <strong>El Usuario no pudo ser creado con EXITO!</strong>
                                    </div>
                                @endif
                            </div>
                            <hr>
                            <div>
                                <blockquote class="blockquote">
                                    <footer class="blockquote-footer" style="font-family: 'Jura', sans-serif; font-size: 16px"> Todos los campos de este formulario son obligatorios (*)</footer>
                                </blockquote>
                            </div>
                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label" style="font-family: 'Jura', sans-serif; font-size: 16px; vertical-align: middle;">Nombres:</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}"  style="font-family: 'Jura', sans-serif; font-size: 16px" autofocus>

                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif

                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('lastname') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label" style="font-family: 'Jura', sans-serif; font-size: 16px; vertical-align: middle;">Apellidos:</label>

                                <div class="col-md-6">
                                    <input id="lastname" type="text" class="form-control" name="lastname" value="{{ old('lastname') }}"  style="font-family: 'Jura', sans-serif; font-size: 16px" autofocus>

                                    @if ($errors->has('lastname'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('lastname') }}</strong>
                                        </span>
                                    @endif

                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="password" class="col-md-4 control-label" style="font-family: 'Jura', sans-serif; font-size: 16px; vertical-align: middle;">Contraseña:</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password" value="{{ old('password') }}"  style="font-family: 'Jura', sans-serif; font-size: 16px" autofocus>

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                    
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label" style="font-family: 'Jura', sans-serif; font-size: 16px; vertical-align: middle;">Correo Electrónico:</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" style="font-family: 'Jura', sans-serif; font-size: 16px" autofocus>

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <br><hr>

                            <div class="input-group col-md-6 form-group{{ $errors->has('tipo_usuario') ? ' has-error' : '' }}" style="margin: auto auto auto auto">
                                <div class="input-group input-daterange">
                                    <div for="tipo_usuario" class="input-group-addon"><b style="font-family: 'Poiret One', cursive;">Tipo de Usuario</b></div>
                                    <select id="tipo_usuario" name="tipo_usuario" class="form-control" required style="font-family: 'Jura', sans-serif; font-size: 16px">
                                        <option value="1" style="font-family: 'Jura', sans-serif; font-size: 16px"> Administrador (Todos los Permisos) </option>
                                        <option value="2" style="font-family: 'Jura', sans-serif; font-size: 16px"> Invitado (Solo Carga de Archivos) </option>
                                    </select>

                                    @if ($errors->has('tipo_usuario'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('tipo_usuario') }}</strong>
                                        </span>
                                    @endif

                                </div>

                            </div>

                        </div>
                        
                        <hr id="separador_usuario">
                        
                        <div class="form-group">
                            <div style="padding-left: 45%">
                                <button type="submit" class="btn btn-primary" style="font-family: 'Jura', sans-serif; font-size: 13px">
                                    Registrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
