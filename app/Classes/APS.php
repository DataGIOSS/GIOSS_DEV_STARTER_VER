<?php

namespace App\Classes;

use App\Classes\FileValidator;
use App\Traits\ToolsForFilesController;
use App\Models\FileStatus;
use App\Models\Archivo;
use App\Models\UserIp;
use App\Models\Registro;
use App\Models\Eapb;
use App\Models\Ambito;
use App\Models\EntidadesSectorSalud;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\DiagnosticoCiex;
use App\Models\ProcedimientoCup;
use App\Models\HomologosCupsCodigo;
use App\Models\ProcedimientosQNq;
use App\Models\GiossArchivoApsCfvl;

class APS extends FileValidator {

  var $datos_creacion_global;

  function __construct($pathfolder, $fileName,$consecutive, $datos_creacion) {
    $filePath = $pathfolder.$fileName;
    $this->countLine($filePath);
    $this->datos_creacion_global = $datos_creacion;
    if(!($this->handle = fopen($pathfolder.$fileName, 'r'))) throw new Exception("Error al abrir el archivo APS");
    //dd($fileName);
    $this->folder = $pathfolder;

    $fileNameToken = explode('.',$fileName);
    $this->fileName =  substr($fileNameToken[0],0,58);
    $this->version = substr($fileNameToken[0],58);

    $this->consecutive = $consecutive;
    $this->detail_erros = array(['No. línea archivo original', 'No. linea en archivo de errores','Campo', 'Descripción', 'Valor Registrado']);
    $this->wrong_rows =  array();
    $this->success_rows =  array();

  }

  public function manageContent() {

    try {

      // se validad la existencia del archivo
      $isValidFile = true;
      $fileid = 0;

      $exists = DB::table('archivo')->where('nombre', $this->fileName)
                ->where('version', $this->version)
                ->first(); 

      if($exists){
        $isValidFile = false;
        array_push($this->detail_erros, [0, 0, '', "El archivo ya fue gestionado. Por favor actualizar la version", $this->fileName]);
        $fileid = $exists->id_archivo_seq;
      }else{
          //se define en primera instancia el objeto archivo
      
          $this->archivo = new Archivo();
          $this->archivo->modulo_informacion = 'SGD';
          $this->archivo->nombre = $this->fileName;
          $this->archivo->version = $this->version;
          $this->archivo->id_tema_informacion = 'APS';
          $this->archivo->save();

          $fileid = $this->archivo->id_archivo_seq;

      }

      // se inicializa el objeto file_status 
      $this->file_status =  new FileStatus();
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
          $this->dropWhiteSpace($data); // se borran los espcaios en de cada campo
          $isValidRow = true;
          $temp_array = Array();

          $this->validateEntitySection($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,0,6));
          $this->validateUserSection($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,6,9,true));
          $this->validateAPS($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,15,7,true), $temp_array);

          foreach ($temp_array as $key => $value) {
            $data[$key] = $value;
          }

          if ($isValidRow) // se validan cohenrencia entre fechas
          { 
            $this->validateDates($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, $firstRow,$data);
          }

          if(!$isValidRow){
            
            array_push($this->wrong_rows, $data);
            $this->updateStatusFile($lineCount); //se acatualiza la lienea ya tratada
            $lineCount++;
            $lineCountWF++;
            continue;
          }else{
            
            //se valida duplicidad en la informacion
            $exists = DB::table('gioss_archivo_aps_cfvl')->where('contenido_registro_validado', utf8_encode(implode('|', $data)))->first();

            if($exists){
              
              array_push($this->detail_erros, [$lineCount, $lineCountWF, '', "Registro duplicado", 0]);
              array_push($this->wrong_rows, $data);
              $this->updateStatusFile($lineCount);
              $lineCountWF++;
              $lineCount++;
              continue;
            }else
            {

              //se guarda todo el registro en en la tabla soporte
              $tabla = new GiossArchivoApsCfvl();
              $tabla->fecha_periodo_inicio = $this->archivo->fecha_ini_periodo;
              $tabla->fecha_periodo_fin = $this->archivo->fecha_fin_periodo;
              $tabla->nombre_archivo = utf8_encode($this->fileName);
              $tabla->numero_registro = $lineCount;
              $tabla->contenido_registro_validado = utf8_encode(implode('|', $data));
              $tabla->fecha_hora_validacion = time();
              $tabla->save();
              
              // alamacena en la dimension
              $exists = DB::table('user_ips')->where('num_identificacion', $data[8])->orderBy('created_at', 'desc')->first();

              $createNewUserIp = true;
              $useripsid = 0;

              if($exists){
                if($exists->num_historia_clinica ==  $data[6] || $exists->tipo_identificacion ==  $data[7] || $exists->primer_apellido ==  $data[9] || $exists->segundo_apellido ==  $data[10] || $exists->primer_nombre ==  $data[11] || $exists->segundo_nombre ==  $data[12] || $exists->fecha_nacimiento ==  $data[13] || $exists->sexo ==  $data[14])
                {
                  $createNewUserIp = false;
                  $useripsid = $exists->id_user;
                }
              }

              if($createNewUserIp)
              {
                $ipsuser = new UserIp();
                $ipsuser->num_historia_clinica = $data[6];
                $ipsuser->tipo_identificacion = $data[7];
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
              
              $cadena_temp=ltrim($data[3], '0');
              $cadena_test=substr($cadena_temp, 0,  strlen($cadena_temp) - 1);

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

              //se almacena la información correpondiente al procedimiento
              $procedimiento = new ProcedimientosQNq();
              $procedimiento->id_registro = $register->id_registro_seq;
              $procedimiento->fecha_procedimiento = strtotime($data[15]);
              $procedimiento->tipo_codificacion = $data[17];
              $procedimiento->cod_procedimiento = $data[16];
              $procedimiento->cod_diagnostico_principal = $data[18];
              $procedimiento->cod_diagnostico_rel1 = $data[20];
              $procedimiento->ambito_procedimiento = $data[21];
            
              $procedimiento->save();

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
      Log::info(print_r($e->getMessage(), true));
    }

  }

  private function validateAPS(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF, $consultSection, &$temp_array) {

    //validacion campo 16
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
        array_push($detail_erros, [$lineCount, $lineCountWF, 16, "El campo debe terner el formato AAAA-MM-DD", "=\"".$consultSection[15]."\""]);
      }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 16, "El campo no debe ser nulo", "=\"".$consultSection[15]."\""]);
    }
    
    //validacion campo 18
     if(isset($consultSection[17])) {
        
        switch ($consultSection[17]) {
          
          case '1':

            //Adaptación de la validación inicial para garantizar la existencia del código y que corresponda  a un CUP.
            //Además se valida el tipo del dato recibido.
            if(isset($consultSection[16])) {
              if (ctype_alpha(trim($consultSection[16]))) {
                if(strlen(trim($consultSection[16])) <= 8){
                    $exists = DB::table('procedimiento_cups')->where('cod_procedimiento', $consultSection[16])->first();
                    if(!$exists){
                      $exists_Homologo = DB::table('homologos_cups_codigos')->where('cod_homologo', $consultSection[16])->first();
                      if(!$exists_Homologo){
                          $isValidRow = false;
                          array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El valor del campo no corresponde a un codigo de procedimiento CUP ni HOMÓLOGO válido", "=\"".$consultSection[16]."\""]);
                      } else {
                        $consultSection[16] = $exists_Homologo->cod_cups;
                      }
                    }
                } else {
                    array_push($detail_erros, [$lineCount, $lineCountWF, 17, "ERROR INFORMATIVO: Ya que el Tipo de Codificación es igual a 1 el campo debería tener una longitud menor o igual a 8 caracteres", "=\"".$consultSection[16]."\""]);
                }
              } else {
                $isValidRow = false;
                array_push($detail_erros, [$lineCount, $lineCountWF, 17, "Este campo solo admite cadenas numéricas o alfanuméricas, no cadenas enteramente alfabéticas.", "=\"".$consultSection[16]."\""]);
              }
            } else {
              $isValidRow = false;
              array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo no debe ser nulo", "=\"".$consultSection[16]."\""]);
            }

          break;

          case '4':

            if(isset($consultSection[16])) {
              if (ctype_alpha(trim($consultSection[16]))) {
                if(strlen(trim($consultSection[16])) <= 10){
                    $exists_Homologo = DB::table('homologos_cups_codigos')->where('cod_homologo', $consultSection[16])->first();
                    if(!$exists_Homologo){
                      $exists = DB::table('procedimiento_cups')->where('cod_procedimiento', $consultSection[16])->first();
                      if(!$exists){
                          $isValidRow = false;
                          array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El valor del campo no corresponde a un codigo de procedimiento CUP ni HOMÓLOGO válido", "=\"".$consultSection[16]."\""]);
                      }
                    } else {
                      $consultSection[16] = $exists_Homologo->cod_cups;
                    }
                } else {
                    array_push($detail_erros, [$lineCount, $lineCountWF, 17, "ERROR INFORMATIVO: Ya que el Tipo de Codificación es igual a 1 el campo debería tener una longitud menor o igual a 8 caracteres", "=\"".$consultSection[16]."\""]);
                }
              } else {
                $isValidRow = false;
                array_push($detail_erros, [$lineCount, $lineCountWF, 17, "Este campo solo admite cadenas numéricas o alfanuméricas, no cadenas enteramente alfabéticas.", "=\"".$consultSection[16]."\""]);
              }
            } else {
              $isValidRow = false;
              array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo no debe ser nulo", "=\"".$consultSection[16]."\""]);
            }

            break;

          default:
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo debe ser un número con un valor de 1 ó 4", "=\"".$consultSection[17]."\""]);
            break;
        }

        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo no debe ser nulo", "=\"".$consultSection[17]."\""]);
    }

    //se valida que el procedimiento campo 17 sea quirurjico
    $esQuirurjico = DB::table('procedimiento_cups')->where('cod_procedimiento',$consultSection[16])->where('cod_grup_cups', 11)->first();

    //validacion campo 19 ---
    $diagPrinConfirm = false;
    if(isset($consultSection[18])) {

        if($esQuirurjico){
          $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico',$consultSection[18])->first();
          if(!$exists){
            $diagPrinConfirm = true;
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El valor no corresponde a un valor código de diagnóstico valido", "=\"".$consultSection[18]."\""]);
          }
        }
        
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo no debe ser nulo", "=\"".$consultSection[18]."\""]);
    }

    //validacion campo 20
    if(isset($consultSection[19])) {
        if($esQuirurjico){
          if(strlen(trim($consultSection[19])) > 50){
            $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo  debe tener una longitud menor o igual a 50", "=\"".$consultSection[19]."\""]);
          }
        }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo no debe ser nulo", "=\"".$consultSection[19]."\""]);
    }

    //validación campo 21
    if(isset($consultSection[20])) {
          $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico', $consultSection[20])->first();
          if(!$exists){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 21, "El valor no corresponde a un valor código de diagnóstico valido", "=\"".$consultSection[20]."\""]);
          }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 21, "El campo no debe ser nulo", "=\"".$consultSection[20]."\""]);
    }

    //validación campo 22
    if(isset($consultSection[21])) {

      if(strlen(trim($consultSection[21])) != 1){
          $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El campo de tener una longitud igual a 1", "=\"".$consultSection[21]."\""]);
        }else{
          $exists = DB::table('ambito')->where('cod_ambito',$consultSection[21])->first();
          if(!$exists){
            array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El valor del campo no correponde a un Ambito valido", "=\"".$consultSection[21]."\""]);
          }
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El campo no debe ser nulo", "=\"".$consultSection[21]."\""]);
    }

    $temp_array = $consultSection;

  }

  protected function validateDates(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF,$firstRow ,$data)
  {
    //se valida que las fecha de nacimiento sea inferior a las fecha correpondientes a los peiodos

    if (strtotime($firstRow[3]) < strtotime($data[13]) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha final del periodo reportado  (línea 1, campo 4)", "=\"".$data[13]."\""]);
    }

    //se valida que la fecha de nacimiento sa inferior a la Fecha de Realización Procedimiento 
    if (strtotime($data[15]) < strtotime($data[13]) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la Fecha de Realización Procedimiento  (campo 16)", "=\"".$data[13]."\""]);
    }

    //se valida que la Fecha de Realización Procedimiento  esté entre la fecha de los periodos
    if ( (strtotime($firstRow[2]) > strtotime($data[15])) || (strtotime($firstRow[3]) < strtotime($data[15])) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 16, "La Fecha de Realización Procedimiento  (campo 16) debe estar registrada entre el periodo reportado. fecha incial(línea 1, campo 3) y fecha final (línea 1, campo 4) ", "=\"".$data[15]."\""]);
    }

  }

}