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
use App\Models\ProtocoloQuimioterapia;
use App\Models\RegistroQuimioterapia;
use App\Models\EntidadesSectorSalud;
use App\Models\GiossArchivoArqCfvl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ARQ extends FileValidator {

  //Aquí se hace la apertura del archivo que fue guardado en la ruta que le pasa al constructor y que corresponde a la ruta de la carpeta Storage del proyecto en Laravel.

  function __construct($pathfolder, $fileName,$consecutive) {
    $filePath = $pathfolder.$fileName;
    $conteoLineas = $this->countLine($filePath);
    if(!($this->handle = fopen($filePath, 'r'))) throw new Exception("Error al abrir el archivo ARQ");
    
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
      Log::info("Inicia manageContent (ARQ - Linea 53)");

      // se validad la existencia del archivo
      $isValidFile = true;
      $fileid = 0;

      //Log::info("manageContent (ARQ - Linea 59)");
      $exists = Archivo::where('nombre', $this->fileName)
                ->where('version', $this->version)
                ->first(); 
      //Log::info("manageContent (ARQ - Linea 63)");
      //Si el archivo ya ha sido guardado en la base de datos, este debe descartarse 
      if($exists){
        Log::info("Entra al if (ARQ - Linea 66)");
        $isValidFile = false;
        array_push($this->detail_erros, [0, 0, '', "El archivo ya fue gestionado. Por favor actualice la version"]);
        $fileid = $exists->id_archivo_seq;
      //Si dicho archivo aún no existe en la base de datos entonces ahí si se guarda y es sometido a las validaciones necesarias
      } else {
          Log::info("Entra al else (ARQ - Linea 68)");
          //Se define en primera instancia el objeto archivo
      
          $this->archivo = new Archivo();
          Log::info("Crea el objeto archivo (ARQ - Linea 72)");
          $this->archivo->modulo_informacion = 'SGD';
          Log::info("Le asigna el módulo (ARQ - Linea 74): ".'SGD');
          $this->archivo->nombre = $this->fileName;
          Log::info("Le asigna el nombre (ARQ - Linea 76): ".$this->fileName);
          $this->archivo->version = $this->version;
          Log::info("Le asigna la version (ARQ - Linea 78): ".$this->version);
          $this->archivo->id_tema_informacion = 'ARQ';
          Log::info("Le asigna el id_tema_informacion (ARQ - Linea 80): ".'ARQ');
          $this->archivo->save(); //Falla esta linea inexplicablemente
          Log::info("Guarda el archivo (ARQ - Linea 83)");

          $fileid = $this->archivo->id_archivo_seq;

      }

      Log::info("manageContent (ARQ - Linea 85)");
      // se inicializa el objeto file_status 
      $this->file_status =  new FileStatus();
      $this->file_status->consecutive = $this->consecutive;
      $this->file_status->archivoid = $fileid;
      $this->file_status->current_status = 'PROCCESING';
      Log::info("Guarda el FileStatus (ARQ - Linea 85)");
      $this->file_status->save();  
      Log::info("Finalizó la creacion del FileStatus (ARQ - Linea 85)");
      //Este booleano verifica la validez del registro de control
      $isValidFirstRow = true;
      
      //En este punto se toma lo que el apuntador leyó de la primera linea para hacer la respectiva validación
      $firstRow = fgetcsv($this->handle, 0, "|");
      
      $this->validateFirstRow($isValidFirstRow, $this->detail_erros, $firstRow);

      // Si en este punto se ha verificado el archivo es válido y que además la primera fila ha
      // pasado las validaciones y sigue siendo una fila válida, entonces se continua la
      // construcción del objeto Archivo que será almacenado
      Log::info("manageContent (ARQ - Linea 105)");
      if ($isValidFirstRow && $isValidFile) {
        Log::info("La primera fila es válida y el archivo es válido (ARQ - Linea 109)");
        //Se asignan los parámetros faltantes del Objeto Archivo
        $this->archivo->fecha_ini_periodo = strtotime($firstRow[2]);
        $this->archivo->fecha_fin_periodo = strtotime($firstRow[3]);
        $entidad = EntidadesSectorSalud::where('cod_habilitacion', $firstRow[0])->first();
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
          Log::info("Empieza las validacionees - (ARQ Linea 129)");
          //Se hace la validación de cada sección del archivo 
          $this->validateEntitySection($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,0,6));
          $this->validateUserSection($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,6,9,true));
          $this->validateUserAddressAndPhone($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,15,2,true));
          $this->validateARQ($isValidRow, $this->detail_erros, $lineCount, $lineCountWF, array_slice($data,17,14,true));

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
            $exists = DB::table('gioss_archivo_arq_cfvl')->where('contenido_registro_validado', utf8_encode(implode('|', $data)))->first();
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
                $tabla = new GiossArchivoArqCfvl();
                $tabla->fecha_periodo_inicio = $this->archivo->fecha_ini_periodo;
                $tabla->fecha_periodo_fin = $this->archivo->fecha_fin_periodo;
                $tabla->nombre_archivo = utf8_encode($this->fileName);
                $tabla->numero_registro = $lineCount;
                $tabla->contenido_registro_validado = utf8_encode(implode('|', $data));
                $tabla->fecha_hora_validacion = time() ;
                $tabla->save();

              // Se busca el usuario que aparece en el registro para confirmar que ya haya sido agregado a la base de datos.
              $exists = UserIp::where('num_identificacion', $data[8])->orderBy('created_at', 'desc')->first();

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

              $cadena_temp=ltrim($data[3], '0');
              $cadena_test=substr($cadena_temp, 0,  strlen($cadena_temp) - 1);

              $eapb  = Eapb::where('num_identificacion', $cadena_test)
                              ->where('cod_eapb', $data[4])->first();
              
              if ($eapb) {

                $exists = Registro::where('id_archivo', $this->archivo->id_archivo_seq)->where('id_user', $useripsid)->where('id_eapb', $eapb->id_entidad)->first();

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
                $exists = Registro::where('id_archivo', $this->archivo->id_archivo_seq)->where('id_user', $useripsid)->first();

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
              $consult = new RegistroQuimioterapia();
              $consult->id_registro = $register->id_registro_seq;
              $consult->cod_diagnostico = $data[17];
              $consult->tipo_tratamiento = $data[19];
              $consult->cod_protocolo = $data[20];
              $consult->frecuencia_ciclo = $data[22];
              $consult->motivo_finalizacion = $data[23];
              $consult->fecha_inicio_tratamiento = strtotime($data[24]);
              $consult->fecha_inicio_aplicacion = strtotime($data[25]);
              $consult->cantidad_aplicaciones = $data[26];
              $consult->frecuencia_aplicacion = $data[27];
              $consult->fecha_aplicacion_indicada = strtotime($data[28]);
              $consult->fecha_aplicacion_real = strtotime($data[29]);
              $consult->fecha_finalizacion = strtotime($data[30]);

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
    Log::error("Termina manageContent (ARQ - Linea 278)");
  }

  private function validateARQ(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF, $consultSection) {
    
    // Se valida el código de diagnóstico (Campo 18)
    Log::info("Inicia validateARQ (ARQ - Linea 284)");
    if (isset($consultSection[17])) {
      if (strlen(trim($consultSection[17])) != 4 || !ctype_alnum(trim($consultSection[17]))) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo debe tener una longitud igual a 4 caracteres y solo debe estar compuesto por letras y números."]);
      } else {
        $exists = DiagnosticoCiex::where('cod_diagnostico', $consultSection[17])->first();
        if (!$exists) {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El valor de este campo no corresponde a un código de diagnóstico válido"]);
        }
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 18, "El campo no puede ser nulo."]);
    }

    // Se valida la descripción del diagnóstico (campo 19)
    if (isset($consultSection[18])) {
      if (strlen(trim($consultSection[18])) > 50 || trim($consultSection[18]) == "") {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo no debe ser vacío y debe tener una longitud menor o igual a cincuenta caracteres"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 19, "El campo no puede ser nulo."]);
    }

    // Se valida el tipo de tratamiento (campo 20)
    if (isset($consultSection[19])) {
      if (!preg_match("/^(([1-14])|([98]))$/", $consultSection[19])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo no debe ser vacío y debe corresponder a un código de tipo de tratamiento válido."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 20, "El campo no puede ser nulo"]);
    }

    // Se valida el codigo de protocolo (campo 21)
    if (isset($consultSection[20])) {
      $exists = ProtocoloQuimioterapia::where('cod_protocolo', $consultSection[20])->first();
      if (!$exists) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 21, "EL valor de código de protocolo dado no corresponde a un valor de código de protocolo válido"]);
      }
    } else {
      $isValidRow = false;
      array_push($consultSection, [$lineCount, $lineCountWF, 21, "El campo no puede ser nulo"]);
    }

    // Se valida la descripción del protocolo (campo 22)
    if (isset($consultSection[21])) {
      if (strlen(trim($consultSection[21])) > 50 || trim($consultSection[21]) == "") {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El campo no debe ser vacío y debe tener una longitud menor o igual a cincuenta caracteres"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 22, "El campo no puede ser nulo."]);
    }

    // Se valida la frecuencia de ciclo (campo 23)
    if (isset($consultSection[22])) {
      if (strlen(trim($consultSection[22])) > 2 || !ctype_digit(trim($consultSection[22]))) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 23, "El campo no debe ser vacío y debe contener un valor numérico de máximo dos caracteres"]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 23, "El campo no puede ser nulo"]);
    }

    // Se valida el Motivo de Finalización (campo 24)
    if (isset($consultSection[23])) {
      if (!preg_match("/^(([1-8])|(10)|(11)|(21)|(22)|(23)|(98)|(99))$/", $consultSection[23])) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 24, "El campo no debe ser vacío y debe contener un valor de motivo de finalización válido."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 24,"El campo no puede ser nulo."]);
    }

    // Se valida laa Fecha de Inicio del Tratamiento (campo 25)
    if(isset($consultSection[24])) {
      if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[24]))
      {
        // Aquí se valida que dicha fecha realmente haya existido.
        $date = explode('-', $consultSection[24]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 25, "El campo debe corresponder a un fecha válida."]);
        }
      }
      else{
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 25, "El campo debe tener el formato AAAA-MM-DD"]);
      }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 25, "El campo no debe ser nulo"]);
    }

    // Se valida laa Fecha de Inicio de Aplicación (campo 26)
    if(isset($consultSection[25])) {
      if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[25]))
      {
        // Aquí se valida que dicha fecha realmente haya existido.
        $date = explode('-', $consultSection[25]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 26, "El campo debe corresponder a un fecha válida."]);
        }
      }
      else{
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 26, "El campo debe tener el formato AAAA-MM-DD"]);
      }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 26, "El campo no debe ser nulo"]);
    }

    // Se valida la Cantidad de Aplicaciones (campo 27)
    if (isset($consultSection[26])) {
      
      if (strlen(trim($consultSection[26])) > 2 || !ctype_digit(trim($consultSection[26]))) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 27, "El campo no debe ser vacío y debe tener un valor numérico de máximo dos caracteres."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 27, "El campo no puede ser nulo."]);
    }

    // Se valida la Frecuencia de Aplicación (campo 28)
    if (isset($consultSection[27])) {
      if (strlen(trim($consultSection[27])) > 2 || !ctype_digit(trim($consultSection[27]))) {
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 28, "El campo no debe ser vacío y debe contener un valor numérico de máximo dos caracteres."]);
      }
    } else {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 28, "El campo no puede ser nulo."]);
    }

    // Se valida la Fecha de Aplicación Indicada (campo 29)
    if(isset($consultSection[28])) {
      if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[28]))
      {
        // Aquí se valida que dicha fecha realmente haya existido.
        $date = explode('-', $consultSection[28]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 29, "El campo debe corresponder a un fecha válida."]);
        }
      }
      else{
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 29, "El campo debe tener el formato AAAA-MM-DD"]);
      }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 29, "El campo no debe ser nulo"]);
    }

    // Se valida la Fecha de Aplicación Real (campo 30)
    if(isset($consultSection[29])) {
      if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[29]))
      {
        // Aquí se valida que dicha fecha realmente haya existido.
        $date = explode('-', $consultSection[29]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 30, "El campo debe corresponder a un fecha válida."]);
        }
      }
      else{
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 30, "El campo debe tener el formato AAAA-MM-DD"]);
      }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 30, "El campo no debe ser nulo"]);
    }

    // Se valida la Fecha de Finalización (campo 31)
    if(isset($consultSection[30])) {
      if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $consultSection[30]))
      {
        // Aquí se valida que dicha fecha realmente haya existido.
        $date = explode('-', $consultSection[30]);
        if(!checkdate($date[1], $date[2], $date[0]))
        {
          $isValidRow = false;
          array_push($detail_erros, [$lineCount, $lineCountWF, 31, "El campo debe corresponder a un fecha válida."]);
        }
      }
      else{
        $isValidRow = false;
        array_push($detail_erros, [$lineCount, $lineCountWF, 31, "El campo debe tener el formato AAAA-MM-DD"]);
      }
        
    }else{
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 31, "El campo no debe ser nulo"]);
    }

    Log::info("Termina validateARQ (ARQ - Linea 500)");
  }

  protected function validateDates(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF,$firstRow ,$data)
  {
    // Se valida que la fecha de nacimiento sea menor que el final del periodo reportado
    Log::info("Inicia validateDates (ARQ - Linea 506)");
    if (strtotime($firstRow[3]) < strtotime($data[13]) ){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha final del periodo reportado  (línea 1, campo 4)"]);
    }

    // Se valida que la fecha de nacimiento sea menor que el inicio del tratamiento
    if(strtotime($data[24]) < strtotime($data[13])){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha de inicio del tratamiento (campo 25)"]);
    }

    // Se valida que la fecha de nacimiento sea menor que el inicio de la aplicación
    if(strtotime($data[25] < strtotime($data[13]))){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha de inicio de la aplicación (campo 26)"]); 
    }

    // Se valida que la fecha de nacimiento sea menor que la fecha de aplicacion indicada
    if(strtotime($data[28] < strtotime($data[13]))){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha de aplicación indicada (campo 29)"]); 
    }

    // Se valida que la fecha de nacimiento sea menor que la fecha de aplicacion real
    if(strtotime($data[29] < strtotime($data[13]))){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha de aplicación real (campo 30)"]); 
    }

    // Se valida que la fecha de nacimiento sea menor que la fecha de aplicacion indicada
    if(strtotime($data[30] < strtotime($data[13]))){
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de nacimiento (campo 14) debe ser inferior a la fecha de finalización (campo 31)"]); 
    }

    // Se valida que la fecha de inicio del tratamiento este por debajo del final del periodo reportado
    if(strtotime($firstRow[3]) < strtotime($data[24])) {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de inicio del tratamiento (campo 25) debe ser inferior a la fecha final del periodo reportado (linea 1 campo 4)"]); 
    }

    // Se valida que la fecha de inicio del tratamiento este por debajo de la fecha de inicio de aplicacion
    if(strtotime($data[25]) < strtotime($data[24])) {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de inicio del tratamiento (campo 25) debe ser inferior o igual a la fecha de inicio de aplicación (campo 26), nunca mayor"]); 
    }

    // Se valida que la fecha de inicio de aplicación este por debajo de la fecha de aplicación indicada
    if (isset($data[28])) {
      if(strtotime($data[28]) < strtotime($data[25])) {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de inicio de la aplicación (campo 26) debe ser inferior o igual a la fecha de aplicación indicada (campo 29), nunca mayor"]);
      }
    }

    // Se valida que la fecha de inicio de aplicación este por debajo de la fecha de aplicación real
    if (isset($data[29])) {
      if(strtotime($data[29]) < strtotime($data[25])) {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha de inicio de la aplicación (campo 26) debe ser inferior o igual a la fecha de aplicación real (campo 30), nunca mayor"]);
      }
    }

    // Se valida que la fecha de aplicación real este por debajo de la fecha de finalización
    if(isset($data[30])){
      if(strtotime($data[30]) < strtotime($data[29])) {
      $isValidRow = false;
      array_push($detail_erros, [$lineCount, $lineCountWF, 14, "La fecha aplicación real (campo 30) debe ser inferior a la fecha de finalización (campo 31)"]); 
      }
    }
    Log::info("Termina validateDates (ARQ - Linea 577)");
  }
}