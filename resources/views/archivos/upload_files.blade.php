@extends('layouts.menu')

@section('content')
<div class="container-fluid" style="margin-left: 65px;">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <h1 id="content_header" class="page-header" >Gestion De Archivos</h1> 

              <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" onclick="stop_load_table()" href="#home" style="font-family: 'Poiret One', cursive; font-size: 20px;"><b>Carga de Archivos</b></a></li>
                <li><a data-toggle="tab" href="#menu1" onclick="load_table()" style="font-family: 'Poiret One', cursive; font-size: 20px;"><b>Archivos Cargados</b> <img id="loading_table_img" style="display: none" src="{{asset("images/preloader.gif")}}"></a></li>
              </ul>

              <div class="tab-content">
                <div id="home" class="tab-pane fade in active panel" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">
                    {{-- <div class="panel-heading">Carga de Archivos</div> --}}

                    <div class="panel-body">
                        <form id="cargaArchivos" role="form">
                            {{ csrf_field() }}
                            <div class="row form-group" style="margin: auto auto auto auto">
                                <h3><kbd>Periodo de Tiempo a Cargar</kbd></h3><br>
                                <p style="font-family: 'Jura', sans-serif; font-size: 16px">Por favor señale el periodo de tiempo a cargar</p>
                                <hr>
                            </div>

                            <div class="row">

                                <div class="col-md-1 col-md-offset-1 form-group">

                                    <div class="input-group">
                                        <div class="input-group-addon" style="font-family: 'Poiret One', cursive;"><b>Desde</b></div>
                                        <input type="date" name="fecha_ini" id="fecha_ini" class="form-control" value="2016-01-01" align="center"  style="font-family: 'Jura', sans-serif; font-size: 16px">
                                        <i class="input-group-addon glyphicon glyphicon-calendar" style="position: relative;top: 0px;"></i>
                                    </div>

                                </div>

                                <input id="current_date" type="hidden" name="current_date">
                                <input id="current_time" type="hidden" name="current_time">
                                <input id="current_user" type="hidden" name="current_user" value="{{ Auth::user()->email }}">

                                <div class="col-md-1 col-md-offset-5 form-group">

                                    <div class="input-group">
                                        <div class="input-group-addon" style="font-family: 'Poiret One', cursive;"><b>Hasta</b></div>
                                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="2016-01-01"  style="font-family: 'Jura', sans-serif; font-size: 16px">
                                        <i class="input-group-addon glyphicon glyphicon-calendar" style="position: relative;top: 0px;"></i>
                                    </div>

                                </div>
                            </div>
                            <hr>
                            <div class="row" style="margin: auto auto auto auto;">
                                <button type="button" id="add_file"  class="col-md-3 col-md-offset-1 btn btn-success btn-m" style="width: 150px;font-family: 'Jura', sans-serif; font-size: 13px">
                                  <span class="glyphicon glyphicon-plus-sign"></span> Adicionar Archivo
                                </button>
                                <button type="button" id="btnUpload" class="col-md-3 col-md-offset-6 btn btn-info btn-m disabled" style="width: 150px;font-family: 'Jura', sans-serif; font-size: 13px right: -60px" disabled>
                                  <span class="glyphicon glyphicon-plus-sign"></span> Cargar Archivos
                                </button>

                                <br> 
                                <br>

                                <div id="alert" class="form-group " style="display:none;" align="center">
                                    <div class="alert alert-danger fade in" style="width: 100%; height: 60%; overflow-y: scroll;">
                                        <h4><strong>Error al cargar los archivos!</strong></h4>
                                        <div id="error_area" style="text-align: left;"></div>
                                    </div>
                                </div>
                                
                            </div>

                            <div class="row" id="divgif" align="center" style="margin-top: 5px">
                                
                            </div>
                            <div class="row" style="margin-top: 5px">
                                <div class="col-md-6" id="files_div">
                                    
                                </div>
                                <div class="col-md-6 col-md-offset-3" id="div_file_statuses" style="display: none; width: 50%; text-align: left;">
                                   
                                </div>
                            </div>
                        </form>
                        
                    </div>
                </div>

                <div id="menu1" class="tab-pane fade panel" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); min-width: 100%">
                    <div class="panel-body" style="min-width: inherit">
                        
                        <table class="table table-striped" style="overflow-x: scroll; min-width: inherit">
                            <thead style="font-family: 'Jura', sans-serif; font-size: 15px; text-align: center">
                                <th style="max-width: 150px; text-align: center;">NOMBRE ARCHIVO</th>
                                <th style="text-align: center;">TIPO DE ARCHIVO</th>
                                <th style="text-align: center;"># LINEAS</th>
                                <th style="text-align: center;">PORCENTAJE PROCESADO</th>
                                <th style="text-align: center;">ESTADO ACTUAL</th>
                                <th style="text-align: center;">RESULTADO FINAL</th>
                                <th style="text-align: center;">RESULTADOS</th>
                                <th style="text-align: center;">USUARIO</th>
                                <th style="text-align: center;">FECHA DE CARGA</th>
                                <th style="text-align: center;">HORA DE CARGA</th>
                                {{-- <th style="text-align: center;">ANULAR CARGA</th> --}}
                            </thead>
                            <tbody id="loaded_files" style="font-family: 'Jura', sans-serif; font-size: 15px; text-align: center">

                            </tbody>
                        </table>
                        
                    </div>
                </div>

             </div>
        </div>
    </div>
</div>

<script>
    var route = "{{ route('uploading') }}"; 
    var status_file_route = "{{ route('status_files') }}";
    var loadGif = ' <img src="{{asset("images/30.gif")}}"/>'
</script>
{{ Html::script(asset("js/uploadfiles.js")) }}
@endsection
