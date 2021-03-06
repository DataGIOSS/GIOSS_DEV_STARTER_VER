<?php

namespace App\Classes;

use App\Classes\FileValidator;
use App\Traits\ToolsForFilesController;
use App\Models\Ambito;
use App\Models\ConsultaCup;
use App\Models\GiossConsultaCup;
use App\Models\HomologosCupsCodigo;
use App\Models\DiagnosticoCiex;
use App\Models\TipoDiagnostico;
use App\Models\FinalidadConsultum;
use App\Models\FileStatus;
use App\Models\Archivo;
use App\Models\UserIp;
use App\Models\Registro;
use App\Models\Eapb;
use App\Models\Consultum;
use App\Models\EntidadesSectorSalud;
use App\Models\GiossArchivoAacCfvl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AAC extends FileValidator {

  // Clase constructora para el Archivo Validador de AAC

  var $datos_creacion_global;

  function __construct($pathfolder, $fileName, $consecutive, $datos_creacion) {
    $filePath = $pathfolder.$fileName;
    $conteoLineas = $this->countLine($filePath);
    $this->datos_creacion_global = $datos_creacion;
    if(!($this->handle = fopen($filePath, 'r'))) throw new Exception("Error al abrir el archivo AAC");
    
    $this->folder = $pathfolder;

    $fileNameToken = explode('.',$fileName);
    $this->fileName =  substr($fileNameToken[0],0,58);
    $this->version = substr($fileNameToken[0],58);
    $this->consecutive = $consecutive;
    $this->detail_erros = array(['No. línea archivo original', 'No. linea en archivo de errores','Campo', 'Descripción', 'Valor Registrado']);
    $this->wrong_rows =  array();
    $this->success_rows =  array();
  }
  
  // Función para el manejo  las validaciones de los registros

  public function manageContent() {

		try {
      // se valida la existencia del archivo
      $isValidFile = true;
      $fileid = 0;

      $exists = DB::table('archivo')->where('nombre', $this->fileName)
                ->where('version', $this->version)
                ->first();

      if($exists){

        $isValidFile = false;
        array_push($this->detail_erros, [0, 0, '', "El archivo ya fue gestionado. Por favor actualice la version", $this->fileName]);
        $fileid = $exists->id_archivo_seq;
      } else {
          //se define en primera instancia el objeto archivo
          $this->archivo = new Archivo();
          $this->archivo->modulo_informacion = 'SGD';
          $this->archivo->nombre = $this->fileName;
          $this->archivo->version = $this->version;
          $this->archivo->id_tema_informacion = 'AAC';
          $this->archivo->save();

          $fileid = $this->archivo->id_archivo_seq;

      }
      // se inicializa el objeto file_status 
      $this->file_status = new FileStatus();
      $this->file_status->consecutive = $this->consecutive;
      $this->file_status->archivoid = $fileid;
      $this->file_status->current_status = 'WORKING';
      $this->file_status->usuario_creacion = $this->datos_creacion_global[0];
      $this->file_status->fecha_creacion = $this->datos_creacion_global[1];
      $this->file_status->hora_creacion = $this->datos_creacion_global[2];
      $this->file_status->save();
      
      $isValidFirstRow = true ;
      
			$firstRow = fgetcsv($this->handle, 0, "|");
      
			$this->validateFirstRow($isValidFirstRow, $this->detail_erros, $firstRow);

      if ($isValidFirstRow && $isValidFile) {
        
        //se adicionan terminan de definir los prametros el archivo
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
        //se valida cada línea 

        while($data = fgetcsv($this->handle, 10000, "|"))
        {
          
          $this->dropWhiteSpace($data); // se borran los espacios de cada campo
          $isValidRow = true;
          $temp_array = Array();

          Log::info("Empieza las validaciones (AAC - Linea 116)");

          Log::info("Empieza las validaciones de entidad (AAC - Linea 118)");

          $this->validateEntitySection($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,0,6));

          Log::info("Termina las validaciones de entidad y empieza las validaciones del usuario (AAC - Linea 122)");

          $this->validateUserSection($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,6,9,true));

          Log::info("Termina las validaciones de usuaro y empieza las validaciones del achivo (AAC - Linea 126)");

          $this->validateAAC($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,15,14,true), $temp_array);

          Log::info("Termina las validaciones del archivo (AAC - Linea 130)");

          foreach ($temp_array as $key => $value) {
            $data[$key] = $value;
          }

          if ($isValidRow) // se validan coherencia entre fechas
          { 
            Log::info("Empieza las validaciones de fecha (AAC - Linea 124)");
            $this->validateDates($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, $firstRow,$data);
          }

          if(!$isValidRow){
            Log::info("La fila revisada no es válida (AAC - Linea 129)");
            array_push($this->wrong_rows, $data);
            $this->updateStatusFile($lineCount); //se acatualiza la linea ya tratada
            $lineCount++;
            $lineCountWF++;
            continue;
          }else{
            Log::info("Se valida la duplicidad del registro (fila) (AAC - Linea 136)");
            //se valida duplicidad en la informacion
            $exists = DB::table('gioss_archivo_aac_cfvl')->where('contenido_registro_validado', utf8_encode(implode('|', $data)))->first();

            if($exists){
              Log::info("La fila está duplicada (AAC - Linea 160)");
              array_push($this->detail_erros, [$lineCount, $lineCountWF, '', "Registro duplicado", 0]);
              array_push($this->wrong_rows, $data);
              $this->updateStatusFile($lineCount);
              $lineCountWF++;
              $lineCount++;
              continue;
            }else
            {
              Log::info("La fila no está duplicada por lo tanto se crea el archivo GiossArchivoAacCfvl (AAC - Linea 169)");
            //se guarda todo el registro en en la tabla soporte
              $tabla = new GiossArchivoAacCfvl();
              $tabla->fecha_periodo_inicio = $this->archivo->fecha_ini_periodo;
              $tabla->fecha_periodo_fin = $this->archivo->fecha_fin_periodo;
              $tabla->nombre_archivo = utf8_encode($this->fileName);
              $tabla->numero_registro = $lineCount;
              $tabla->contenido_registro_validado = utf8_encode(implode('|', $data));
              $tabla->fecha_hora_validacion = time() ;
              $tabla->save();

              //
              // alamacena en la dimension
              $exists = DB::table('user_ips')->where('num_identificacion', $data[8])->orderBy('created_at', 'desc')->first();

              $createNewUserIp = true;
              $useripsid = 0;

              if($exists){
                Log::info("El Num ID existe  (AAC - Linea 188)");
                if($exists->num_historia_clinica ==  $data[6] || $exists->tipo_identificacion ==  $data[7] || $exists->primer_apellido ==  $data[9] || $exists->segundo_apellido ==  $data[10] || $exists->primer_nombre ==  $data[11] || $exists->segundo_nombre ==  $data[12] || $exists->fecha_nacimiento ==  $data[13] || $exists->sexo ==  $data[14])
                {
                  Log::info("El usuario ya estaba con otros datos. No se crea un nuevo usuario (AAC - Linea 191)");
                  $createNewUserIp = false;
                  $useripsid = $exists->id_user;
                }
              }

              if($createNewUserIp)
              {
                Log::info("No se encuentra el usuario por lo cual se crea un usuario nuevo (AAC - Linea 199)");
                $ipsuser = new UserIp();
                $ipsuser->num_historia_clinica = $data[6];
                $ipsuser->tipo_identificacion = utf8_encode($data[7]);
                $ipsuser->num_identificacion = $data[8];
                $ipsuser->primer_apellido = utf8_encode($data[9]);
                $ipsuser->segundo_apellido = utf8_encode($data[10]);
                $ipsuser->primer_nombre = utf8_encode($data[11]);
                $ipsuser->segundo_nombre = utf8_encode($data[12]);
                $ipsuser->fecha_nacimiento = $data[13];
                $ipsuser->sexo = $data[14];

                $ipsuser->save();
                $useripsid = $ipsuser->id_user;
              }

              Log::info("Se busca la entidad (AAC - Linea 215)");
              $cadena_temp=ltrim($data[3], '0');
              $cadena_test=substr($cadena_temp, 0,  strlen($cadena_temp) - 1);

              $eapb  = DB::table('eapbs')->where('num_identificacion', $cadena_test)
                              ->where('cod_eapb', $data[4])->first();
            
              Log::info("Se verifica la existencia e la entidad (AAC - Linea 222)");
              if ($eapb) {
                Log::info("La entidad existe, se busca el registro (AAC - Linea 224)");
                $exists = DB::table('registro')->where('id_archivo', $this->archivo->id_archivo_seq)->where('id_user', $useripsid)->where('id_eapb', $eapb->id_entidad)->first();

                Log::info("La entidad existe pero el registro no, se crea el registro (AAC - Linea 227)");
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
                Log::info("La entidad no existe, se busca el registro (AAC - Linea 240)");
                $exists = DB::table('registro')->where('id_archivo', $this->archivo->id_archivo_seq)->where('id_user', $useripsid)->first();
                
                Log::info("La entidad existe y tampoco el registro (AAC - Linea 243)");

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

              //se almacena la información correpondiente a la consulta
              $consult = new Consultum();
              $consult->id_registro = $register->id_registro_seq;
              $consult->fecha_consulta = $data[15];
              $consult->ambito_consulta = $data[16];
              $consult->tipo_codificacion = $data[18];
              $consult->cod_consulta = $data[17];
              $consult->cod_consulta_esp = $data[19];
              $consult->cod_diagnostico_principal = $data[21];
              $consult->cod_diagnostico_rel1 = $data[23];
              $consult->cod_diagnostico_rel2 = $data[25];
              $consult->tipo_diagnostico_principal = $data[27];
              $consult->finalidad_consulta = $data[28];

              $consult->save();

              array_push($this->success_rows, $data);
              $this->updateStatusFile($lineCount);
              $lineCount++;

            }//fin else no esta duplicado
          }//fin else, cuando es valido
        }//fin while
        if($lineCount==3)
        {
          $this->updateStatusFile($lineCount - 1);
        }
        else
        {
          $this->updateStatusFile($lineCount);
        }

        fclose($this->handle);
        $this->generateFiles();

      } else {
        $this->generateFiles();

        if(!$isValidFirstRow){
          $this->archivo->version = '999';
          $this->archivo->save();
        }
      }
    
		} catch (\Exception $e) {
		  Log::error(print_r($e->getMessage(), true));
		}

  }

  function validateAAC(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF, $consultSection, &$temp_array) {
    Log::info("Empieza el llamado a las validaciones del archivo (AAC - Linea 320) -> Campo 16");
    if(isset($consultSection[15])) {
        if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[15])){
          $date = explode('-', $consultSection[15]);
          if(!checkdate($date[1], $date[2], $date[0])){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 16, "El campo debe corresponder a un fecha válida.", "=\"".$consultSection[15]."\""]);
          } 
        }
        else{
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 16, "El campo debe tener el formato AAAA-MM-DD", "=\"".$consultSection[15]."\""]);
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 16, "El campo no debe ser nulo", "=\"".$consultSection[15]."\""]);
    }
    
    Log::info("----------------------- Campo 17 ---------------------------------");
    //validacion campo 17
    if(isset($consultSection[16])) {
        if(strlen(trim($consultSection[16])) != 1){
          $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo de tener una longitud igual a 1", "=\"".$consultSection[16]."\""]);
        }else{
          $exists = DB::table('ambito')->where('cod_ambito',$consultSection[16])->first();
          if(!$exists){
            array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El valor del campo no correponde a un Ambito valido", "=\"".$consultSection[16]."\""]);
          }
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo no debe ser nulo", "=\"".$consultSection[16]."\""]);
    }
    
    Log::info("----------------------- Campo 18, 19, 20 ---------------------------------");
    //validacion campo 18, 19 y 20
    if(isset($consultSection[18])) {
        if(!is_numeric($consultSection[18]) || strlen(trim($consultSection[18])) != 1){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo debe ser un número de un dígito", "=\"".$consultSection[18]."\""]);
        }else{
          switch ($consultSection[18]) {
            
            case '1':


            if(isset($consultSection[17])) {
              if (ctype_alpha(trim($consultSection[17]))) {
                if(strlen(trim($consultSection[17])) <= 8){
                  $exists = DB::table('gioss_consulta_cups')->where('cod_consulta', $consultSection[17])->first();
                  if (!$exists) {
                    $exists_Homologo = DB::table('homologos_cups_codigos')->where('cod_homologo', $consultSection[17])->first();
                    if (!$exists_Homologo) {
                      $isValidRow = false;
                      array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El valor del campo no corresponde a un codigo de consulta CUP ni HOMÓLOGO válido.", "=\"".$consultSection[17]."\""]);
                    } else {
                      $consultSection[17] = $exists_Homologo->cod_cups;
                    }
                  }
                } else {
                  array_push($detail_erros, [$lineCount, $lineCountWF, 18, "ERROR INFORMATIVO: Ya que el Tipo de Codificación es igual a 1 el campo debería tener una longitud menor o igual a 6 caracteres", "=\"".$consultSection[17]."\""]);
                }
              } else {
                $isValidRow = false;
                array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo de código de consulta no puede estar compuesto por una cadena totalmente alfabética.", "=\"".$consultSection[17]."\""]);
              }
            } else {
              $isValidRow = false;
              array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo no debe ser nulo", "=\"".$consultSection[17]."\""]);
            }


            break;

            case '4':

              if(isset($consultSection[17])) {
              if (ctype_alpha(trim($consultSection[17]))) {
                if(strlen(trim($consultSection[17])) <= 10){
                  $exists_Homologo = DB::table('homologos_cups_codigos')->where('cod_homologo', $consultSection[17])->first();
                  if (!$exists_Homologo) {
                    $exists = DB::table('gioss_consulta_cups')->where('cod_consulta', $consultSection[17])->first();  
                    if (!$exists) {
                      $isValidRow = false;
                      array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El valor del campo no corresponde a un codigo de consulta CUP ni HOMÓLOGO válido.", "=\"".$consultSection[17]."\""]);
                    }
                  } else {
                      $consultSection[17] = $exists_Homologo->cod_cups;
                  }
                } else {
                  array_push($detail_erros, [$lineCount, $lineCountWF, 18, "ERROR INFORMATIVO: Ya que el Tipo de Codificación es igual a 1 el campo debería tener una longitud menor o igual a 6 caracteres", "=\"".$consultSection[17]."\""]);
                }
              } else {
                $isValidRow = false;
                array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo de código de consulta no puede estar compuesto por una cadena totalmente alfabética.", "=\"".$consultSection[17]."\""]);
              }
            } else {
              $isValidRow = false;
              array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo no debe ser nulo", "=\"".$consultSection[17]."\""]);
            }

            break;

            default:
                $isValidRow = false;
                array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo debe ser 1 o 4.", "=\"".$consultSection[18]."\""]);
          }

        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo no debe ser nulo", "=\"".$consultSection[18]."\""]);
    }

    Log::info("----------------------- Campo 20 ---------------------------------");
    //validacion campo 20
    if(isset($consultSection[19])) {
        if(!preg_match("/^\d{2}$/", $consultSection[19])){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo debe tener un valor numérico de 2 dígitos", "=\"".$consultSection[19]."\""]);
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo no debe ser nulo", "=\"".$consultSection[19]."\""]);
    }

    Log::info("----------------------- Campo 21 ---------------------------------");
    //validacion campo 21
    if(isset($consultSection[20])) {
      if($consultSection[19] != 99 && $consultSection[19] != ''){
        if(strlen($consultSection[20]) > 50 || trim($consultSection[20]) == ''){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 21, "El campo no debe ser vacío y debe tener una longitud menor o igual a 50", "=\"".$consultSection[20]."\""]);
        }
      }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 21, "El campo no debe ser nulo", "=\"".$consultSection[20]."\""]);
    }

    Log::info("----------------------- Campo 22 ---------------------------------");
    //validacion campo 22
    if(isset($consultSection[21])) {
        if(strlen($consultSection[21]) > 4){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El campo no debe ser vacío y debe tener una longitud menor o igual a 4 caracteres.", "=\"".$consultSection[21]."\""]);
        }else{
          $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico',$consultSection[21])->first();
          if(!$exists){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El valor no corresponde a un valor código de diagnóstico CIEX válido", "=\"".$consultSection[21]."\""]);
          }
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El campo no debe ser nulo", "=\"".$consultSection[21]."\""]);
    }

    Log::info("----------------------- Campo 23 ---------------------------------");
    //validacion campo 23
    if(isset($consultSection[22])) {

        if(strlen($consultSection[22]) > 50 || trim($consultSection[22]) == '') {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 23, "Este campo no debe ser vacío ya que el campo 22 no es vacío y debe tener una longitud no mayor a 50 caracteres.", "=\"".$consultSection[22]."\""]);
        }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 23, "El campo no debe ser nulo", "=\"".$consultSection[22]."\""]);
    }

    Log::info("----------------------- Campo 24 ---------------------------------");
    //validacion campo 24
    if(isset($consultSection[23])) {
      if(strlen(trim($consultSection[23])) != '')
      {
        if(strlen($consultSection[23]) > 4){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 24, "El campo debe tener un longitud menor o igual a 4 caracteres.", "=\"".$consultSection[23]."\""]);
        }else{
          $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico', $consultSection[23])->first();
          if(!$exists){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 24, "El valor no corresponde a un valor código de diagnóstico CIEX válido", "=\"".$consultSection[23]."\""]);
          }
        }
      }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 24, "El campo no debe ser nulo", "=\"".$consultSection[23]."\""]);
    }

    Log::info("----------------------- Campo 25 ---------------------------------");
    //validacion campo 25
    if(isset($consultSection[24])) {
      if(strlen(trim($consultSection[23])) != '')
      {
        if(strlen($consultSection[24]) > 50 || trim($consultSection[24]) == ''){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 25, "Ya que el campo 24 no es vacío este campo tampoco puede ser vacío debe tener una longitud menor o igual a 50 caracteres.", "=\"".$consultSection[24]."\""]);
        }
      }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 25, "El campo no debe ser nulo", "=\"".$consultSection[24]."\""]);
    }

    Log::info("----------------------- Campo 26 ---------------------------------");
    //validacion campo 26
    if(isset($consultSection[25])) {
      if(strlen(trim($consultSection[25])) != '')
      {
        if(strlen($consultSection[25]) > 4){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 26, "El campo debe tener un longitud menor o igual 4 caracteres.", "=\"".$consultSection[25]."\""]);
        }else{
          $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico',$consultSection[25])->first();
          if(!$exists){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 26, "El valor no corresponde a un valor código de diagnóstico valido", "=\"".$consultSection[25]."\""]);
          }
        }
      }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 26, "El campo no debe ser nulo", "=\"".$consultSection[25]."\""]);
    }

    Log::info("----------------------- Campo 27 ---------------------------------");
    //validacion campo 27
    if(isset($consultSection[26])) {
      if(strlen(trim($consultSection[25])) != '')
      {
        if(strlen($consultSection[26]) > 50 || trim($consultSection[26]) == ''){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 27, "Ya que el campo 26 no es vacío este campo tampoco puede ser vacío debe tener una longitud menor o igual a 50 caracteres.", "=\"".$consultSection[26]."\""]);
        }
      }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 27, "El campo no debe ser nulo", "=\"".$consultSection[26]."\""]);
    }

    Log::info("----------------------- Campo 28 ---------------------------------      ".$consultSection[27]);
    //validacion campo 28
    if(isset($consultSection[27])) {
        if(!preg_match("/^([1-3])$/", trim($consultSection[27]))) {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 28, "El campo debe tener una longitud igual a 1 caracter y debe corresponder a un tipo de diagnóstico válido.", "=\"".$consultSection[27]."\""]);
        }/*else{
          $exists = TipoDiagnostico::where('cod_tipo',$consultSection[27])->first();
          if (!$exists) {
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 28, "El campo no corresponde a la identificación tipo de diagnóstico válido."]);
          }
        }*/
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 28, "El campo no debe ser nulo", "=\"".$consultSection[27]."\""]);
    }

    Log::info("----------------------- Campo 29 ---------------------------------");
    //validacion campo 29
    if(isset($consultSection[28])) {
      if($consultSection[28] != '') {
        if(!preg_match("/^(([1-9])|(0[1-9])|(10)|(99))$/", $consultSection[28])){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 29, "El campo debe ser un valor numérico de 2 dígitos y debe corresponder a una finalidad de consulta válida", "=\"".$consultSection[28]."\""]);
        }/*else{
          $exists = FinalidadConsultum::where('cod_finalidad',$consultSection[28])->first();
          if (!$exists) {
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 29, "El valor del campo no corresponde a un número de identificación de finalidad de consulta válido."]);
          }
        }*/
      }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 29, "El campo no debe ser nulo", "=\"".$consultSection[28]."\""]);
    }

    $temp_array = $consultSection;
    //Log::info("Termina las validaciones de Método AAC (FileValidator - Linea 520)");
  }

  protected function validateDates(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF,$firstRow ,$data)
  {

    if (strtotime($firstRow[3]) < strtotime($data[13]) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha final del periodo reportado  (línea 1, campo 4)", "=\"".$data[13]."\""]);
    }

    //se valida que la fecha de nacimiento sa inferior a la fecha de consulta
    if (strtotime($data[15]) < strtotime($data[13]) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha de consulta (campo 16)"], "=\"".$data[13]."\"");
    }

    //se valida que la fecha de consulta esté entre la fecha de los periodos
    if ( (strtotime($firstRow[2]) > strtotime($data[15])) || (strtotime($firstRow[3]) < strtotime($data[15])) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 16, "La fecha de consulta (campo 16) debe estar registrada entre el periodo reportado. fecha incial(línea 1, campo 3) y fecha final (línea 1, campo 4)", "=\"".$data[15]."\""]);
    }

  }

}