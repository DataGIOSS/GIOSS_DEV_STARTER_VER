<?php

namespace App\Classes;

use App\Classes\FileValidator;
use App\Traits\ToolsForFilesController;
use App\Models\FileStatus;
use App\Models\Archivo;
use App\Models\UserIp;
use App\Models\Registro;
use App\Models\Eapb;
use App\Models\EntidadesSectorSalud;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Vacunacion;
use App\Models\VacunaCup;
use App\Models\HomologosCupsCodigo;
use App\Models\GiossArchivoAvaCfvl;

class AVA extends FileValidator {

  var $datos_creacion_global;

  function __construct($pathfolder, $fileName,$consecutive, $datos_creacion) {
    Log::info("------- El arreglo datos creacion en constructor: ".print_r($datos_creacion, true));

    $filePath = $pathfolder.$fileName;
    $this->countLine($filePath);
    $this->datos_creacion_global = $datos_creacion;
    if(!($this->handle = fopen($pathfolder.$fileName, 'r'))) throw new Exception("Error al abrir el archivo AVA");
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
      Log::info("------- El arreglo datos creacion en manageContent es: ".print_r($this->datos_creacion_global, true));
      // se valida la existencia del archivo
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
          $this->archivo->id_tema_informacion = 'AVA';
          $this->archivo->save();

          $fileid = $this->archivo->id_archivo_seq;

      }

      // se inicializa el objeto file_status 
      $this->file_status =  new FileStatus();
      $this->file_status->consecutive = $this->consecutive;
      $this->file_status->archivoid = $fileid;
      $this->file_status->current_status = 'WORKING';
      Log::info("Se creó el archivo en la base de datos, no se han añadido fecha y hora en File Statuses");
      $this->file_status->usuario_creacion = $this->datos_creacion_global[0];
      $this->file_status->fecha_creacion = $this->datos_creacion_global[1];
      $this->file_status->hora_creacion = $this->datos_creacion_global[2];
      $this->file_status->save();  
      Log::info("Se guardó File Statuses");

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
          $this->validateAVA($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,15,14,true), $temp_array);

          foreach ($temp_array as $key => $value) {
            $data[$key] = $value;
          }

          if ($isValidRow) // se valida la cohenrencia entre fechas
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
            $exists = DB::table('gioss_archivo_ava_cfvl')->where('contenido_registro_validado', utf8_encode(implode('|', $data)))->first();

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
                $tabla = new GiossArchivoAvaCfvl();
                $tabla->fecha_periodo_inicio = $this->archivo->fecha_ini_periodo;
                $tabla->fecha_periodo_fin = $this->archivo->fecha_fin_periodo;
                $tabla->nombre_archivo = utf8_encode($this->fileName);
                $tabla->numero_registro = $lineCount;
                $tabla->contenido_registro_validado = utf8_encode(implode('|', $data));
                $tabla->fecha_hora_validacion = time() ;
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
              //se alamcena la informacion de la relacion registro
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

              //se almacena la información correpondiente a la vacunacion
              $vacunacion = new Vacunacion();
              $vacunacion->id_registro = $register->id_registro_seq;
              $vacunacion->fecha_aplicacion = $data[15];
              $vacunacion->tipo_codificacion = $data[17];
              $vacunacion->codigo_tipo_vacuna = $data[16];
              $vacunacion->numero_dosis = $data[18];

              $vacunacion->save();

              array_push($this->success_rows, $data);
              $this->updateStatusFile($lineCount - 1);
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
          $this->updateStatusFile($lineCount - 1);
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
      Log::info("Error de ejecución ARCHIVO AVA ".print_r($e->getMessage(), true));
    }

  }


  private function validateAVA(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF,$consultSection, &$temp_array) 
  {

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

    //validacion campo 17
    if(isset($consultSection[16])) {
        if(strlen(trim($consultSection[16])) > 8){
          
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo no debe ser nulo", "=\"".$consultSection[16]."\""]);
    }

    //validacion campo 18
    if(isset($consultSection[17])) {
        if(!is_numeric($consultSection[17]) || strlen(trim($consultSection[17])) != 1){
          $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo debe ser un número de un dígito", "=\"".$consultSection[17]."\""]);
        }else{
          switch ($consultSection[17]) {
            case '1':
              if (ctype_alpha(trim($consultSection[16]))) {
                $isValidRow = false;
                array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo de código del tipo de vacuna no puede estar compuesto por una cadena totalmente alfabética.", "=\"".$consultSection[16]."\""]);
              } else {
                if (strlen(trim($consultSection[16])) > 8) {
                  $isValidRow = false;
                  array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo de tener una longitud de máximo 8 caracteres", "=\"".$consultSection[16]."\""]);
                } else {
                  $exists = DB::table('vacuna_cups')->where('codigo_tipo_vacuna',$consultSection[16])->first();
                  if(!$exists){
                    $existsHomologo = DB::table('homologos_cups_codigos')->where('cod_homologo',$consultSection[16])->first();
                    if(!$existsHomologo){
                      $isValidRow = false;
                      array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El valor del campo no corresponde a un codigo de vacuna ni CUP ni homologo válido", "=\"".$consultSection[16]."\""]);
                    }else{
                      $esCup = DB::table('vacuna_cups')->where('codigo_tipo_vacuna', $existsHomologo->cod_cups)->first();
                      if (!$esCup) {
                        $isValidRow = false;
                        array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El código CUP del código homólogo recibido no fue encontrado en los registros de códigos válidos.", "=\"".$consultSection[16]."\""]);
                      } else {
                        $consultSection[16] = $exists->cod_cups;
                      }
                    }
                  }
                }
              }

              break;
            
            case '4':
              if (ctype_alpha(trim($consultSection[16]))) {
                $isValidRow = false;
                array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo de código del tipo de vacuna no puede estar compuesto por una cadena totalmente alfabética.", "=\"".$consultSection[16]."\""]);
              } else {
                if (strlen(trim($consultSection[16])) > 8) {
                  $isValidRow = false;
                  array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo de tener una longitud de máximo 8 caracteres", "=\"".$consultSection[16]."\""]);
                } else {
                  $exists = DB::table('homologos_cups_codigos')->where('cod_homologo',$consultSection[16])->first();
                  if(!$exists){
                    $existsCUP = DB::table('vacuna_cups')->where('codigo_tipo_vacuna', $consultSection[16])->first();
                    if (!$existsCup) {
                       $isValidRow = false;
                       array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El valor del campo no corresponde a un codigo de vacuna CUP ni a un código de vacuna homologa válida", "=\"".$consultSection[16]."\""]);
                     }
                  }else{
                    $esCodigoCup = DB::table('vacuna_cups')->where('codigo_tipo_vacuna', $exists->cod_cups)->first();
                    if (!$esCodigoCup) {
                      $isValidRow = false;
                      array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El código CUP del código homólogo recibido no fue encontrado en los registros de códigos válidos.", "=\"".$consultSection[16]."\""]);
                    } else {
                      $consultSection[16] = $exists->cod_cups;
                    }
                  }
                }
              }

              break;

            default:
              $isValidRow = false;
              array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El tipo de codificación solo puede recibir un valor igual a 1 ó 4.", "=\"".$consultSection[16]."\""]);

          }

        }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo no debe ser nulo", "=\"".$consultSection[17]."\""]);
    }

    //validacion campo 19
    if(isset($consultSection[18])) {
        if(!ctype_digit($consultSection[18]) || (trim($consultSection[18]) < 1 || trim($consultSection[18]) > 5)) {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo debe tener un valor numérico entre 1 y 5", "=\"".$consultSection[18]."\""]);
        }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo no debe ser nulo", "=\"".$consultSection[18]."\""]);
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

    //se valida que la fecha de nacimiento sa inferior a la Fecha de la Aplicación de la Dosis de Vacunación 
    if (strtotime($data[15]) < strtotime($data[13]) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la Fecha de la Aplicación de la Dosis de Vacunación  (campo 16)", "=\"".$data[13]."\""]);
    }

    //se valida que la Fecha de la Aplicación de la Dosis de Vacunación  esté entre la fecha de los periodos
    if ( (strtotime($firstRow[2]) > strtotime($data[15])) || (strtotime($firstRow[3]) < strtotime($data[15])) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 16, "La Fecha de la Aplicación de la Dosis de Vacunación  (campo 16) debe estar registrada entre el periodo reportado. fecha incial(línea 1, campo 3) y fecha final (línea 1, campo 4) ", "=\"".$data[15]."\""]);
    }

  }

}