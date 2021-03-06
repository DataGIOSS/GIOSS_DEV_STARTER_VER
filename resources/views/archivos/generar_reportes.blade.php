@extends('layouts.menu')

@section('content')
<div class="container-fluid" style="margin-left: 65px;">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <h1 id="content_header" class="page-header" > Generación de Reportes </h1>  
    
            <div class="panel panel-default" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">
                <div class="panel-heading" style="font-family: 'Poiret One', cursive; font-size: 20px">Elaborar Archivo a Reportar</div>

                <div class="panel-body">
                    <form id="generarReporte" role="form">
                        {{ csrf_field() }}
                        <div class="row form-group" style="margin: auto auto auto auto">
                            <h3><kbd>Periodo de Tiempo a Reportar</kbd></h3>
                            <hr>
                            <p style="font-family: 'Jura', sans-serif; font-size: 16px">Por favor señale el periodo de tiempo a reportar</p>
                        </div>
                        
                        <div class="row">
                            
                            <div class="col-md-4 col-md-offset-3 form-group" style="margin-top: 20px;">
                                <div class="input-group input-daterange">
                                    <div class="input-group-addon" style="font-family: 'Poiret One', cursive;"><strong>Desde</strong></div>
                                    <input type="date" class="form-control" value="2016-01-01" align="center"  style="font-family: 'Jura', sans-serif; font-size: 16px; width: 157px">
                                    <div class="input-group-addon" style="font-family: 'Poiret One', cursive;"><strong>Hasta</strong></div>
                                    <input type="date" class="form-control" value="2016-01-01"  style="font-family: 'Jura', sans-serif; font-size: 16px; width: 157px">
                                </div>
                            </div>

                        </div><hr>
                        <div class="row">
                            <div class="col-md-4 form-group"></div>
                            
                            <div class="col-md-4 form-group" style="margin-top: 0px; margin-bottom: 0px;" align="center">
                                <button type="button" id="btnReport" class="btn btn-info btn-m" style="font-family: 'Jura', sans-serif; width: 170px">
                                    <i class="fa fa-send-o" style="font-size: 15px; height: 15px; top: 1px; text-align: center">&nbsp;&nbsp;<span style="font-family: 'Jura', sans-serif">Generar Reporte</span></i> 
                                </button>    
                            </div>
                            
                            <div class="col-md-4 form-group"></div>

                        </div>
                        
                        <div id="alert" class="form-group " style="display:none;" align="center">
                            <div class="alert alert-danger" style="width: 700px; height: 150px; overflow-y: scroll;">
                                <h4><strong>Error al generar el reporte!</strong></h4>
                                <div id="error_area" style="text-align: left;"></div>
                            </div>
                        </div>

                        <div class="row" id="divgif" style="display:none;" align="center"></div>
                        
                        <div class="row">
                            <div class="col-md-6" id="div_file_statuses" style="display:none;"></div>
                        </div>

                    </form>
                    
                </div>
            </div>

        </div>
    </div>
</div>

{{ Html::script(asset("js/reports.js")) }}

@endsection