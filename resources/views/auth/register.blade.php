@extends('layouts.menu')

@section('content')
<div class="container-fluid" style="margin-left: 65px">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <h1 id="content_header" class="page-header" >Gestión De Usuarios</h1>

            <ul class="nav nav-tabs" id="myTab">
                <li class="active"><a data-toggle="tab" onclick="clean_tab()" href="#home_us" style="font-family: 'Poiret One', cursive; font-size: 20px;"><b>Creación de Usuarios del Aplicativo</b></a></li>
                <li><a data-toggle="tab" href="#menu1" onclick="clean_tab()" style="font-family: 'Poiret One', cursive; font-size: 20px;"><b>Listado de Usuarios</b> <img id="loading_table_img" style="display: none" src="{{asset("images/preloader.gif")}}"></a></li>
            </ul>

            <div class="tab-content">
                <div id="home_us" class="tab-pane panel fade in active" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">
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

                <div id="menu1" class="tab-pane panel fade" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">
                    
                    <div class="panel-body">
                        
                        <div id="alert" class="form-group" style="margin: auto auto auto auto; font-family: 'Jura', sans-serif; font-size: 15px; width: 50%; text-align: center">
                            @if(session()->has('edit_success'))
                                <div class="alert alert-success fade in" style="margin: auto auto auto auto">
                                <strong>EL USUARIO FUE ACTUALIZADO CON EXITO!</strong>
                              </div>
                            @elseif(session()->has('edit_error'))
                                <div class="alert alert-danger fade in" style="margin: auto auto auto auto">
                                    <strong>El USUARIO NO PUDO SER EDITADO CON EXITO!</strong>
                                </div>

                                <hr>
                            @endif

                            @if(session()->has('disable_success'))
                                <div class="alert alert-danger fade in" style="margin: auto auto auto auto">
                                <strong>EL USUARIO FUE DESACTIVADO CON EXITO!</strong>
                              </div>
                            @elseif(session()->has('able_success'))
                                <div class="alert alert-success fade in" style="margin: auto auto auto auto">
                                    <strong>El USUARIO FUE ACTIVADO CON EXITO!</strong>
                                </div>

                                <hr>
                            @elseif(session()->has('disable_error'))
                                <div class="alert alert-danger fade in" style="margin: auto auto auto auto">
                                    <strong>El USUARIO NO PUDO SER MODIFICADO CON EXITO!</strong>
                                </div>

                                <hr>
                            @endif
                        </div>

                        <table class="table table-striped">
                            <thead style="font-family: 'Jura', sans-serif; font-size: 15px">
                                <th style="text-align: center;">NOMBRE</th>
                                <th style="text-align: center;">APELLIDO</th>
                                <th style="text-align: center;">CORREO ELECTRÓNICO</th>
                                <th style="text-align: center;">TIPO</th>
                                <th style="text-align: center;">ACCIÓN</th>
                            </thead>
                            <tbody style="font-family: 'Jura', sans-serif; font-size: 15px; text-align: center;">
                                @foreach($users as $user)
                                    <tr>
                                        <td style="position: relative; top: 50%; transform: translateY(10%);">{{$user->name}}</td>
                                        <td style="position: relative; top: 50%; transform: translateY(10%);">{{$user->lastname}}</td>
                                        <td style="position: relative; top: 50%; transform: translateY(10%);">{{$user->email}}</td>
                                        <td style="position: relative; top: 50%; transform: translateY(10%);">
                                        @if($user->roleid == 1)
                                            <span class="label label-danger">
                                                Administrador
                                            </span>
                                        @else
                                            <span class="label label-primary">
                                                Invitado
                                            </span>
                                        @endif
                                        </td>
                                        
                                        <td>
                                            <button onclick="get_url('{{ $user->id }}')" id="edit_user_btn" type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit{{$user->id}}" data-whatever="@mdo" style="position: relative; width: 40px; height: 40px; border-radius: 100%; outline: none"><span id="disable_user_spn" class="fa fa-wrench" style="font-size: 1.5em; position: absolute; display: block; top: 18%; left: -18%"></span></button>
                                            

                                            <div class="modal fade" id="edit{{$user->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                              <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                  <div class="modal-header">
                                                    <h3 class="modal-title" id="exampleModalLabel" style="text-transform: uppercase;">EDITAR USUARIO {{$user->name}} {{$user->lastname}}</h3>
                                                  </div>
                                                  <div class="modal-body">



                                                    <form id="{{ $user->id }}" class="form-horizontal" role="form" method="POST" action="{{ url('editar') }}" novalidate>
                                                        {{ csrf_field() }}

                                                        <div class="row" id="info_edit_usuario{{ $user->id }}" style="margin: auto auto auto auto; font-family: 'Jura', sans-serif; font-size: 16px">

                                                            <div>
                                                                <blockquote class="blockquote">
                                                                    <footer class="blockquote-footer" style="font-family: 'Jura', sans-serif; font-size: 16px"> Edite solo los datos de usuario que desea modificar. Los demás campos deben permanecer iguales (*)</footer>
                                                                </blockquote>
                                                            </div>

                                                            
                                                            <input id="edit_id_user{{ $user->id }}" type="hidden" class="form-control" name="edit_id_user" value="{{ $user->id }}"  style="font-family: 'Jura', sans-serif; font-size: 16px" autofocus>
                                                            

                                                            <div class="form-group" style="display: none">
                                                                <input id="edit_url{{ $user->id }}" type="text" class="form-control" name="edit_url" value=""  style="font-family: 'Jura', sans-serif; font-size: 16px" autofocus>
                                                            </div>


                                                            <div class="form-group{{ $errors->has('edit_name') ? ' has-error' : '' }}">
                                                                <label for="edit_name" class="col-md-4 control-label" style="font-family: 'Jura', sans-serif; font-size: 16px; vertical-align: middle;">Nombres:</label>

                                                                <div class="col-md-6">
                                                                    <input id="edit_name" type="text" class="form-control" name="edit_name" value="{{ $user->name }}"  style="font-family: 'Jura', sans-serif; font-size: 16px" autofocus>

                                                                    @if ($errors->has('edit_name'))
                                                                        <span class="help-block">
                                                                            <strong>{{ $errors->first('edit_name') }}</strong>
                                                                        </span>
                                                                    @endif

                                                                </div>
                                                            </div>

                                                            <div class="form-group{{ $errors->has('edit_lastname') ? ' has-error' : '' }}">
                                                                <label for="edit_lastname" class="col-md-4 control-label" style="font-family: 'Jura', sans-serif; font-size: 16px; vertical-align: middle;">Apellidos:</label>

                                                                <div class="col-md-6">
                                                                    <input id="edit_lastname" type="text" class="form-control" name="edit_lastname" value="{{ $user->lastname }}"  style="font-family: 'Jura', sans-serif; font-size: 16px" autofocus>

                                                                    @if ($errors->has('edit_lastname'))
                                                                        <span class="help-block">
                                                                            <strong>{{ $errors->first('edit_lastname') }}</strong>
                                                                        </span>
                                                                    @endif

                                                                </div>
                                                            </div>

                                                            <div class="form-group{{ $errors->has('edit_password') ? ' has-error' : '' }}">
                                                                <label for="edit_password" class="col-md-4 control-label" style="font-family: 'Jura', sans-serif; font-size: 16px; vertical-align: middle;">Contraseña:</label>

                                                                <div class="col-md-6">
                                                                    <input id="edit_password" type="password" class="form-control" placeholder="*************" name="edit_password" style="font-family: 'Jura', sans-serif; font-size: 16px" autofocus>

                                                                    @if ($errors->has('edit_password'))
                                                                        <span class="help-block">
                                                                            <strong>{{ $errors->first('edit_password') }}</strong>
                                                                        </span>
                                                                    @endif
                                                                    
                                                                </div>
                                                            </div>

                                                            <div class="form-group{{ $errors->has('edit_email') ? ' has-error' : '' }}">
                                                                <label for="edit_email" class="col-md-4 control-label" style="font-family: 'Jura', sans-serif; font-size: 16px; vertical-align: middle;">Correo Electrónico:</label>

                                                                <div class="col-md-6">
                                                                    <input id="edit_email" type="email" class="form-control" placeholder="{{ $user->email }}" name="edit_email" style="font-family: 'Jura', sans-serif; font-size: 16px" autofocus>

                                                                    @if ($errors->has('edit_email'))
                                                                        <span class="help-block">
                                                                            <strong>{{ $errors->first('edit_email') }}</strong>
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <br>

                                                            <div class="input-group col-md-6 form-group{{ $errors->has('edit_tipo_usuario') ? ' has-error' : '' }}" style="margin: auto auto auto auto">
                                                                <div class="input-group" style="width:150%; right: 50px">
                                                                    <div for="edit_tipo_usuario" class="input-group-addon"><b style="font-family: 'Poiret One', cursive;">Tipo de Usuario</b></div>
                                                                    <select id="edit_tipo_usuario" name="edit_tipo_usuario" class="form-control" required style="font-family: 'Jura', sans-serif; font-size: 16px">
                                                                        @if ($user->roleid == 1)
                                                                            <option value="1" style="font-family: 'Jura', sans-serif; font-size: 16px" selected> Administrador (Todos los Permisos) </option>
                                                                            <option value="2" style="font-family: 'Jura', sans-serif; font-size: 16px"> Invitado (Solo Carga de Archivos) </option>
                                                                        @else
                                                                            <option value="1" style="font-family: 'Jura', sans-serif; font-size: 16px"> Administrador (Todos los Permisos) </option>
                                                                            <option value="2" style="font-family: 'Jura', sans-serif; font-size: 16px" selected> Invitado (Solo Carga de Archivos) </option>
                                                                        @endif
                                                                        
                                                                    </select>

                                                                    @if ($errors->has('edit_tipo_usuario'))
                                                                        <span class="help-block">
                                                                            <strong>{{ $errors->first('edit_tipo_usuario') }}</strong>
                                                                        </span>
                                                                    @endif

                                                                </div>

                                                            </div>

                                                        </div>

                                                        <br>
                                                        
                                                        <div class="form-group">
                                                            <div class="modal-footer" style="text-align: center">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                                <button type="submit" class="btn btn-primary">Actualizar</button>
                                                            </div>
                                                        </div>
                                                    </form>

                                                  </div>
                                                  
                                                </div>
                                              </div>
                                            </div>

                                            @if($user->status == 1)
                                                <button onclick="get_url('{{ $user->id }}')" id="disable_user_btn" type="button" class="btn btn-danger" data-toggle="modal" data-target="#disable{{$user->id}}" data-whatever="@mdo" style="position: relative; width: 40px; height: 40px; border-radius: 100%; outline: none"><span id="disable_user_spn" class="fa fa-user-times" style="font-size: 1.5em; position: absolute; display: block; top: 20%; left: -18%"></span></button>
                                            @else
                                                <button onclick="get_url('{{ $user->id }}')" id="disable_user_btn" type="button" class="btn btn-success" data-toggle="modal" data-target="#disable{{$user->id}}" data-whatever="@mdo", style="position: relative; width: 40px; height: 40px; border-radius: 100%; outline: none"><span id="disable_user_spn" class="fa fa-user-plus" style="font-size: 1.5em; position: absolute; display: block; top: 20%; left: -18%"></span></button>
                                            @endif

                                            <div class="modal fade" id="disable{{$user->id}}" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                              <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                  <div class="modal-header">
                                                    <h3 class="modal-title" id="exampleModalLabel">DESACTIVAR USUARIO</h3>
                                                  </div>
                                                  <div class="modal-body">
                                                    
                                                    @if($user->status == 0)
                                                        ¿Desea activar al usuario {{$user->name}} {{$user->lastname}}? 
                                                    @else
                                                        ¿Desea desactivar al usuario {{$user->name}} {{$user->lastname}}? 
                                                    @endif

                                                    <form id="{{ $user->id }}" class="form-horizontal" role="form" method="POST" action="{{ url('desactivar_usuario') }}" novalidate>
                                                        {{ csrf_field() }}

                                                        <div class="row" id="info_edit_usuario{{ $user->id }}" style="display: none">

                                                            
                                                            <input id="edit_id_user" type="hidden" class="form-control" name="edit_id_user" value="{{ $user->id }}"/>
                                                            
                                                            <input id="disable_url{{ $user->id }}" type="hidden"  value="test" name="disable_url" />

                                                            <div class="form-group{{ $errors->has('edit_status') ? ' has-error' : '' }}" style="display:none">
                                                                <label for="edit_status" class="col-md-4 control-label"></label>

                                                                <div class="col-md-6">
                                                                    @if ($user->status == 1)
                                                                        <input id="edit_status" type="text" class="form-control" name="edit_status" value="0">
                                                                    @else
                                                                        <input id="edit_status" type="text" class="form-control" name="edit_status" value="1">
                                                                    @endif
                                                                </div>
                                                            </div>

                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <div class="modal-footer" style="text-align: center">
                                                                <button id="close_{{ $user->id }}" type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                                <button id="submit_{{ $user->id }}" type="submit" onclick="disable_user('{{ $user->id }}')" class="btn btn-primary">Confirmar</button>
                                                            </div>
                                                        </div>
                                                    </form>

                                                  </div>
                                                </div>
                                              </div>
                                            </div>

                                        </td>
                                    </tr>

                                @endforeach
                            </tbody>
                        </table>
    
                        <script type="text/javascript">

                            function get_url(id){

                                console.log('Entra a get URL '+ document.getElementById('disable_url' + id).value + '   ' + id);

                                //document.getElementById('disable_url' + id).value = 'urlbefore';

                                var url = document.URL;

                                $( document ).ready(function() {
                                    document.getElementById('edit_url'+ id).value = url;
                                    document.getElementById('disable_url' + id).value = url;
                                });

                                
                            }

                            function disable_user(id){

                                if ($('#edit_status' + id).val() == 1) {
                                    $('#disable_user_btn' + id).removeClass('btn btn-danger');
                                    $('#disable_user_spn' + id).removeClass('glyphicon glyphicon-remove-sign');
                                    
                                    $('#disable_user_btn' + id).addClass('btn btn-success');
                                    $('#disable_user_spn' + id).addClass('glyphicon glyphicon-ok');

                                } else {
                                    
                                    $('#disable_user_btn' + id).removeClass('btn btn-success');
                                    $('#disable_user_spn' + id).removeClass('glyphicon glyphicon-ok');

                                    $('#disable_user_btn' + id).addClass('btn btn-danger');
                                    $('#disable_user_spn' + id).addClass('glyphicon glyphicon-remove-sign');

                                }
                            }

                            $('#myTab a').click(function(e) {
                                e.preventDefault();
                                $(this).tab('show');
                            });

                            function clean_tab(){
                                $('#home_us').removeClass('show');
                            }

                            // store the currently selected tab in the hash value
                            $("ul.nav-tabs > li > a").on("shown.bs.tab", function(e) {
                                var id = $(e.target).attr("href").substr(1);
                                window.location.hash = id;
                            });

                            $('#alert').fadeIn('fast').delay(5000).fadeOut('slow');
                            // on load of the page: switch to the currently selected tab
                            var hash = window.location.hash;
                            $('#myTab a[href="' + hash + '"]').tab('show');
                        </script>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
