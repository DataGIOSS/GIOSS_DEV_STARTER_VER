@extends('layouts.menu')

@section('content')
<div class="container-fluid" style="margin-left: 65px">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <h1 class="page-header" style="color: #7FFFD4; font-size: 3em; text-shadow: 0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 0 4px 0 #b9b9b9, 0 5px 0 #aaa, 0 6px 1px rgba(0,0,0,.1), 0 0 5px rgba(0,0,0,.1), 0 1px 3px rgba(0,0,0,.3), 0 3px 5px rgba(0,0,0,.2), 0 5px 10px rgba(0,0,0,.25), 0 10px 10px rgba(0,0,0,.2),0 20px 20px rgba(0,0,0,.15); font-family: 'Cinzel Decorative', serif;">Ayuda</h1>  
    
            <div class="panel panel-default" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">
                
                <div class="panel-heading" style="font-family: 'Poiret One', cursive; font-size: 20px">Guía Rápida de Uso</div>
                

                <div class="panel-body" style="font-family: 'Jura', sans-serif; font-size: 16px">
                    
                    <h3><kbd>Creación de Usuarios</kbd></h3><br>

                        <div class="jumbotron text-center" style="background-image: url('{{ asset('images/Crear_Usuario.png') }}'); height: 300px; background-size: 1000px;width: 100%"></div>

                        <p style="text-align: justify;">
                            Para la Creación de Usuarios es necesario que se diriga a la pestaña <strong><mark>CREAR USUARIOS</mark></strong> que podrá
                            encontrar en el menú situado al lado izquierdo de esta ventana. Una vez allí puede proceder a asignar los valores 
                            correspondientes al nombre, apellido, correo electrónico y la contraseña. Recuerde que todos los campos son
                            obligatorios.
                        </p> 
                        
                        <p style="text-align: justify;">
                            Adicionalmente para el uso del aplicativo se han generado dos perfiles que le permitirán facilitar la 
                            asignación de permisos para los usuarios que tengan acceso al aplicativo. Con el perfil de <strong>Administrador</strong> 
                            podrá hacer uso completo de la plataforma, sin restricciones. Con el perfil de <strong>Invitado</strong> solo podrá cargar
                            archivos de un determinado periodo mas no tendrá acceso a la generación de Reportes ni a la creación de nuevos Usuarios. 
                        </p>

                    <h3><kbd>Carga de Archivos</kbd></h3><br>

                        <div class="jumbotron text-center" style="background-image: url('{{ asset('images/Cargar_Archivos.png') }}'); height: 300px; background-size: 1000px; width: 100%"></div>

                        <p style="text-align: justify;">
                            Para la Carga de Archivos, debe entrar a la pestaña <strong><mark>CARGAR ARCHIVOS</mark></strong> que también enontrará en el 
                            menú desplegable a la izquierda de la ventana. En esta sección se le pedirá que indique el periodo comprendido
                            por el archivo a cargar.
                        </p>
                        <p style="text-align: justify;">
                            Recuerde que las primeras validaciones que se realizan son aquellas que corresponden al nombre del archivo que 
                            usted está cargando. Las validaciones se hacen sobre cada sección del nombre del archivo:
                        </p><br>

                        <dl class="row">
                          <dt class="col-sm-6">Módulo de Información</dt>
                          <dd class="col-sm-6" style="padding-bottom: 10px;text-align: justify;">
                            Este campo corresponde al valor <strong>SGD (Sistema de Gestión de Información)</strong> que será un valor fijo.
                          </dd>
                          
                          <hr style="padding: 0px; width: 98%;height: 10px; border: 0; box-shadow: 0 10px 10px -10px #8c8b8b inset;">

                          <dt class="col-sm-6">Tipo Fuente</dt>
                          <dd class="col-sm-6" style="padding-bottom: 20px;text-align: justify;">
                            Este campo corresponde a la entidad que es fuente de la información, será también un valor fijo <strong>239 (Institución Prestadora de servicios de salud IPS)</strong>.
                          </dd>

                          <hr style="padding: 0px; width: 98%;height: 10px; border: 0; box-shadow: 0 10px 10px -10px #8c8b8b inset;">

                          <dt class="col-sm-6">Tema de Información</dt>
                          <dd class="col-sm-6" style="padding-bottom: 10px;text-align: justify;">
                            Este campo corresponde al tipo de archivo a cargar y solo puede tener un valor equivalente a los disponibles en el seleccionador de tipo que aparece en la interfaz de Carga de Archivos <strong>(AAC, AVA, APS...)</strong>. Si el valor ingresado no corresponde a un tipo de archivo conocido este será rechazado.
                          </dd>

                          <hr style="padding: 0px; width: 98%;height: 10px; border: 0; box-shadow: 0 10px 10px -10px #8c8b8b inset;">

                          <dt class="col-sm-6">Mes Reportado</dt>
                          <dd class="col-sm-6" style="padding-bottom: 10px;text-align: justify;">
                            Este valor corresponde al mes en el que inicia el reporte del archivo a cargar en el formato Año - Mes <strong>(AAAA-MM)</strong>.
                          </dd>

                          <hr style="padding: 0px; width: 98%;height: 10px; border: 0; box-shadow: 0 10px 10px -10px #8c8b8b inset;">

                          <dt class="col-sm-6">Fecha de Inicio de Periodo de Corte</dt>
                          <dd class="col-sm-6" style="padding-bottom: 10px;text-align: justify;">
                            Este campo corresponde al primer día calendario del mes a reportar y debe ser reportado en el formato <strong>(AAAA-MM-DD)</strong>.
                          </dd>

                          <hr style="padding: 0px; width: 98%;height: 10px; border: 0; box-shadow: 0 10px 10px -10px #8c8b8b inset;">

                          <dt class="col-sm-6">Fecha de Cierre Periodo de Corte</dt>
                          <dd class="col-sm-6" style="padding-bottom: 10px;text-align: justify;">
                            Este campo corresponde al último día calendario del mes a reportar y debe ser reportado en el formato <strong>(AAAA-MM-DD)</strong>.
                          </dd>

                          <hr style="padding: 0px; width: 98%;height: 10px; border: 0; box-shadow: 0 10px 10px -10px #8c8b8b inset;">

                          <dt class="col-sm-6">Tipo de Identificación de la Entidad que Reporta</dt>
                          <dd class="col-sm-6" style="padding-bottom: 10px;text-align: justify;">
                            Este campo coresponde al identificador del tipo de documento de identificación de la entidad que reporta. Esta sección tendrá un valor fijo que será <strong>NIT (tipo de identificación de la FVL)</strong>
                          </dd>

                          <hr style="padding: 0px; width: 98%;height: 10px; border: 0; box-shadow: 0 10px 10px -10px #8c8b8b inset;">

                          <dt class="col-sm-6">Número de Identificación de la Entidad que Reporta</dt>
                          <dd class="col-sm-6" style="padding-bottom: 10px;text-align: justify;">
                            Este campo corresponde al Número de Documento de Identificación de la entidad que reporta. Para este campo se exige en el formato que el valor tenga una cantidad de 12 caracteres, si el valor tiene una longitud menor, debe ser completado con ceros a la izquierda. El valor de este campo será fijo: <strong>000890324177 (Número del NIT de la FVL)</strong>
                          </dd>

                          <hr style="padding: 0px; width: 98%;height: 10px; border: 0; box-shadow: 0 10px 10px -10px #8c8b8b inset;">

                          <dt class="col-sm-6">Código de Habilitación de la Entidad que Reporta</dt>
                          <dd class="col-sm-6" style="padding-bottom: 10px;text-align: justify;">
                            Este campo corresponde al código de habilitación de la entidad que reporta. Tendrá también un valor fijo que será <strong>760010287001 (Código de Habilitación de la FVL)</strong>
                          </dd>

                          <hr style="padding: 0px; width: 98%;height: 10px; border: 0; box-shadow: 0 10px 10px -10px #8c8b8b inset;">

                          <dt class="col-sm-6">Versión</dt>
                          <dd class="col-sm-6" style="padding-bottom: 10px;text-align: justify;">
                            Ya que el usuario debe cargar el archivo que corresponde a un mismo periodo las veces que sean necesarias hasta que todos los registros sean en su totalidad exitosos, se da la posibilidad de asignar un número de <strong>Versión</strong> después del <strong>Código de Habilitación</strong> de la entidad en el archivo que está cargando, es decir, si ya usted cargó el archivo y este fue procesado, pero fue necesario realizar alguna correción de registros, usted deberá asignar un valor entre <strong>0</strong> y <strong>100</strong> al final del nombre del archivo. Este valor debe ser cambiado para cada nueva versión del archivo que se busca procesar con exito.
                          </dd>

                          <hr style="padding: 0px; width: 98%;height: 10px; border: 0; box-shadow: 0 10px 10px -10px #8c8b8b inset;">

                        </dl>

                        <p style="text-align: justify;">
                            El nombre del archivo a cargar deberá tener un formato similar al ejemplo presentado a continuación: 
                            <ul>
                              <li>
                                <p style="text-shadow: -1px 0.5px red; word-wrap: break-word;">
                                  Nombre del archivo Sin Versión <br>
                                  <strong style="text-shadow: initial;">SGD239AAC2016082016080120160810NIT000890324177760010287001.TXT</strong>
                                </p>
                              </li>

                              <li>
                                <p style="text-shadow: -1px 0.5px red;word-wrap: break-word;">
                                  Nombre del archivo Con Versión <br>
                                  <strong style="text-shadow: initial;">SGD239AAC2016082016080120160810NIT000890324177760010287001<mark>01</mark>.TXT</strong>
                                </p>
                              </li>
                            </ul>
                            
                            <p style="text-align: justify;">
                                Al ingresar a la pestaña de Carga de Archivos deberá seleccionar Desde y Hasta qué fecha comprende el periodo del archivo que va a cargar. Este rango de fechas debe ser igual a la <strong>Fecha de Inicio de Periodo de Corte</strong> y a la <strong>Fecha de Cierre Periodo de Corte</strong> que se encuentran en el nombre del archivo. Una vez indicado el periodo debe hacer clic en el botón <mark style="background-color: lightgreen"> Adicionar Archivo </mark>, al hacerlo aparecerá un panel que contiene una lista donde usted deberá seleccionar el Tipo de Archivo que desea cargar a continuación, hecho esto deberá Hacer clic en el botón <mark  style="background-color: lightyellow">Seleccionar Archivo</mark> para poder seleccionar el archivo que se procesará. Si ya ha ejecutado estos pasos correctamente, podrá cargar el archivo al sistema haciendo clic en el botón <mark  style="background-color: lightblue">Cargar Archivo</mark>.
                            </p>
                            <p style="text-align: justify;">
                                Al hacer esto, el aplicativo realizará las primeras validaciones que corresponden al nombre del archivo, si allí existe alguna incongruencia se desplegará un <strong>Panel de Errores</strong> donde se le indicará lo que ha fallado, así mismo ocurrirá con la primera linea del archivo que contiene: <strong>Código de Habilitación de la Entidad, Mes Reportado, Fecha de Inicio del Periodo de Corte, Fecha de Cierre del Periodo de Corte</strong> y el <strong>Número total de Registros Contenidos</strong>. Si el Archivo que se intentó cargar ha fallado en los parámetros del nombre o en la primera linea del archivo, no hace falta que usted modifique la <strong>Versión</strong> del archivo cargado, ya que al fallar en las primeras validaciones este no se almacenó en los registros.     
                            </p>

                            <p style="text-align: justify;">
                                Una vez se cargue el archivo al aplicativo, este le mostrará al usuario el progreso de carga del archivo. Al finalizar el proceso n el mismo panel de progreso el usuario podrá encontrar el estado final de la carga del archivo, si el estado es <strong>FALLIDO</strong> es porque el aplicativo no encontró ningún registro que pasara correctamente las validaciones, si el resultado es <strong>REGULAR</strong> es porque se encontraron algunos registros buenos y otros incorrectos y pendientes por correegir, si el estado final de <strong>EXITOSO</strong> es porque todos los registros del archivo cargado fueron correctos y no hace falta cargar nuevamente este archivo.
                            </p>
                            <p style="text-align: justify;">
                                Desde el panel de progreso, al finalizar la carga de cada archivo el usuario podrá <strong>DESCARGAR</strong> el resultado del procesamiento. Este resultado estará contenido en una carpeta comprimida de extensión <strong>.ZIP</strong> y en su interior estarán: el archivo original que se cargó para ser procesado, el archivo de registros exitosos (si es que hubo al menos uno), el archivo de registros fallidos (si hubo alguno) y por último el archivo de detalles de los errores encontrados en los registros que fallaron (este archivo solo se genera si se encontraron registros fallidos).
                            </p>
                            <p style="text-align: justify;">
                                Una vez cargado un archivo o un grupo de archivos, el usuario deberá corregir los registros fallidos en el archivo original e ingresar una nueva versión de
                                este para que todos los datos sean insertados correctamente en la Bodega de Datos.
                            </p>

                        </p>

                      <h3><kbd>Creación de Reportes</kbd></h3><br>

                      <div class="jumbotron text-center" style="background-image: url('{{ asset('images/Generar_Reporte.png') }}'); height: 300px; width: 100%; background-size: 1000px"></div>

                      Para la Creación de Reportes es indispensable que el usuario haya cargado todos los archivos correspondientes al periodo a reportar para que así el sistema pueda extraer la información necesaria. Si ya han sido cargados todos los archivos puede ingresar a la pestaña <strong><mark>GENERAR REPORTE</mark></strong> que podrá encontrar en el menú desplegable. Una vez allí basta con que indique el periodo que desea reportar y que debe ser equivalente al periodo de los archivos cargó. 

                      <blockquote class="blockquote blockquote-reverse" style="padding-top: 20px">
                          <footer class="blockquote-footer" style="font-size: 18px"> <strong>Equipo de Desarrollo <cite title="Source Title">DataGIOSS</cite></strong></footer>
                      </blockquote>

                </div>
                
            </div>

        </div>
    </div>
</div>
@endsection