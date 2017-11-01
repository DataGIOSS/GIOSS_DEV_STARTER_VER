<?php

namespace App\Classes;

use App\Classes\FileValidator;
use App\Traits\ToolsForFilesController;
use App\Models\DiagnosticoCiex;
use App\Models\TipoDiagnostico;
use App\Models\FileStatus;
use App\Models\Archivo;
use App\Models\UserIp;
use App\Models\Registro;
use App\Models\Eapb;
use App\Models\RegistroCancer;
use App\Models\EntidadesSectorSalud;
use App\Models\GiossArchivoArcCfvl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ARC extends FileValidator {

  //Aquí se hace la apertura del archivo que fue guardado en la ruta que le pasa al constructor y que corresponde a la ruta de la carpeta Storage del proyecto en Laravel.

  function __construct($pathfolder, $fileName,$consecutive) {
    $filePath = $pathfolder.$fileName;
    $conteoLineas = $this->countLine($filePath);
    if(!($this->handle = fopen($filePath, 'r'))) throw new Exception("Error al abrir el archivo ARC");
    
    //dd($fileName);
    $this->folder = $pathfolder;

    $fileNameToken = explode('.',$fileName);
    $this->fileName =  substr($fileNameToken[0],0,58);
    $this->version = substr($fileNameToken[0],58);

    $this->consecutive = $consecutive;
    $this->detail_erros = array(['No. línea archivo original', 'No. linea en archivo de errores','Campo', 'Descripción']);
    $this->wrong_rows =  array();
    $this->success_rows =  array();
    
  }

  //Aquí se harán las validaciones correspondientes al contenido de cada registro que se trajo desde el archivo cargado.
  public function manageContent() {
    try {
      Log::info("Inicia manageContent (ARC - Linea 53)");

      // se validad la existencia del archivo
      $isValidFile = true;
      $fileid = 0;

      //Log::info("manageContent (ARC - Linea 59)");
      $exists = DB::table('archivo')->where('nombre', $this->fileName)
                ->where('version', $this->version)
                ->first(); 
      //Log::info("manageContent (ARC - Linea 63)");
      //Si el archivo ya ha sido guardado en la base de datos, este debe descartarse 
      if($exists){
        Log::info("Entra al if (ARC - Linea 66)");
        $isValidFile = false;
        array_push($this->detail_erros, [0, 0, '', "El archivo ya fue gestionado. Por favor actualice la version"]);
        $fileid = $exists->id_archivo_seq;
      //Si dicho archivo aún no existe en la base de datos entonces ahí si se guarda y es sometido a las validaciones necesarias
      } else {
          Log::info("Entra al else (ARC - Linea 68)");
          //Se define en primera instancia el objeto archivo
      
          $this->archivo = new Archivo();
          Log::info("Crea el objeto archivo (ARC - Linea 72)");
          $this->archivo->modulo_informacion = 'SGD';
          Log::info("Le asigna el módulo (ARC - Linea 74): ".'SGD');
          $this->archivo->nombre = $this->fileName;
          Log::info("Le asigna el nombre (ARC - Linea 76): ".$this->fileName);
          $this->archivo->version = $this->version;
          Log::info("Le asigna la version (ARC - Linea 78): ".$this->version);
          $this->archivo->id_tema_informacion = 'ARC';
          Log::info("Le asigna el id_tema_informacion (ARC - Linea 80): ".'ARC');
          $this->archivo->save(); //Falla esta linea inexplicablemente
          Log::info("Guarda el archivo (ARC - Linea 83)");

          $fileid = $this->archivo->id_archivo_seq;

      }


      Log::info("manageContent (ARC - Linea 85)");
      // se inicializa el objeto file_status 
      $this->file_status =  new FileStatus();
      $this->file_status->consecutive = $this->consecutive;
      $this->file_status->archivoid = $fileid;
      $this->file_status->current_status = 'PROCCESING';
      Log::info("Guarda el FileStatus (ARC - Linea 85)");
      $this->file_status->save();
      Log::info("Finalizó la creacion del FileStatus (ARC - Linea 85)");
      //Este booleano verifica la validez del registro de control
      $isValidFirstRow = true;
      
      //En este punto se toma lo que el apuntador leyó de la primera linea para hacer la respectiva validación
      $firstRow = fgetcsv($this->handle, 0, "|");
      
      $this->validateFirstRow($isValidFirstRow, $this->detail_erros, $firstRow);

      // Si en este punto se ha verificado el el archivo es válido y que además la primera fila ha
      // pasado las validaciones y sigue siendo una fila válida, entonces se continua la
      // construcción del objeto Archivo que será almacenado
      Log::info("manageContent (ARC - Linea 105)");
      if ($isValidFirstRow && $isValidFile) {
        Log::info("La primera fila es válida y el archivo es válido (ARC - Linea 109)");
        //Se asignan los parámetros faltantes del Objeto Archivo
        $this->archivo->fecha_ini_periodo = strtotime($firstRow[2]);
        $this->archivo->fecha_fin_periodo = strtotime($firstRow[3]);
        $entidad = DB::table('entidades_sector_salud')->where('cod_habilitacion', $firstRow[0])->first();
        $this->archivo->id_entidad = $entidad->id_entidad;
        $this->archivo->numero_registros = $firstRow[4];
        $this->archivo->save();

        $this->file_status->total_registers =  $firstRow[4];
        $this->file_status->save();

        $lineCount = 2;
        $lineCountWF = 2;
        //Se hace la validación de cada linea recorriendo el archivo con el
        //apuntador mediante el método fgetcsv. Este debe recibir un valor
        //mayor al total de registros que trae el archivo.
        while($data = fgetcsv($this->handle, 10000, "|")) {
          // Se eliminan los espacios que contengan los campos leidos de cada linea del archivo
          $this->dropWhiteSpace($data); 
          $isValidRow = true;
          Log::info("Empieza las validacionees - (ARC Linea 129)");
          //Se hace la validación de cada sección del archivo 
          $this->validateEntitySection($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,0,6));
          $this->validateUserSection($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,6,9,true));
          $this->validateUserAddressAndPhone($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,15,2,true));
          $this->validateARC($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,17,57,true));

          // Se valida la coherencia entre fechas
          if ($isValidRow){
            $this->validateDates($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, $firstRow,$data);
          }

          if(!$isValidRow){
            
            array_push($this->wrong_rows, $data);
            $this->updateStatusFile($lineCount); //se acatualiza la linea ya tratada
            $lineCount++;
            $lineCountWF++;
            continue;

          } else {
              
            //Se valida la duplicidad de la información. Esto se hace mediante una consulta que busque el registro que se acaba de analizar en la base de datos. 
            $exists = DB::table('gioss_archivo_arc_cfvl')->where('contenido_registro_validado', utf8_encode(implode('|', $data)))->first();
            //Si el registro ya existe se marca como registro duplicado y el error se inserta al arreglo de errores y al arreglo de detalles de error
            if($exists){
              
              array_push($this->detail_erros, [$lineCount, $lineCountWF, '', "Registro duplicado"]);
              array_push($this->wrong_rows, $data);
              $this->updateStatusFile($lineCount);
              $lineCountWF++;
              $lineCount++;  
              continue;
            } else {
              //Si el registro no exite aún en la base de datos este es almacenado en la tabla de soporte
                $tabla = new GiossArchivoARCCfvl();
                $tabla->fecha_periodo_inicio = $this->archivo->fecha_ini_periodo;
                $tabla->fecha_periodo_fin = $this->archivo->fecha_fin_periodo;
                $tabla->nombre_archivo = utf8_encode($this->fileName);
                $tabla->numero_registro = $lineCount;
                $tabla->contenido_registro_validado = utf8_encode(implode('|', $data));
                $tabla->fecha_hora_validacion = time() ;
                $tabla->save();

              // Se busca el usuario que aparece en el registro para confirmar que ya haya sido agregado a la base de datos.
              $exists = DB::table('user_ips')->where('num_identificacion', $data[8])->orderBy('created_at', 'desc')->first();

              $createNewUserIp = true;
              $useripsid = 0;

              //Si la consulta arroja algún resultado entra a este if
              if($exists){ 
                //Si alguno de los campos coincide con los campos del registro extraido por la consulta entra a este if 
                if($exists->num_historia_clinica ==  $data[6] || $exists->tipo_identificacion ==  $data[7] || $exists->primer_apellido ==  $data[9] || $exists->segundo_apellido ==  $data[10] || $exists->primer_nombre ==  $data[11] || $exists->segundo_nombre ==  $data[12] || $exists->fecha_nacimiento ==  $data[13] || $exists->sexo ==  $data[14] || $exists->direccion == $data[15] || $exists->telefono == $data[16])
                {
                  //No es necesario crear un nuevo usuario, se conserva el id del usuario encontrado
                  $createNewUserIp = false;
                  $useripsid = $exists->id_user;
                }
              }

              //Si el usuario no fue encontrado en la tabla de usuarios entra a este if y crea un objeto de tipo UsuarioIP
              if($createNewUserIp)
              {
                $ipsuser = new UserIp();
                $ipsuser->num_historia_clinica = utf8_encode($data[6]);
                $ipsuser->tipo_identificacion = utf8_encode($data[7]);
                $ipsuser->num_identificacion = utf8_encode($data[8]);
                $ipsuser->primer_apellido = utf8_encode($data[9]);
                $ipsuser->segundo_apellido = utf8_encode($data[10]);
                $ipsuser->primer_nombre = utf8_encode($data[11]);
                $ipsuser->segundo_nombre = utf8_encode($data[12]);
                $ipsuser->fecha_nacimiento = utf8_encode($data[13]);
                $ipsuser->sexo = utf8_encode($data[14]);
                $ipsuser->direccion = utf8_encode($data[15]);
                $ipsuser->telefono = utf8_encode($data[16]);

                $ipsuser->save();
                $useripsid = $ipsuser->id_user;
              }

              //Se guarda la información del registro
              $cadena_temp=ltrim($data[3], '0');
              $cadena_test=substr($cadena_temp, 0, strlen($cadena_temp) - 1);

              $eapb  = DB::table('eapbs')->where('num_identificacion', $cadena_test)
                              ->where('cod_eapb', $data[4])->first();
              
              if ($eapb) {

                $exists = DB::table('registro')->where('id_archivo', $this->archivo->id_archivo_seq)->where('id_user', $useripsid)->where('id_eapb', $eapb->id_entidad)->first();

                if (!$exists) {
                  Log::info("Crea el registro");
                  $register = new Registro();
                  $register->id_archivo = $this->archivo->id_archivo_seq;
                  $register->id_user = $useripsid;

                  $register->id_eapb = $eapb->id_entidad;
                  $register->save();
                  Log::info("Guarda el registro en la BD");
                }
                
              } else {
                $exists = DB::table('registro')->where('id_archivo', $this->archivo->id_archivo_seq)->where('id_user', $useripsid)->first();

                if (!$exists) {
                  Log::info("Crea el registro");
                  $register = new Registro();
                  $register->id_archivo = $this->archivo->id_archivo_seq;
                  $register->id_user = $useripsid;

                  $register->id_eapb = 1;
                  $register->save();
                  Log::info("Guarda el registro en la BD");
                }
              }

              //Se guarda la información correspondiente a la consulta.
              $consult = new RegistroCancer();
              $consult->id_registro = $register->id_registro_seq;
              $consult->validez_registro = utf8_encode($data[17]);
              $consult->cod_diagnostico = utf8_encode($data[18]);
              $consult->tipo_estudio = utf8_encode($data[20]);
              $consult->motivo_no_diagnostico = utf8_encode($data[21]);
              $consult->fecha_diagnostico = strtotime($data[22]);
              $consult->fecha_primera_consulta = strtotime($data[23]);
              $consult->histologia = utf8_encode($data[24]);
              $consult->grado_diferenciacion = utf8_encode($data[25]);
              $consult->primera_estadificacion = utf8_encode($data[26]);
              $consult->fecha_estadificacion = strtotime($data[27]);
              $consult->resultado_prueba_her2 = utf8_encode($data[28]);
              $consult->estadificacion_dukes = utf8_encode($data[29]);
              $consult->estadificacion_dukes = utf8_encode($data[29]);
              $consult->fecha_estadificacion_dukes = strtotime($data[30]);
              $consult->estadificacion_clinica = utf8_encode($data[31]);
              $consult->valor_escala_gleason = utf8_encode($data[32]);
              $consult->clasificacion_riesgo = utf8_encode($data[33]);
              $consult->fecha_clasificacion_riesgo = strtotime($data[34]);
              $consult->objetivo_tratamiento = utf8_encode($data[35]);
              $consult->objetivo_intervencion = utf8_encode($data[36]);
              $consult->antecedentes_cancer_primario = utf8_encode($data[37]);
              $consult->fecha_diagnostico_cancer_primario = strtotime($data[38]);
              $consult->cod_diagnostico_cancer_primario = utf8_encode($data[39]);
              $consult->recibio_quimioterapia = utf8_encode($data[40]);
              $consult->cantidad_fases_quimioterapia = utf8_encode($data[41]);
              $consult->recibio_prefase_citorreduccion = utf8_encode($data[42]);
              $consult->recibio_induccion = utf8_encode($data[43]);
              $consult->recibio_intensificacion = utf8_encode($data[44]);
              $consult->recibio_consolidacion = utf8_encode($data[45]);
              $consult->recibio_reinduccion = utf8_encode($data[46]);
              $consult->recibio_mantenimiento = utf8_encode($data[47]);
              $consult->recibio_mantenimiento_largo_final = utf8_encode($data[48]);
              $consult->recibio_quimioterapia_diferente = utf8_encode($data[49]);
              $consult->sometido_a_cirugia = utf8_encode($data[50]);
              $consult->ubicacion_temp_primera_cirugia = utf8_encode($data[51]);
              $consult->motivo_ultima_cirugia = utf8_encode($data[52]);
              $consult->ubicacion_temp_ultima_cirugia = utf8_encode($data[53]);
              $consult->estado_final_ult_cirugia = utf8_encode($data[54]);
              $consult->caracteristicas_actuales_prim_radio = utf8_encode($data[55]);
              $consult->motivo_finalizacion_prim_radio = utf8_encode($data[56]);
              $consult->ubicacion_temp_ult_radio = utf8_encode($data[57]);
              $consult->tipo_radioterapia = utf8_encode($data[58]);
              $consult->caracteristicas_actuales_ult_radio = utf8_encode($data[59]);
              $consult->motivo_finalizacion_ult_radio = utf8_encode($data[60]);
              $consult->recibio_transplante = utf8_encode($data[61]);
              $consult->tipo_transplante = utf8_encode($data[62]);
              $consult->ubicacion_temp_transplante = utf8_encode($data[63]);
              $consult->fecha_transplante = strtotime($data[64]);
              $consult->usuario_recibio_valoracion = utf8_encode($data[65]);
              $consult->recibio_consulta_espe_paliativo = utf8_encode($data[66]);
              $consult->recibio_consulta_prof_salud = utf8_encode($data[67]);
              $consult->recibio_consulta_otro_espe = utf8_encode($data[68]);
              $consult->recibio_consulta_medico_general = utf8_encode($data[69]);
              $consult->recibio_consulta_trabajador_social = utf8_encode($data[70]);
              $consult->recibio_consulta_otro_prof_salud = utf8_encode($data[71]);
              $consult->fecha_primera_consulta_paliativo = strtotime($data[72]);
              $consult->tipo_tratamiento = $data[73];

              $consult->save();

              //Se agrega el registro a los registros exitosos 
              array_push($this->success_rows, $data);
              $this->updateStatusFile($lineCount);
              $lineCount++;

            }  
          }
        }

        if($lineCount==3)
        {
          $this->updateStatusFile($lineCount - 1);
        }
        else
        {
          $this->updateStatusFile($lineCount);
        }

        //Cierra el archivo de texto cargado por el usuario
        fclose($this->handle);
        $this->generateFiles();

      }else{
        $this->generateFiles();

        if(!$isValidFirstRow){
          $this->archivo->version = '999';
          $this->archivo->save();
        }
      }
    
    } catch (\Exception $e) {
      Log::info("Error: ".print_r($e->getMessage(), true));
    }
    Log::error("Termina manageContent (ARC - Linea 278)");
  }

  private function validateARC(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF, $consultSection) {
    
    // Se valida la validez del registro (Campo 18)
    Log::info("Inicia validateARC (ARC - Linea 384)");

    if (isset($consultSection[17])) {
      if (!preg_match("/^(([1-4])|(98)|(99))$/", $consultSection[17])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo no corresponde a ningún código de validez de registro posible."]);
      } 
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo no puede ser nulo."]);
    }

    // Se valida el código de diagnóstico principal (Campo 19)
    if (isset($consultSection[18])) {
      if (strlen(trim($consultSection[18])) != 4 || !ctype_alnum(trim($consultSection[18]))) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo debe tener una longitud igual a 4 caracteres y solo debe estar compuesto por letras y números."]);
      } else {
        $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico', $consultSection[18])->first();
          if (!$exists) {
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El valor de este campo no corresponde a un código de diagnóstico válido"]);
          }
        }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo no puede ser nulo."]);
    }
    
    // Se valida el descripción del diagnóstico principal (Campo 20)
    if (isset($consultSection[19])) {
      if (strlen(trim($consultSection[19])) > 50 || trim($consultSection[19]) == "") {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo no debe ser vacío y debe tener una longitud menor o igual a cincuenta caracteres"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo no puede ser nulo."]);
    }
    
    // Se valida el tipo de estudio (Campo 21)
    if (isset($consultSection[20])) {
      if (!preg_match("/^(([1-8])|(99))$/", $consultSection[20])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 21, "El campo no corresponde a ningún código de tipo de tratamiento válido."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 21, "El campo no puede ser nulo."]);
    }
    
    // Se valida el motivo por el cual el usuario no tuvo consulta (Campo 22)
    if (isset($consultSection[21])) {
      if (!preg_match("/^(([1-5])|(98)|(99))$/", $consultSection[21])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, "El campo no correspone a ningún código de motivo válido"]);
      }
      
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El campo no puede ser nulo."]);
    }
    
    // Se valida la fecha de informe hispatológico (Campo 23)
    if (isset($consultSection[22])) {
      if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[22])) {
        $date = explode('-', $consultSection[22]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 23, "El campo debe corresponder a un fecha válida."]);
        }
      } else {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 23, "El campo debe tener el formato AAAA-MM-DD"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 23, "El campo no puede ser nulo."]);
    }
    
    // Se valida la fecha de la primera consulta (Campo 24)
    if (isset($consultSection[23])) {
      if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[23])) {
        $date = explode('-', $consultSection[23]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 24, "El campo debe corresponder a un fecha válida."]);
        }
      } else {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 24, "El campo debe tener el formato AAAA-MM-DD"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 24, "El campo no puede ser nulo."]);
    }

    // Se valida la Histología el Tumor (Campo 25)
    if (isset($consultSection[24])) {
      if(!preg_match("/^((1)|(2))?([1-9])?((10)|(20))?$/", $consultSection[24]) || strlen(trim($consultSection[24])) > 2 ) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 25, "El campo debe corresponder a código de histología válido."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 25, "El campo no puede ser nulo."]);
    }
    
    // Se valida el grado de diferenciación (Campo 26)
    if (isset($consultSection[25])) {
      if (!preg_match("/^(([1-4])|(98)|(99))$/", $consultSection[25])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 26, "El campo debe corresponder a un grado de diferenciación válido."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 26, "El campo no puede ser nulo."]);
    }
    
    // Se valida la primera estadificacion (Campo 27)
    if (isset($consultSection[26])) {
      if(!preg_match("/^((1)|(2))?([0-9])?((10)|(20))?$/", $consultSection[26]) || strlen(trim($consultSection[26])) > 2) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 27, "El campo debe corresponder a código de estadificación válido."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 27, "El campo no puede ser nulo."]);
    }

    // Se valida la fecha de la primera estadificacion (Campo 28)
    if (isset($consultSection[27])) {
      if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[27])) {
        $date = explode('-', $consultSection[27]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 28, "El campo debe corresponder a un fecha válida."]);
        }
      } else {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 28, "El campo debe tener el formato AAAA-MM-DD"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 28, "El campo no puede ser nulo."]);
    }
    
    // Se valida el resultado de la prueba HER2 (Campo 29)
    if (isset($consultSection[28])) {
      if (!preg_match("/^(([1-3])|(97))$/", $consultSection[28])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 29, "El campo debe corresponder a un valor válido de la prueba HER2."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 29, "El campo no puede ser nulo"]);
    }
    
    // Se valida la estadificacion de DUKES (Campo 30)
    if (isset($consultSection[29])) {
      if (!preg_match("/^(([1-4])|(98)|(99))$/", $consultSection[29])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 30, "El campo debe corresponder a un valor de estadificacioón DUKES válido."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 30, "El campo no puede ser nulo."]);
    }
    
    // Se valida laa fecha de la estadificación de DUKES (campo 31)
    if (isset($consultSection[30])) {
      if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[30])) {
        $date = explode('-', $consultSection[30]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 31, "El campo debe corresponder a un fecha válida."]);
        }
      } else {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 31, "El campo debe tener el formato AAAA-MM-DD"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 31, "El campo no puede ser nulo."]);
    }

    // Se valia la estadificación clínica (Campo 32)
    if (isset($consultSection[31])) {
      if (!preg_match("/^(([1-4])|(98)|(99))$/", $consultSection[31])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 32, "El campo debe corresponder a un valor de estadificación clínica válido."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 32, "El campo no puede ser nulo."]);
    }

    // Se valida el valor de la clasificación GLEASON (Campo 33)
    if (isset($consultSection[32])) {
      if(!preg_match("/^([2-9])?(10)?$/", $consultSection[26]) || strlen(trim($consultSection[26])) > 2) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 33, "El valor no corresponde a un valor de la clasificacion GLEASON válido"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 33, "El campo no puede ser nulo"]);
    }

    // Se valida la clasificación de riesgo (Campo 34)
    if (isset($consultSection[33])) {
      if (!preg_match("/^(([1-9])?|(10)|(11)|(12)|(13)|(97)|(98)|(99))$/", $consultSection[26]) || strlen(trim($consultSection[26])) > 2) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 34, "El campo no corresponde a una clasificación de riesgo válida"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 34, "El campo no puede ser nulo."]);
    }
    
    // Se valida la fecha de clasificación del riesgo (Campo 35)
    if (isset($consultSection[34])) {
      if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[34])) {
        $date = explode('-', $consultSection[34]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 35, "El campo debe corresponder a un fecha válida."]);
        }
      } else {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 35, "El campo debe tener el formato AAAA-MM-DD"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 35, "El campo no puede ser nulo."]);
    }
    
    // Se valida el objetivo del tratamiento (Campo 36)
    if (isset($consultSection[35])) {
      if (!preg_match("/^(([1-2])|(99))$/", $consultSection[35])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 36, "El campo debe contener un valor de objetivo de tratamiento válido"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 36, "El campo no puede ser nulo."]);
    }

    // Se valida el objetivo de intervención (Campo 37)
    if (isset($consultSection[36])) {
      if (!preg_match("/^(([1-6])|(99))$/", $consultSection[36])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 37, "El campo debe contener un valor de objetivo de intervención válido"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 37, "El campo no puede ser nulo."]);
    }
    
    // Se validan los antecedentes de cancer primario (Campo 38)
    if (isset($consultSection[37])) {
      if (!preg_match("/^(([1-2])|(99))$/", $consultSection[37])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 38, "El campo debe contener un valor de antecedentes de cancer primario válido"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 38, "El campo no puede ser nulo."]);
    }

    // Se valida la fecha de diagnóstico de cancer primario (Campo 39)
    if (isset($consultSection[38])) {
      if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[38])) {
        $date = explode('-', $consultSection[38]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 39, "El campo debe corresponder a un fecha válida."]);
        }
      } else {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 39, "El campo debe tener el formato AAAA-MM-DD"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 39, "El campo no puede ser nulo."]);
    }

    // Se valida tipo de cancer antecedente (Campo 40)
    if (isset($consultSection[39])) {
      if (strlen(trim($consultSection[39])) != 4 || !ctype_alnum(trim($consultSection[39]))) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 40, "El campo debe tener una longitud igual a 4 caracteres y solo debe estar compuesto por letras y números."]);
      } else {
        $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico', $consultSection[39])->first();
          if (!$exists) {
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 40, "El valor de este campo no corresponde a un código de diagnóstico válido"]);
          }
        }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 40, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibió o no quimioterapia (Campo 41)
    if (isset($consultSection[40])) {
      if (!preg_match("/^(([1-2])|(98))$/", $consultSection[40])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 41, "El campo debe contener un valor válido: 1, 2 o 98."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 41, "El campo no puede ser nulo."]);
    }

    // Se validan las fases de quimioterapia que recibió el usuario (Campo 42)
    if (isset($consultSection[41])) {
      if (strlen(trim($consultSection[41])) > 2) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 42, "El campo debe tener una longitud menor o igual a 2 caracteres."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 42, "El campo no puede ser nulo."]);
    }
    
    // Se valida si el usuario recibio prefase o citoreduccion (Campo 43)
    if (isset($consultSection[42])) {
      if (!preg_match("/^(([1-2])|(97)|(99))$/", $consultSection[42])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 43, "El campo debe contener un valor válido: 1, 2, 97 o 99."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 43, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibio inducción (Campo 44)
    if (isset($consultSection[43])) {
      if (!preg_match("/^(([1-2])|(97)|(99))$/", $consultSection[43])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 44, "El campo debe contener un valor válido: 1, 2, 97 o 99."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 44, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibio intensificación (Campo 45)
    if (isset($consultSection[44])) {
      if (!preg_match("/^(([1-2])|(97)|(99))$/", $consultSection[44])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 45, "El campo debe contener un valor válido: 1, 2, 97 o 99."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 45, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibio consolidación (Campo 46)
    if (isset($consultSection[45])) {
      if (!preg_match("/^(([1-2])|(97)|(99))$/", $consultSection[45])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 46, "El campo debe contener un valor válido: 1, 2, 97 o 99."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 46, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibio reinducción (Campo 47)
    if (isset($consultSection[46])) {
      if (!preg_match("/^(([1-2])|(97)|(99))$/", $consultSection[46])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 47, "El campo debe contener un valor válido: 1, 2, 97 o 99."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 47, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibio mantenimiento (Campo 48)
    if (isset($consultSection[47])) {
      if (!preg_match("/^(([1-2])|(97)|(99))$/", $consultSection[47])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 48, "El campo debe contener un valor válido: 1, 2, 97 o 99."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 48, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibio mantenimiento largo (Campo 49)
    if (isset($consultSection[48])) {
      if (!preg_match("/^(([1-2])|(97)|(99))$/", $consultSection[48])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 49, "El campo debe contener un valor válido: 1, 2, 97 o 99."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 49, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibio quimioterapia diferente (Campo 50)
    if (isset($consultSection[49])) {
      if (!preg_match("/^(([1-2])|(97)|(99))$/", $consultSection[49])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 50, "El campo debe contener un valor válido: 1, 2, 97 o 99."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 50, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario fe sometido a cirugía (Campo 51)
    if (isset($consultSection[50])) {
      if (!preg_match("/^([1-3])$/", $consultSection[50])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 51, "El campo debe contener un valor válido: 1, 2, 3."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 51, "El campo no puede ser nulo."]);
    }

    // Se valida la ubicación teemporaal de la primera cirugía (Campo 52)
    if (isset($consultSection[51])) {
      if (!preg_match("/^(([1-4])|(98))$/", $consultSection[51])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 52, "El valor dado no corresponde a una ubicación temporal válida."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 52, "El campo no puede ser nulo."]);
    }

    // Se valida la motivación de la cirugía (Campo 53)
    if (isset($consultSection[52])) {
      if (!preg_match("/^(([1-7])|(98))$/", $consultSection[52])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 53, "El valor dado no corresponde a un valor válido para el motivo de la intervención quirúrgica."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 53, "El campo no puede ser nulo."]);
    }

    // Se valida la ubicación teemporaal de la última cirugía (Campo 54)
    if (isset($consultSection[53])) {
      if (!preg_match("/^(([1-4])|(98))$/", $consultSection[53])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 54, "El valor dado no corresponde a una ubicación temporal válida."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 54, "El campo no puede ser nulo."]);
    }

    // Se valida el estado vital del usuario (Campo 55)
    if (isset($consultSection[54])) {
      if (!preg_match("/^(([1-2])|(98))$/", $consultSection[54])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 55, "El campo debe contener un valor válido: 1, 2 o 98."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 55, "El campo no puede ser nulo."]);
    }

    // Se validan las características actuales de la primera radioterapia (Campo 56)
    if (isset($consultSection[55])) {
      if (!preg_match("/^(([1-3])|(98))$/", $consultSection[55])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 56, "El campo debe contener un valor válido: 1, 2, 3 o 98."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 56, "El campo no puede ser nulo."]);
    }

    // Se valida la motivación de finalización del primer esquema de radioterapia(Campo 57)
    if (isset($consultSection[56])) {
      if (!preg_match("/^(([1-7])|(98))$/", $consultSection[56])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 57, "El valor dado no corresponde a un valor válido para el motivo de finalización del primer esquema de radioterapia."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 57, "El campo no puede ser nulo."]);
    }

    // Se valida la ubicación tmporaal del último esquema de radioterapia (Campo 58)
    if (isset($consultSection[57])) {
      if (!preg_match("/^(([1-9])|(98))$/", $consultSection[57])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 58, "El valor dado no corresponde a una ubicación temporal válida."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 58, "El campo no puede ser nulo."]);
    }

    // Se valida el tipo de radioterapia (Campo 59)
    if (isset($consultSection[58])) {
      if (!preg_match("/^(([1-7])|(98))$/", $consultSection[58])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 59, "El valor dado no corresponde a un tipo de radioterapia válido."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 59, "El campo no puede ser nulo."]);
    }

    // Se validan las características del último esquema de radioterapia (Campo 60)
    if (isset($consultSection[59])) {
      if (!preg_match("/^(([1-3])|(98))$/", $consultSection[59])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 60, "El campo debe contener un valor válido: 1, 2, 3 o 98."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 60, "El campo no puede ser nulo."]);
    }

    // Se valida el motivo de finalización del último esquema de radioterapia (Campo 61)
    if (isset($consultSection[60])) {
      if (!preg_match("/^(([1-7])|(98))$/", $consultSection[60])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 61, "El valor dado no corresponde a un motivo de finalización válido."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 61, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibió transplantes (Campo 62)
    if (isset($consultSection[61])) {
      if (!preg_match("/^(([1-2])|(98))$/", $consultSection[61])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 62, "El campo debe contener un valor válido: 1, 2 o 98."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 62, "El campo no puede ser nulo."]);
    }

    // Se valida el tipo de transplante (Campo 63)
    if (isset($consultSection[62])) {
      if (!preg_match("/^(([1-9])|(98))$/", $consultSection[62])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 63, "El valor dado no corresponde a un motivo de finalización válido."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 63, "El campo no puede ser nulo."]);
    }

    // Se valida la ubicación temporal del transplante (Campo 64)
    if (isset($consultSection[63])) {
      if (!preg_match("/^(([1-3])|(98))$/", $consultSection[63])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 64, "El valor dado no corresponde a una ubicación temporal del transplante válida."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 64, "El campo no puede ser nulo."]);
    }

    // Se valida la fecha del transplantre (Campo 65)
    if (isset($consultSection[64])) {
      if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[64])) {
        $date = explode('-', $consultSection[64]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 65, "El campo debe corresponder a un fecha válida."]);
        }
      } else {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 65, "El campo debe tener el formato AAAA-MM-DD"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 65, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario fue valorado (Campo 66)
    if (isset($consultSection[65])) {
      if (!preg_match("/^([1-3])$/", $consultSection[65])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 66, "El campo debe tener un valor válido, 1, 2 o 3."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 66, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibió consulta con médico especialista en cuidado paliativo (Campo 67)
    if (isset($consultSection[66])) {
      if (!preg_match("/^([1-2])$/", $consultSection[66])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 67, "El campo debe tener un valor válido, 1 o 2."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 67, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibió consulta con profesional de la salud (Campo 68)
    if (isset($consultSection[67])) {
      if (!preg_match("/^([1-2])$/", $consultSection[67])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 68, "El campo debe tener un valor válido, 1 o 2."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 68, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibió consulta con otro tipo de especialista (Campo 69)
    if (isset($consultSection[68])) {
      if (!preg_match("/^([1-2])$/", $consultSection[68])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 69, "El campo debe tener un valor válido, 1 o 2."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 69, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibió consulta con médico general (Campo 70)
    if (isset($consultSection[69])) {
      if (!preg_match("/^([1-2])$/", $consultSection[69])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 70, "El campo debe tener un valor válido, 1 o 2."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 70, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibió consulta con trabaador social (Campo 71)
    if (isset($consultSection[70])) {
      if (!preg_match("/^([1-2])$/", $consultSection[70])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 71, "El campo debe tener un valor válido, 1 o 2."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 71, "El campo no puede ser nulo."]);
    }

    // Se valida si el usuario recibió consulta con otro tipo de profesional en salud (Campo 72)
    if (isset($consultSection[71])) {
      if (!preg_match("/^([1-2])$/", $consultSection[71])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 72, "El campo debe tener un valor válido, 1 o 2."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 72, "El campo no puede ser nulo."]);
    }

    // Se valida la fecha  de consulta con médico o especialista (Campo 73)
    if (isset($consultSection[72])) {
      if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[72])) {
        $date = explode('-', $consultSection[72]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 73, "El campo debe corresponder a un fecha válida."]);
        }
      } else {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 73, "El campo debe tener el formato AAAA-MM-DD"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 73, "El campo no puede ser nulo."]);
    }

    // Se valida el tipo de tratamiento (Campo 74)
    if (isset($consultSection[73])) {
      if (!preg_match("/^([1-3])$/", $consultSection[73])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 74, "El campo debe tener un valor válido, 1, 2 o 3."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 74, "El campo no puede ser nulo."]);
    }

    Log::info("Termina validateARC (ARC - Linea 500)");

  }

  protected function validateDates(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF, $firstRow , $data)
  {
    // Se valida que la fecha de nacimiento sea menor que el final del periodo reportado
    Log::info("Inicia validateDates (ARC - Linea 506)");
    if (strtotime($firstRow[3]) < strtotime($data[13])) {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha final del periodo reportado (línea 1, campo 4)"]);
    }

    Log::info("Termina validateDates (ARC - Linea 577)");
  }
}