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

use App\Models\Ambito;
use App\Models\MedicamentosAtc;
use App\Models\MedicamentosCum;
use App\Models\MedicamentosHomologo;
use App\Models\MedicamentosR;
use App\Models\Medicamento;
use App\Models\GiossArchivoAmsCfvl;

class AMS extends FileValidator {

  var $datos_creacion_global;

  function __construct($pathfolder, $fileName,$consecutive, $datos_creacion) {
    $filePath = $pathfolder.$fileName;
    $this->countLine($filePath);
    $this->datos_creacion_global = $datos_creacion;
    if(!($this->handle = fopen($pathfolder.$fileName, 'r'))) throw new Exception("Error al abrir el archivo ASM");
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

  public function manageContent() {

    try {
      Log::info("entro managecontent");

      //valida primera linea

      $isValidFirstRow = true;
      
      $firstRow = fgetcsv($this->handle, 0, "|");
      
      $this->validateFirstRow($isValidFirstRow, $this->detail_erros, $firstRow);

      // se validad la existencia del archivo
      $isValidFile = true;
      $fileid = 0;

      $exists = DB::table('archivo')->where('nombre', $this->fileName)
                ->where('version', $this->version)
                ->first(); 

      if($exists)
      {
        $isValidFile = false;
        array_push($this->detail_erros, [0, 0, '', "El archivo ya fue gestionado. Por favor actualizar la version"]);
        $fileid = $exists->id_archivo_seq;
      }//fin if existe
      else 
      {
          //se define en primera instancia el objeto archivo
      
          $this->archivo = new Archivo();
          $this->archivo->modulo_informacion = 'SGD';
          $this->archivo->nombre = $this->fileName;
          $this->archivo->version = $this->version;
          $this->archivo->id_tema_informacion = 'ASM';
          $this->archivo->save();

          Log::info("Se crea el archivo");

          $fileid = $this->archivo->id_archivo_seq;

      }//fin else

      // se inicializa el objeto file_status 
      $this->file_status =  new FileStatus();
      $this->file_status->consecutive = $this->consecutive;
      $this->file_status->archivoid = $fileid;
      $this->file_status->current_status = 'WORKING';
      $this->file_status->usuario_creacion = $this->datos_creacion_global[0];
      $this->file_status->fecha_creacion = $this->datos_creacion_global[1];
      $this->file_status->hora_creacion = $this->datos_creacion_global[2];
      $this->file_status->save();


      if ($isValidFirstRow && $isValidFile) 
      {
        Log::info("Validaciones iniciales correctas");

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


          $this->validateEntitySection($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,0,6));
          $this->validateUserSection($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,6,9,true));
          $this->validateAMS($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,15,5,true));

          if ($isValidRow) // se validan cohenrencia entre fechas
          { 
            $this->validateDates($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, $firstRow,$data);
          }
          
          if(!$isValidRow){
            Log::info("linea invalida".$lineCount);
            array_push($this->wrong_rows, $data);
            $this->updateStatusFile($lineCount); //se acatualiza la lienea ya tratada
            $lineCount++;
            $lineCountWF++;
            continue;
          }else{
              Log::info("entro a guardar: ".$lineCount);
            //se valida duplicidad en la informacion
            $exists = DB::table('gioss_archivo_ams_cfvl')->where('contenido_registro_validado', utf8_encode(implode('|', $data)))->first();

            if($exists){
              
              array_push($this->detail_erros, [$lineCount, $lineCountWF, '', "Registro duplicado"]);
              array_push($this->wrong_rows, $data);
              $this->updateStatusFile($lineCount);
              $lineCountWF++;
              $lineCount++;
              continue;
            }
            else
            {
              //se guarda todo el registro en en la tabla soporte
                $tabla = new GiossArchivoAmsCfvl();
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
                
                if($exists->num_historia_clinica ==  $data[6] || $exists->tipo_identificacion ==  $data[7] || $exists->primer_apellido ==  $data[9] || $exists->segundo_apellido ==  $data[10] || $exists->primer_nombre ==  $data[11] || $exists->segundo_nombre ==  $data[12] || $exists->fecha_nacimiento ==  $data[13] || $exists->sexo ==  $data[14])
                {
                  $createNewUserIp = false;
                  $useripsid = $exists->id_user;
                }
              }

              if($createNewUserIp)
              {
                Log::info("crea nuevo user");
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

              $eapb  = Eapb::where('num_identificacion', $cadena_test)
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

              Log::info("se crea anuevo medicamento ");
              //se almacena la informración correpondiente al Medicamento suministrado
              $medicamento = new Medicamento();
              $medicamento->id_registro = $register->id_registro_seq;
              $medicamento->fecha_entrega = strtotime($data[15]);
              $medicamento->tipo_codificacion = $data[17];
              $medicamento->codigo_medicamento = $data[16];
              $medicamento->catidad = $data[18];
              $medicamento->ambito_suministro = $data[19];
  
              $medicamento->save();

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
        Log::info("Se generan los archivos de reporte");
        
        $this->generateFiles();

        if(!$isValidFirstRow){
          $this->archivo->version = '999';
          $this->archivo->save();
        }

      }
    
    } catch (\Exception $e) {
      Log::error(print_r($e->getMessage(), true)."Error");
    }

  }

  private function validateAMS(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF, $consultSection) {

    //validacion campo 16
    if(isset($consultSection[15])) {
      if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[15])){
        $date = explode('-', $consultSection[15]);
        if(!checkdate($date[1], $date[2], $date[0])){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 16, "El campo debe corresponder a un fecha válida."]);
        }  
      }
      else{
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 16, "El campo debe terner el formato AAAA-MM-DD"]);
      }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 16, "El campo no debe ser nulo"]);
    }
    Log::info("termino validacion camp 16 ");

    //validacion campo 17
    if(isset($consultSection[16])) {
        if(strlen(trim($consultSection[16])) > 20){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo debe tener una longitud menor o igual a 20 caracteres"]);
        }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo no debe ser nulo"]);
    }
    //Log::info("termino validacion camp 17 ");

    //validacion campo 18
     if(isset($consultSection[17])) {
        
        switch ($consultSection[17]) {
          case '1':
            if (trim($consultSection[16]) != '') {
              $existsCum = DB::table('medicamentos_cum')->where('codigo_medicamento', ltrim($consultSection[16], '0'))->first();
              if(!$existsCum){
                $existsAtc = DB::table('medicamentos_atc')->where('codigo_medicamento', ltrim($consultSection[16], '0'))->first();
                if (!$existsAtc) {
                  $existsHomologo = DB::table('medicamentos_homologo')->where('codigo_medicamento', ltrim($consultSection[16], '0'))->first();
                  if (!$existsHomologo) {
                    $isValidRow = false;
                    array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El valor del campo no corresponde a un codigo de medicamento CUM, ATC ni Homólogo válido"]);
                  } else {
                    $esCum = DB::table('medicamentos_cum')->where('codigo_medicamento', $existsHomologo->codigo_cum)->first();
                    if(!$esCum){
                      $isValidRow = false;
                      array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El código homólogo entregado en este campo no corresponde a un código existente entre los códigos CUM"]);
                    } else {
                      $consultSection[16] = $existsHomologo->codigo_cum;
                    }
                  }
                }
              }
            } else {
              $isValidRow = false;
              array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo de codigo del medicamento no puede estar vacío"]);
            }
            
            break;
          
          case '2':
            if (trim($consultSection[16]) != '') {
              $existsAtc = DB::table('medicamentos_atc')->where('codigo_medicamento', ltrim($consultSection[16], '0'))->first();
              if(!$existsAtc){
                $existsCum = DB::table('medicamentos_cum')->where('codigo_medicamento', ltrim($consultSection[16], '0'))->first();
                if (!$existsCum) {
                  $existsHomologo = DB::table('medicamentos_homologo')->where('codigo_medicamento', ltrim($consultSection[16], '0'))->first();
                  if (!$existsHomologo) {
                    $isValidRow = false;
                    array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El valor del campo no corresponde a un codigo de medicamento ATC, CUM ni Homólogo válido"]);
                  } else {
                    $esCum = DB::table('medicamentos_cum')->where('codigo_medicamento', $existsHomologo->codigo_cum)->first();
                    if (!$esCum) {
                      $isValidRow = false;
                      array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El código homólogo entregado en este campo no corresponde a un código CUM válido."]);
                    }
                  }
                }
              }
            } else {
              $isValidRow = false;
              array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo de codigo del medicamento no puede estar vacío"]);
            }

            break;

          case '3':
            if (trim($consultSection[16]) != '') {
              $existsHomologo = DB::table('medicamentos_homologo')->where('codigo_medicamento', ltrim($consultSection[16], '0'))->first();
              if(!$existsHomologo){
                $existsCum = DB::table('medicamentos_cum')->where('codigo_medicamento', ltrim($consultSection[16], '0'))->first();
                if (!$existsCum) {
                  $existsAtc = DB::table('medicamentos_atc')->where('codigo_medicamento', ltrim($consultSection[16], '0'))->first();
                  if (!$existsAtc) {
                    $isValidRow = false;
                    array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El valor del campo no corresponde a un codigo de medicamento CUM, ATC ni Homólogo válido"]);
                  }
                }
              } else {
                $esCum = DB::table('medicamentos_cum')->where('codigo_medicamento', $existsHomologo->codigo_cum)->first();
                if (!$esCum) {
                  $isValidRow = false;
                    array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El código Homólogo entregado no corresponde a un código CUM válido"]);
                }
              }
            } else {
              $isValidRow = false;
              array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo de codigo de medicamento no puede estar vacío"]);
            }

            break;

          default:
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo debe ser un número con un valor entre 1 y 4"]);
            break;
        }

        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo no debe ser nulo"]);
    }
    Log::info("termino validacion camp 18 ");

    //validacion campo 19
    if(isset($consultSection[18])) {
        if(!ctype_digit(trim($consultSection[18])) || strlen(trim($consultSection[18])) > 3){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo debe ser un valor entero de máximo 3 dígitos"]);
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo no debe ser nulo"]);
    }

    Log::info("termino validacion camp 19 ");

    //validacion campo 20
    if(isset($consultSection[19])) {
        if(strlen(trim($consultSection[19])) != 1){
          $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo de tener una longitud igual a 1"]);
        }else{
          $exists = DB::table('ambito')->where('cod_ambito',$consultSection[19])->first();
          if(!$exists){
            array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El valor del campo no correponde a un Ambito valido"]);
          }
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo no debe ser nulo"]);
    }
    Log::info("termino validacion camp 20 ");

  }

  protected function validateDates(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF,$firstRow ,$data) {
 

    if (strtotime($firstRow[3]) < strtotime($data[13]) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha final del periodo reportado  (línea 1, campo 4)"]);
    }

    //se valida que la fecha de nacimiento sa inferior a la Fecha de Entrega de Medicamento
    if (strtotime($data[15]) < strtotime($data[13]) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la Fecha de Entrega de Medicamentoa (campo 16)"]);
    }

    //se valida que la Fecha de Entrega de Medicamento esté entre la fecha de los periodos
    if ( (strtotime($firstRow[2]) > strtotime($data[15])) || (strtotime($firstRow[3]) < strtotime($data[15])) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 16, "La Fecha de Entrega de Medicamento (campo 16) debe estar registrada entre el periodo reportado. fecha incial(línea 1, campo 3) y fecha final (línea 1, campo 4) "]);
    }

  }

}