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

use App\Models\DiagnosticoCiex;
use App\Models\IngresosEgresosHospitalario;
use App\Models\GiossArchivoAehCfvl;

class AEH extends FileValidator {

  var $datos_creacion_global;

  function __construct($pathfolder, $fileName,$consecutive, $datos_creacion) {
     Log::info("------- El arreglo datos creacion en constructor: ".print_r($datos_creacion, true));
    $filePath = $pathfolder.$fileName;
    $this->countLine($filePath);
    $this->datos_creacion_global = $datos_creacion;
    if(!($this->handle = fopen($pathfolder.$fileName, 'r'))) throw new Exception("Error al abrir el archivo AEH");
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
          $this->archivo->id_tema_informacion = 'AEH';
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
          $this->validateAEH($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,15,13,true), $temp_array);

          foreach ($temp_array as $key => $value) {
            $data[$key] = $value;
          }

          if ($isValidRow) // se validan cohenrencia entre fechas
          { 
            $this->validateDates($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, $firstRow,$data);
          }

          if(!$isValidRow){
            
            array_push($this->wrong_rows, $data);
            $this->updateStatusFile($lineCount); //se acatualiza la linea ya tratada
            $lineCount++;
            $lineCountWF++;
            continue;
          }else {
              
            $exists = DB::table('gioss_archivo_aeh_cfvl')->where('contenido_registro_validado', utf8_encode(implode('|', $data)))->first();

            if($exists){
              
              array_push($this->detail_erros, [$lineCount, $lineCountWF, '', "Registro duplicado", 0]);
              array_push($this->wrong_rows, $data);
              $this->updateStatusFile($lineCount);
              $lineCountWF++;
              $lineCount++;
              continue;
            }else
            {
              Log::info("Llega a la sección de creación del registro exitoso");
              //se guarda todo el registro en en la tabla soporte
                $tabla = new GiossArchivoAehCfvl();
                $tabla->fecha_periodo_inicio = utf8_encode($this->archivo->fecha_ini_periodo);
                $tabla->fecha_periodo_fin = utf8_encode($this->archivo->fecha_fin_periodo);
                $tabla->nombre_archivo = utf8_encode($this->fileName);
                $tabla->numero_registro = utf8_encode($lineCount);
                $tabla->contenido_registro_validado = utf8_encode(implode('|', $data));
                $tabla->fecha_hora_validacion = utf8_encode(time());
                $tabla->save();
                Log::info("Crea en la base de datos el registro exitoso");

              //
              // alamacena en la dimension
              $exists = DB::table('user_ips')->where('num_identificacion', $data[8])->orderBy('created_at', 'desc')->first();

              $createNewUserIp = true;
              $useripsid = 0;

              if($exists){
                if($exists->num_historia_clinica ==  $data[6] || $exists->tipo_identificacion ==  $data[7] || $exists->primer_apellido ==  $data[9] || $exists->segundo_apellido ==  $data[10] || $exists->primer_nombre ==  $data[11] || $exists->segundo_nombre ==  $data[12] || $exists->fecha_nacimiento ==  $data[13] || $exists->sexo ==  $data[14])
                {
                  Log::info("El usuario existe");
                  $createNewUserIp = false;
                  $useripsid = $exists->id_user;
                }
              }

              if($createNewUserIp)
              {
                Log::info("El usuario no existe por lo tanto lo crea");
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

                $ipsuser->save();
                $useripsid = $ipsuser->id_user;
                Log::info("Guarda el usuario en la BD");
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
              //se alamcena la informacion de la relacion registro

              Log::info("Crea el objeto AEH");
              //se almacena la información correpondiente a la ingreso egreso hopitalario
              $aehobject = new IngresosEgresosHospitalario();
              $aehobject->id_registro = $register->id_registro_seq;
              $aehobject->fecha_hora_ingreso = strtotime($data[15].' '.$data[16]);
              $aehobject->fecha_hora_egreso = strtotime($data[17].' '.$data[18]);
              $aehobject->cod_diagnostico_ingreso = utf8_encode($data[19]);
              $aehobject->cod_diagnostico_egreso = utf8_encode($data[21]);
              $aehobject->cod_diagnostico_egreso_rel1 = utf8_encode($data[23]);
              $aehobject->cod_diagnostico_egreso_rel2 = utf8_encode($data[24]);
              $aehobject->estado_salida = utf8_encode($data[25]);
              $aehobject->codigo_diagnostico_muerte = utf8_encode($data[26]);

              $aehobject->save();
              Log::info("Guarda el objeto AEH en la BD");

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
      Log::error(print_r($e->getMessage(), true));
    }

  }

  private function validateAEH(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF, $consultSection, &$temp_array) {

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
          array_push($detail_erros, [$lineCount, $lineCountWF, 16, "El campo debe tener el formato AAAA-MM-DD", "=\"".$consultSection[15]."\""]);
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 16, "El campo no debe ser nulo", "=\"".$consultSection[15]."\""]);
    }

    //validacion campo 17
    if(isset($consultSection[16])) {
        if(!preg_match("/^(2[0-3]|[0-1][0-9]):([0-5][0-9])$/", $consultSection[16])){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo debe tener el formato HH:MM (Hora Militar)", "=\"".$consultSection[16]."\""]);
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo no debe ser nulo", "=\"".$consultSection[16]."\""]);
    }

    //validacion campo 18
    if(isset($consultSection[17])) {
        if(preg_match("/^([0-9]{4}-(0[0-9]|1[0-2])-(0[0-9]|[1-2][0-9]|3[0-1]))$/", $consultSection[17])) {
          $date = explode('-', $consultSection[17]);
          if(!checkdate($date[1], $date[2], $date[0])){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo debe corresponder a un fecha válida.", "=\"".$consultSection[17]."\""]);
          }   
        }else{
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo debe tener el formato AAAA-MM-DD ", "=\"".$consultSection[17]."\""]);
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo no debe ser nulo", "=\"".$consultSection[17]."\""]);
    }

    //validacion campo 19
    if(isset($consultSection[18])) {
        if(!preg_match("/^(2[0-3]|[0-1][0-9]):([0-5][0-9])$/", $consultSection[18])){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo debe tener el formato HH:MM (Hora Militar)", "=\"".$consultSection[18]."\""]);
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo no debe ser nulo", "=\"".$consultSection[18]."\""]);
    }

    //validacion campo 20
    if(isset($consultSection[19])) {
        if(strlen(trim($consultSection[19])) > 4){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo no puede ser vacío y debe tener un longitud menor o igual a 4 caracteres.", "=\"".$consultSection[19]."\""]);
        }else{
          $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico', $consultSection[19])->first();
          if(!$exists){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El valor no corresponde a un valor código de diagnóstico CIEX valido", "=\"".$consultSection[19]."\""]);
          }
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo no debe ser nulo", "=\"".$consultSection[19]."\""]);
    }

    //validacion campo 21
    if(isset($consultSection[20])) {
        if(strlen(trim($consultSection[20])) > 50 && trim($consultSection[20]) == ''){
          $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 21, "El campo no dede ser vacio y debe tener una longitud menor o igual a 50", "=\"".$consultSection[20]."\""]);
        }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 21, "El campo no debe ser nulo", "=\"".$consultSection[20]."\""]);
    }

    //validacion campo 22
    if(isset($consultSection[21])) {
        if(strlen(trim($consultSection[21])) > 4) {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El campo no puede ser vacío y debe tener una longitud menor o igual a 4 caracteres.", "=\"".$consultSection[21]."\""]);
        }else{
          $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico', $consultSection[21])->first();
          if(!$exists){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El valor no corresponde a un valor código de diagnóstico valido", "=\"".$consultSection[21]."\""]);
          }
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El campo no debe ser nulo", "=\"".$consultSection[21]."\""]);
    }

    //validacion campo 23
    if(isset($consultSection[22])) {
        if(strlen(trim($consultSection[22])) > 50 && trim($consultSection[22]) == ''){
          $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 23, "El campo no puede ser vacio y debe tener una longitud menor o igual a 50", "=\"".$consultSection[22]."\""]);
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 23, "El campo no debe ser nulo", "=\"".$consultSection[22]."\""]);
    }

    //validacion campo 24
    if(isset($consultSection[23])) {
        if(strlen(trim($consultSection[23])) != ''){

          if(strlen(trim($consultSection[23])) > 4){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 24, "El campo debe tener un longitud menor o igual a 4 caracteres.", "=\"".$consultSection[23]."\""]);
          }else{
            $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico', $consultSection[23])->first();
            if(!$exists){
              $isValidRow = false;
              array_push($detail_erros, [$lineCount, $lineCountWF, 24, "El valor no corresponde a un valor código de diagnóstico CIEX valido", "=\"".$consultSection[23]."\""]);
            }
          }

        }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 24, "El campo no debe ser nulo", "=\"".$consultSection[23]."\""]);
    }

    //validacion campo 25
    if(isset($consultSection[24])) {
        if(strlen(trim($consultSection[24])) != ''){

          if(strlen(trim($consultSection[24])) > 4){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 25, "El campo debe tener un longitud menor o igual a 4 caracteres.", "=\"".$consultSection[24]."\""]);
          }else{
            $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico', $consultSection[24])->first();
            if(!$exists){
              $isValidRow = false;
              array_push($detail_erros, [$lineCount, $lineCountWF, 25, "El valor no corresponde a un valor código de diagnóstico CIEX valido", "=\"".$consultSection[24]."\""]);
            }
          }

        }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 25, "El campo no debe ser nulo", "=\"".$consultSection[24]."\""]);
    }

    //validacion campo 26
    if(isset($consultSection[25])) {
        if(!preg_match("/^[1-2]$/", $consultSection[25])){
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 26, "El valor del campo no correponde a un valor valido: 1(vivo) - 2(muerto)", "=\"".$consultSection[25]."\""]);
        }
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 26, "El campo no debe ser nulo", "=\"".$consultSection[25]."\""]);
    }

    //validacion campo 27
    if(isset($consultSection[26])) {
        if(trim($consultSection[25]) == 2){ //si el anterior registro lo reporta como muerto (2)

          if(strlen($consultSection[26]) > 4 || trim($consultSection[26]) == ''){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 27, "El campo no puede ser vacío y debe tener una longitud menor o igual a 4 caracteres.", "=\"".$consultSection[26]."\""]);
          }else{
            $exists = DB::table('diagnostico_ciex')->where('cod_diagnostico', $consultSection[26])->first();
            if(!$exists){
              $isValidRow = false;
              array_push($detail_erros, [$lineCount, $lineCountWF, 27, "El valor no corresponde a un valor código de diagnóstico CIEX valido", "=\"".$consultSection[26]."\""]);
            }
          }

        }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 27, "El campo no debe ser nulo", "=\"".$consultSection[26]."\""]);
    }

    //validacion campo 28
    if(isset($consultSection[27])) {
        if (trim($consultSection[26]) != '') {
          if(strlen(trim($consultSection[27])) > 50 || trim($consultSection[27]) == ''){
            $isValidRow = false;
            array_push($detail_erros, [$lineCount, $lineCountWF, 28, "El campo no puede ser vacío ya que hay diagnóstico de muerte y debe tener una longitud menor o igual a 50 caracteres.", "=\"".$consultSection[27]."\""]);
          }
        }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 28, "El campo no debe ser nulo", "=\"".$consultSection[27]."\""]);
    }

    $temp_array = $consultSection;

  }

  protected function validateDates(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF,$firstRow ,$data)
  {
   

    if (strtotime($firstRow[3]) < strtotime($data[13]) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha final del periodo reportado  (línea 1, campo 4)", "=\"".$data[13]."\""]);
    }

    //se valida que la fecha de nacimiento sa inferior a la Fecha de Ingreso al Servicio de Hospitalización 
    if (strtotime($data[15]) < strtotime($data[13]) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la Fecha de Ingreso al Servicio de Hospitalización  (campo 16)", "=\"".$data[13]."\""]);
    }

    //se valida que la fecha de nacimiento sa inferior a la Fecha de Egreso al Servicio de Hospitalización 
    if (strtotime($data[17]) < strtotime($data[13]) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la Fecha de Egreso al Servicio de Hospitalización  (campo 18)", "=\"".$data[13]."\""]);
    }

    //se valida que la fecha de Ingreso esté entre la fecha de los periodos
    if ( (strtotime($firstRow[2]) > strtotime($data[15])) || (strtotime($firstRow[3]) < strtotime($data[15])) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 16, "Fecha de Ingreso al Servicio de Hospitalización (campo 16) debe estar registrada entre el periodo reportado. fecha incial(línea 1, campo 3) y fecha final (línea 1, campo 4).", "=\"".$data[15]."\""]);
    }

    //se valida que la fecha de Ingreso esté entre la fecha de los periodos
    if ( (strtotime($firstRow[2]) >= strtotime($data[17])) || (strtotime($firstRow[3]) <= strtotime($data[17])) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 18, "Fecha de Egreso al Servicio de Hospitalización (campo 18) debe estar registrada entre el periodo reportado. fecha incial(línea 1, campo 3) y fecha final (línea 1, campo 4).", "=\"".$data[17]."\""]);
    }

  }

}