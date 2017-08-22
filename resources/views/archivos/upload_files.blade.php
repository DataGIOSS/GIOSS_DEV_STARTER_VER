@extends('layouts.menu')

@section('content')
<div class="container-fluid" style="margin-left: 65px;">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <h1 class="page-header" style="color: #7FFFD4; font-size: 3em; text-shadow: 0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 0 4px 0 #b9b9b9, 0 5px 0 #aaa, 0 6px 1px rgba(0,0,0,.1), 0 0 5px rgba(0,0,0,.1), 0 1px 3px rgba(0,0,0,.3), 0 3px 5px rgba(0,0,0,.2), 0 5px 10px rgba(0,0,0,.25), 0 10px 10px rgba(0,0,0,.2),0 20px 20px rgba(0,0,0,.15); font-family: 'Cinzel', serif;">Gestion De Archivos</h1>  
    
            <div class="panel panel-default" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">
                <div class="panel-heading" style="font-family: 'Poiret One', cursive; font-size: 20px">Carga de Archivos</div>

                <div class="panel-body">
                    <form id="cargaArchivos" role="form">
                        {{ csrf_field() }}
                        <div class="row form-group" style="margin: auto auto auto auto">
                            <h3><kbd>Periodo de Tiempo a Cargar</kbd></h3>
                            <hr>
                            <p style="font-family: 'Jura', sans-serif; font-size: 16px">Por favor señale el periodo de tiempo a cargar</p>
                        </div>

                        <div class="row">

                            <div class="col-md-1 form-group">

                                <div class="input-group">
                                    <div class="input-group-addon" style="font-family: 'Poiret One', cursive;"><b>Desde</b></div>
                                    <input type="date" name="fecha_ini" id="fecha_ini" class="form-control" value="2016-01-01" align="center"  style="font-family: 'Jura', sans-serif; font-size: 16px">
                                    <i class="input-group-addon glyphicon glyphicon-calendar" style="position: relative;top: 0px;"></i>
                                </div>

                            </div>

                            <div class="col-md-1 col-md-offset-6 form-group">

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
                            <button type="button" id="btnUpload" class="col-md-3 col-md-offset-5 btn btn-info btn-m disabled" style="width: 150px;font-family: 'Jura', sans-serif; font-size: 13px" disabled>
                              <span class="glyphicon glyphicon-plus-sign"></span> Cargar Archivos
                            </button>

                            <br> 
                            <br>

                            <div id="alert" class="form-group " style="display:none;" align="center">
                                <div class="alert alert-danger fade in" style="width: 90%; height: 60%; overflow-y: scroll;">
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
                            <div class="col-md-6" id="div_file_statuses" style="display:none;">
                               
                            </div>
                        </div>
                    </form>
                    
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
