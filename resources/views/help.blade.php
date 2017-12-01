@extends('layouts.menu')

@section('content')
<div class="container-fluid" style="margin-left: 65px">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <h1 class="page-header" style="color: #7FFFD4; font-size: 3em; text-shadow: 0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 0 4px 0 #b9b9b9, 0 5px 0 #aaa, 0 6px 1px rgba(0,0,0,.1), 0 0 5px rgba(0,0,0,.1), 0 1px 3px rgba(0,0,0,.3), 0 3px 5px rgba(0,0,0,.2), 0 5px 10px rgba(0,0,0,.25), 0 10px 10px rgba(0,0,0,.2),0 20px 20px rgba(0,0,0,.15); font-family: 'Cinzel Decorative', serif;">Ayuda</h1>  
    
            <div class="panel panel-default" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">
                
                <div class="panel-heading" style="font-family: 'Poiret One', cursive; font-size: 20px">Guía Rápida de Uso</div>
                

                <div class="panel-body" style="font-family: 'Jura', sans-serif; font-size: 16px">
                    
                    <h3><kbd>Gestión de Usuarios</kbd></h3><br>

                        <div class="jumbotron text-center" style="background-image: url('{{ asset('images/Creacion_Usuarios.png') }}'); height: 300px; background-size: 1200px;width: 100%"></div>

                        <p style="text-align: justify;">
                            Para la Creación de Usuarios es necesario que se dirija a la opción <strong><mark>GESTIONAR USUARIOS</mark></strong> que podrá
                            encontrar en el menú situado al lado izquierdo de esta ventana. Una vez allí debe ubicarse en la pestaña <strong><em>Creación de Usuarios 
                            del Aplicativo</em></strong> donde podrá proceder a asignar los valores correspondientes del nuevo usuario: Nombre, Apellido, Correo
                            Electrónico y Contraseña. Recuerde que todos los campos son obligatorios y si no han sido correctamente diligenciados el sistema 
                            no procederá a crear el usuario.
                        </p> 
                        
                        <p style="text-align: justify;">
                            Adicionalmente para el uso del aplicativo se han generado dos perfiles que le permitirán facilitar la 
                            asignación de permisos para los usuarios que tengan acceso al aplicativo. Con el perfil de <strong>Administrador</strong> 
                            podrá hacer uso completo de la plataforma, sin restricciones. Con el perfil de <strong>Invitado</strong> solo podrá cargar
                            archivos de un determinado periodo mas no tendrá acceso a la generación de Reportes ni a la creación de nuevos Usuarios. 
                        </p>

                        <p style="text-align: justify;">
                            Con el fin de conservar un registro de usuarios que han sido creados en el sistema y para facilitar el manejo de los permisos de 
                            acceso se creó un atributo adicional que señala el estado actual de cada usuario que puede variar entre <strong>ACTIVO</strong> e 
                            <strong>INACTIVO</strong> y cabe resaltar que por defecto cada nuevo usuario es creado en estado inactivo. Para modificar cualquier
                            parámetro de la información de los usuarios existentes es necesario dirigirse a la pestaña de <strong><em>Listado de Usuarios.</em></strong>
                        </p>

                        <div class="jumbotron text-center" style="background-image: url('{{ asset('images/Listado_Usuarios.png') }}'); height: 300px; background-size: 1200px;width: 100%"></div>

                        <p style="text-align: justify;">
                            En esta pestaña usted encontrará un registro de todos los usuarios que han sido creados para el manejo del sistema. Toda la información
                            de cada usuario, así como el tipo que le fue asignado al momento de la creación. En esta misma interfaz usted podrá modificar la
                            información del usuario que desee (a excepción del usuario con el que ha ingresado y de un administrador general del sistema). En la 
                            parte derecha de cada registro podrá observar dos botones: uno para la edición de la información del usuario 
                            <img src="{{ asset('images/editar.png') }}" height="25px" width="25px"> y otro para Activar o Desactivar el usuario y permitir/denegar su acceso al sistema 
                            <img src="{{ asset('images/desactivar.png') }}" height="25px" width="25px"><img src="{{ asset('images/activar.png') }}" height="25px" width="25px">.
                        </p>

                        <p style="text-align: justify;">
                            Al ingresar a la opción de edición de usuario se desplegará un formulario donde se podrá diligenciar la información que se desee editar de
                            la cuenta seleccionada. Solo hace falta diligenciar los campos cuya información requiere de algún tipo de actualización, los demás pueden 
                            permanecer en blanco. Si los campos no son diligenciados correctamente se volverá a abrir el formulario indicando los errores ocurridos,
                            de lo contrario en la interfaz de Listado de Usuarios se indicará que la actualización de la información del usuario fue exitosa. 
                        </p>

                        <div class="jumbotron text-center" style="background-image: url('{{ asset('images/edicion_usuario.png') }}'); height: 300px; background-size: 1200px;width: 100%"></div>
                        <div class="jumbotron text-center" style="background-image: url('{{ asset('images/actualizacion_exitosa.png') }}'); height: 300px; background-size: 1200px;width: 100%"></div>

                        <p style="text-align: justify;">
                            Cabe resaltar que <img src="{{ asset('images/desactivar.png') }}" height="25px" width="25px"> es la opción para desactivar el usuario, lo cual indica que 
                            si el registro del usuario está acompañado de este icono es porque el usuario está activado, por el contrario si el icono es <img src="{{ asset('images/activar.png') }}" height="25px" width="25px">, entonces el usuario está inactivo y la opción permitirá activarlo. De cualquier modo, al hacer clic en esta opción
                            el sistema desplegará un mensaje de confirmación donde el usuario deberá aceptar la Activación o Desactivación (según sea el caso) del usuario 
                            seleccionado.
                        </p>

                        <div class="jumbotron text-center" style="background-image: url('{{ asset('images/Activacion_Usuario.png') }}'); height: 300px; background-size: 1200px;width: 100%"></div>

                    <h3><kbd>Carga de Archivos</kbd></h3><br>

                        <div class="jumbotron text-center" style="background-image: url('{{ asset('images/Carga_Archivos.png') }}'); height: 300px; background-size: 1200px; width: 100%"></div>

                        <p style="text-align: justify;">
                            Para la Carga de Archivos, debe entrar a la opción <strong><mark style="background-color: #FFE7FFFF;">CARGAR ARCHIVOS</mark></strong> que también encontrará en el 
                            menú desplegable a la izquierda de la ventana. En esta sección se le pedirá que indique el periodo comprendido por el archivo a cargar y que seleccione 
                            además el Archivo correspondiente y su Tipo.
                        </p>
                        <p style="text-align: justify;">
                            Recuerde que las primeras validaciones que se realizan sobre el archivo seleccionado son aquellas que corresponden al nombre del archivo que 
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
                            Ya que el usuario debe cargar el archivo que corresponde a un mismo periodo las veces que sean necesarias hasta que todos los registros sean en su totalidad
                            exitosos, se da la posibilidad de asignar un número de <strong>Versión</strong> después del <strong>Código de Habilitación</strong> de la entidad en el archivo
                            que está cargando, es decir, si ya usted cargó el archivo y este fue procesado, pero fue necesario realizar alguna correción de registros, usted deberá asignar
                            un valor entre <strong>0</strong> y <strong>100</strong> al final del nombre del archivo. Este valor debe ser cambiado para cada nueva versión del archivo que
                            se busca procesar con exito.
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
                                El rango de fechas establecido por el usuario debe ser igual a la <strong>Fecha de Inicio de Periodo de Corte</strong> y a la <strong>Fecha de Cierre Periodo 
                                de Corte</strong> que se encuentran en el nombre del archivo, es decir debe comprender exactamente el mismo periodo a reportar. Una vez indicado el periodo debe
                                hacer clic en el botón <mark style="background-color: lightgreen">Adicionar Archivo</mark> que se encuentra en la parte inferior de la interfaz, al hacerlo 
                                aparecerá un pequeño panel que contiene una lista donde usted deberá seleccionar el Tipo de Archivo que desea cargar. Hecho esto deberá Hacer 
                                clic en el botón <mark  style="background-color: lightyellow">Seleccionar Archivo</mark> para poder seleccionar el archivo que se procesará. Si ya ha ejecutado
                                estos pasos correctamente, podrá cargar el archivo al sistema haciendo clic en el botón <mark  style="background-color: lightblue">Cargar Archivo</mark>.
                            </p>

                            <p style="text-align: justify;">
                                Al hacer esto, el aplicativo realizará las primeras validaciones que corresponden al nombre del archivo, si allí existe alguna incongruencia se desplegará un <strong>
                                Panel de Errores</strong> donde se le indicará lo que ha fallado, así mismo ocurrirá con la primera línea del archivo que contiene: <strong>Código de Habilitación de 
                                la Entidad, Mes Reportado, Fecha de Inicio del Periodo de Corte, Fecha de Cierre del Periodo de Corte</strong> y el <strong>Número total de Registros Contenidos</strong>.
                                Si el Archivo que se intentó cargar ha fallado en los parámetros del nombre o en la primera línea del archivo, no hace falta que usted modifique la <strong>Versión</strong>
                                del archivo cargado, ya que al fallar en las primeras validaciones este no es almacenado en los registros y puede ser cargado nuevamente.
                            </p>

                            <div class="jumbotron text-center" style="background-image: url('{{ asset('images/error_carga.png') }}'); height: 300px; background-size: 1200px; width: 100%"></div>

                            <p style="text-align: justify;">
                                Una vez se cargue el archivo al aplicativo, este le mostrará al usuario el progreso de carga del archivo. Al finalizar el proceso en el mismo panel de progreso el
                                usuario podrá encontrar el estado final de la carga del archivo, si el estado es <strong>FALLIDO</strong> es porque el aplicativo no encontró ningún registro que
                                pasara correctamente las validaciones, si el resultado es <strong>REGULAR</strong> es porque se encontraron algunos registros buenos y otros incorrectos y pendientes
                                por corregir, si el estado final de <strong>EXITOSO</strong> es porque todos los registros del archivo cargado fueron correctos y no hace falta cargar nuevamente
                                este archivo.
                            </p>

                            <div class="jumbotron text-center" style="background-image: url('{{ asset('images/archivo_cargando.png') }}'); height: 300px; background-size: 1200px; width: 100%"></div>
                            
                            <p style="text-align: justify;">
                                Una vez se finalice el procesamiento el panel mostrará al usuario el estado final y el enlace de descarga de los resultados:
                            </p>

                            <div class="jumbotron text-center" style="background-image: url('{{ asset('images/Archivo_finalizado.png') }}'); height: 300px; background-size: 1200px; width: 100%"></div>

                            <p style="text-align: justify;">
                                Desde el panel de progreso, al finalizar la carga de cada archivo el usuario podrá <strong>DESCARGAR</strong> el resultado del procesamiento. Este resultado estará 
                                contenido en una carpeta comprimida de extensión <strong>.ZIP</strong> y en su interior estarán: el archivo original que se cargó para ser procesado, el archivo de
                                registros exitosos (si es que hubo al menos uno), el archivo de registros fallidos (si hubo alguno) y por último el archivo de detalles de los errores encontrados
                                en los registros que fallaron (este archivo solo se genera si se encontraron registros fallidos).
                            </p>

                            <p style="text-align: justify;">
                                Una vez cargado un archivo o un grupo de archivos, el usuario deberá corregir los registros fallidos en el archivo original e ingresar una nueva versión de
                                este para que todos los datos sean insertados correctamente en la Bodega de Datos.
                            </p>

                            <p style="text-align: justify;">
                                Adicionalmente en la pestaña <strong><em>Archivos Cargados</em></strong> podrá ver el listado de archivos que han sido procesados y la información correspondiente
                                al usuario que lo procesó la fecha y la hora de carga. Además de esto podrá encontrar el estado final del procesamiento del archivo y el enlace de descarga
                                para acceder a los resultados del procesamiento.
                            </p>

                            <div class="jumbotron text-center" style="background-image: url('{{ asset('images/procesando.png') }}'); height: 300px; background-size: 1200px; width: 100%"></div>
                            
                        </p>

                      <h3><kbd>Creación de Reportes</kbd></h3><br>

                      <p>... Módulo en desarrollo...</p>

                      {{-- <div class="jumbotron text-center" style="background-image: url('{{ asset('images/Generar_Reporte.png') }}'); height: 300px; width: 100%; background-size: 1200px"></div>

                      Para la Creación de Reportes es indispensable que el usuario haya cargado todos los archivos correspondientes al periodo a reportar para que así el sistema pueda extraer la información necesaria. Si ya han sido cargados todos los archivos puede ingresar a la pestaña <strong><mark>GENERAR REPORTE</mark></strong> que podrá encontrar en el menú desplegable. Una vez allí basta con que indique el periodo que desea reportar y que debe ser equivalente al periodo de los archivos cargó. --}}

                      <blockquote class="blockquote blockquote-reverse" style="padding-top: 20px">
                          <footer class="blockquote-footer" style="font-size: 18px"> <strong>Equipo de Desarrollo <cite title="Source Title">DataGIOSS</cite></strong></footer>
                      </blockquote>

                </div>
                
            </div>

        </div>
    </div>
</div>
@endsection