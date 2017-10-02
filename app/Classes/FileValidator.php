<?php

namespace App\Classes;

use App\Models\EntidadesSectorSalud;
use App\Models\TipoEntidad;
use App\Models\TipoIdentificacionEntidad;
use App\Models\TipoIdentificacionUser;
use App\Models\GenerosUser;
use App\Models\Eapb;
use App\Models\TipoEapb;
use App\Models\TipoIdentEapb;
use App\Models\FileStatus;
use Illuminate\Support\Facades\Log;

class FileValidator {

	protected $handle;
	protected $folder;
	protected $fileName;
	protected $version;
	protected $consecutive;
	protected $detail_erros;
	protected $wrong_rows;
	protected $success_rows;
	protected $file_status;
	protected $archivo;
	protected $totalRegistros;

	//Este método realiza la apertura del archivo y hace el conteo de los registros que contiene dicho archivo
	protected function countLine($filePath){
		$handle = null;
		//Si handle apunta a null el archivo no abrió correctamente
		if(!($handle = fopen($filePath, 'r'))) throw new Exception("Error al abrir el archivo. countline");
		$lineCount = 0;
		//Mientras el apuntador no haya llegado al final del archivo, se obtiene la linea actual y se aumenta el conteo 
		while (!feof($handle)) {
			fgets($handle);
			$lineCount++;
		}
		//Se hace el consolidado total de registros y e cierra el archivo que había sido abierto
		$this->totalRegistros = $lineCount - 1;
		fclose($handle);
	}

	//Esta función se encarga de hacer las validaciones necesarias tras la lectura del archivo en cada uno de los campos encontrados.
	function validateFirstRow(&$isValidRow, &$detail_erros, $firstRow) {
		
		//INICIO DE VALIDACIÓN CAMPO 0

		//Se verifica que el campo 0 no esté vacio o que contenga un número
		if(isset($firstRow[0]) && is_numeric($firstRow[0])){
			//Se realiza la consulta a través del código de habilitación en la Base de Datos de Entidades
			$exists = EntidadesSectorSalud::where('cod_habilitacion', $firstRow[0])->first();
			//Se verifica la existencia de la entidad ingresada en el registro
			if(!$exists){
				//En caso de que la Entidad no exista, es decir, que la consulta no arroje resultados, se marca el registro como no válido y se inserta el error en el arreglo de errores
				$isValidRow = false;
				array_push($detail_erros, [1, 0, 1, "NO existe un  código de habilitación para la entidad"]);
			}
		
		//En caso de que el campo sea NULO, se marca el registro como no válido y se inserta el error en el arreglo de errores	
		}else{
			$isValidRow = false;
			array_push($detail_erros, [1, 0, 1, "Debe ser un valor numérico no nulo"]);
		}

		// FIN VALIDACIÓN CAMPO 0
		
		//INICIO EVALUACIÓN CAMPO 1

		//Se verifica que el campo 1 no esté vacio
		if(isset($firstRow[1])){
			//Se verifica que el campo cumpla con la especificacion de la expresión regular dada -> 1. Un valor de 4 dígitos entre 0000 y 9999 2. Un 0 seguido por un dígito entre 0 y 9 ó un 1 seguido por un dígito entre 0 y 2.  
			if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $firstRow[1])) {
				//Se separa el string leido en la fecha y se almacena en un array 
				$date = explode('-', $firstRow[1]);
				//Se verifica que la fecha sea válida con checkdate() pasando mes, día y año comop parámetros
				if(!checkdate($date[1], 01, $date[0])){
					//Si la fecha dada no existe (i.e un día 31 en un mes de 30 días), se marca el registro como no válido y se inserta el error en el arreglo de errores
					$isValidRow = false;
					array_push($detail_erros, [1, 0, 1, "El campo debe corresponder a una fecha válida."]);
				}
			}
			//Si la cadena obteenida en este campo no corresponde al formato de fecha esperado se marca el registro como no válido y se inserta el error en el arreglo de errores
			else{
				$isValidRow = false;
				array_push($detail_erros, [1, 0, 2, "El campo debe tener el formato AAAA-MM"]);
			}
		//Si el campo es NULO se marca el registro como no válido y se inserta el error en el arreglo de errores
		}else{
			$isValidRow = false;
			array_push($detail_erros, [1, 0, 2, "El campo no debe ser nulo"]);
			
		}

		//FIN VALIDACIÓN CAMPO 1

		//INICIO VALIDACIÓN CAMPO 2

		//Se verifica que el campo 2 no esté vacio
		if(isset($firstRow[2])) {
			//Se verifica que el campo cumpla con la especificacion de la expresión regular dada -> 1. Un valor de 4 dígitos entre 0000 y 9999 2. Un 0 seguido por un dígito entre 0 y 9 ó un 1 seguido por un dígito entre 0 y 2, y 3. Un 0 seguido por un dígito entre 0 y 9 ó un 1 (ó un 2) seguido por un dígito entre 0 y 9 ó un 3 seguido por un dígito entre 0 y 1.
			if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $firstRow[2])){
				//Se separa el string leido en la fecha y se almacena en un array 
				$date = explode('-', $firstRow[2]);
				//Se verifica que la fecha sea válida con checkdate() pasando mes, día y año comop parámetros 
				if(!checkdate($date[1], $date[2], $date[0])){
					//Si la fecha dada no existe (i.e un día 31 en un mes de 30 días), se marca el registro como no válido y se inserta el error en el arreglo de errores
					$isValidRow = false;
					array_push($detail_erros, [1, 0, 3, "El campo debe corresponder a un fecha válida."]);
				}
			}
			//Si la cadena obteenida en este campo no corresponde al formato de fecha esperado se marca el registro como no válido y se inserta el error en el arreglo de errores
			else
			{
				$isValidRow = false;
				array_push($detail_erros, [1, 0, 3, "El campo debe tener el formato AAAA-MM-DD"]);
			}
		//Si el campo es NULO se marca el registro como no válido y se inserta el error en el arreglo de errores
		}else{
			$isValidRow = false;
			array_push($detail_erros, [1, 0, 3, "El campo no debe ser nulo"]);
		}

		//FIN VALIDACIÓN CAMPO 2

		//INICIO VALIDACIÓN CAMPO 3

		//Se verifica que el campo no sea NULO
		if(isset($firstRow[3])){
			//Se verifica de nuevo que se cumpla con el formato de fecha requerido 
			if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $firstRow[3])){	
				//De nuevo se separa la cadena por guiones 
				$date = explode('-', $firstRow[3]);
				//De nuevo se verifica que la fecha sea una fecha encontrada
				if(!checkdate($date[1], $date[2], $date[0])){	
					//Si no es una fecha válida se marca el registro como no válido y se inserta el error a la cadena de errores 
					$isValidRow = false;
					array_push($detail_erros, [1, 0, 4, "El campo debe corresponder a un fecha válida."]);
				}
			//Si la fecha no cumple con el formato se marca el registro como no válido y se inserta el error a la cadena de errores
			} else {
				$isValidRow = false;
				array_push($detail_erros, [1, 0, 4, "El campo debe tener el formato AAAA-MM-DD"]);
			}
		//Si el campo es NULO se marca el registro como no válido y se inserta el error a la cadena de errores 
		} else {
			$isValidRow = false;
			array_push($detail_erros, [1, 0, 4, "El campo no debe ser nulo"]);
		}

		//FIN VALIDACIÓN CAMPO 3

		//INICIO VALIDACIÓN DE COHERENCIA ENTRE FECHAS

		//Tras estas validaciones de fecha se verifica que en este punto el registro siga siendo válido 
		if ($isValidRow) {
			//Si el registro sigue siendo válido se verifica que el periodo inicial sea menor que el periodo final.
			if (strtotime($firstRow[2]) > strtotime($firstRow[3]) ){
				//Si esto no se cumple se marca el registro como no válido y se inserta el error en  el arreglo de errores
				$isValidRow = false;
				array_push($detail_erros, [1, 0, '3 y 4', "El periodo incial debe ser menor que el periodo final"]);
			}
		}

		//FIN VALIDACIÓN DE COHERENCIA ENTRE FECHAS
		
		//INICIO VALIDACIÓN CAMPO 4

		//Se verifica que el campo 4 no sea NULO
		if(!isset($firstRow[4]) || !is_numeric($firstRow[4])){
			//Si este campo es NULO o NO ES NUMÉRICO se marca como no válido este registro y se inserta el error en el arreglo de errores
			$isValidRow = false;
			array_push($detail_erros, [1, 0, 5, "Debe ser un valor numérico no nulo"]);
			//Sino se verifica que el número de registros reportados corresponda con el número de registros encontrados
		}elseif (($this->totalRegistros - 1) != intval($firstRow[4])) {
			//Si esto no se cumple se marca el registro como inválido y se inserta el error en el arreglo de errores
			$isValidRow = false;
			array_push($detail_erros, [1, 0, 5, "El valor no coincide con el número de registros del archivo actual: No. registros encontrados = ".($this->totalRegistros - 1)." - valor del campo = ".intval($firstRow[4])]);
		}

	}

    function validateEntitySection(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF, $entitySection) {
    	
    	//validacion campo 1
    	if(isset($entitySection[0]) && preg_match('/^\d{12}$/', $entitySection[0])){
			$exists = EntidadesSectorSalud::where('cod_habilitacion', $entitySection[0])->first();
			if(!$exists){
				$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 1, "El valor del campo no corresponde a un código de entidad registrado"]);
			}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 1, "El campo debe ser valor numérico de 12 dígitos"]);
		}

		//validacion campo 2
    	if(isset($entitySection[1])){
    		if(strlen(trim($entitySection[1])) == 1){
    			$tipo = TipoEapb::where('id_tipo_ent',$entitySection[1])->first();
    			if(!$tipo){
    				$isValidRow = false;
					array_push($detail_erros, [$lineCount, $lineCountWF, 2, "El  valor del campo no corresponde a un código de tipo entidad"]);
    			}
    		}else{
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 2, "El campo debe tener un longitud igual a 1"]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 2, "El campo no debe ser nulo"]);
		}


		//validacion campo 3
    	if(isset($entitySection[2])){
    		if(strlen(trim($entitySection[2])) == 2){
    			
    			$tipo_ident = TipoIdentEapb::where('id_tipo_ident', $entitySection[2])->first();
    			if(!$tipo_ident){
    				$isValidRow = false;
					array_push($detail_erros, [$lineCount, $lineCountWF, 3, "El valor del campo no corresponde a un código de tipo identificacion entidad"]);
    			}
    		}else{
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 3, "El campo debe tener una longitud igual a 2"]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 3, "El campo no debe ser nulo"]);
		}

		//validacion campo 4
    	if(isset($entitySection[3])){
    		if(preg_match('/^\d{12}$/', $entitySection[3])){
    			//Sin truncar el último dígito
    			$tipo = Eapb::where('num_identificacion', ltrim($entitySection[3], '0'))->first();
    			//Truncando el último dígito
    			$cadena_temp=ltrim($entitySection[3], '0');
    			$cadena_test=substr($cadena_temp, 0,  strlen($cadena_temp) - 1);
    			$tipo2 = Eapb::where('num_identificacion', $cadena_test)->first();
    			
    			if(!$tipo && !$tipo2){
    				$isValidRow = false;
					array_push($detail_erros, [$lineCount, $lineCountWF, 4, "El  valor del campo no corresponde a un número de identificación de entidad registrado "]);
    			} 
    		}else{
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 4, "El campo debe ser un valor numérico igual de 12 dígitos"]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 4, "El campo no debe ser nulo"]);
		}		//validacion campo 5
    	if(isset($entitySection[4])){
    		if(strlen(trim($entitySection[4])) <= 6){
    			$tipo = Eapb::where('cod_eapb',$entitySection[4])->first();
    			if(!$tipo){
    				$isValidRow = false;
					array_push($detail_erros, [$lineCount, $lineCountWF, 5, "El  valor del campo no corresponde a un código de EAPB válido"]);
    			}
    		}else{
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 5, "El campo debe tener un longitud menor o igual a 6"]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 5, "El campo no debe ser nulo"]);
		}

		//validacion campo 6
    	if(isset($entitySection[5]) ) {
    		if(strlen(trim($entitySection[5])) > 100 || trim($entitySection[5]) == ''){
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 6, "El campo debe tener un longitud menor o igual a 100 caracteres y no debe ser vacio. ".$entitySection[5]]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 6, "El campo no debe ser nulo"]);
		}

    }

    function validateUserSection(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF, $userSection){

    	//validación campo 7
    	if(isset($userSection[6])){
    		if(strlen(trim($userSection[6])) > 12){
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 7, "El campo debe tener un longitud menor o igual a 12 caracteres "]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 7, "El campo no debe ser nulo"]);
		}

		//validación campo 8
    	if(isset($userSection[7])) {
    		if(strlen(trim($userSection[7])) == 2){
    			$tipo_ident = TipoIdentificacionUser::where('id_tipo_ident', $userSection[7])->first();
    			if(!$tipo_ident){
    				$isValidRow = false;
					array_push($detail_erros, [$lineCount, $lineCountWF, 8, "tipo de identificación no valido"]);
    			}
    			
    		}else{
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 8, "El campo debe tener un longitud igual a 2"]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 8, "El campo no debe ser nulo"]);
		}

		//validación campo 9
    	if(isset($userSection[8])) {
    		if(strlen(trim($userSection[8])) > 12 || !ctype_digit($userSection[8])) {
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 9, "El campo debe ser un valor numérico con una longitud menor o igual a 12 dígitos."]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 9, "El campo no debe ser nulo"]);
		}
		
		//validación campo 10
    	if(isset($userSection[9])) {
    		if(strlen(trim($userSection[9])) > 30 || trim($userSection[9]) == '' ){
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 10, "El campo no debe ser vacío y debe tener un longitud menor o igual a 30 caracteres."]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 10, "El campo no debe ser nulo"]);
		}

		//validación campo 11
    	if(isset($userSection[10])) {
    		if(strlen($userSection[10]) > 30){
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 11, "El campo debe tener un longitud menor a 30 caracteres."]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 11, "El campo no debe ser nulo"]);
		}

		//validación campo 12
    	if(isset($userSection[11]) ) {
    		if(strlen(trim($userSection[11])) > 30 || trim($userSection[11]) == '' ){
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 12, "El campo debe tener un longitud menor o igual a 30 caracteres."]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 12, "El campo no debe ser nulo"]);
		}

		//validación campo 13
    	if(isset($userSection[12])) {
    		if(strlen(trim($userSection[12])) > 30){
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 13, "El campo debe tener un longitud menor o igual a 30 caracteres."]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 13, "El campo no debe ser nulo"]);
		}

		//validación campo 14
    	if(isset($userSection[13])) {
    		if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $userSection[13])){
    			$date = explode('-', $userSection[13]);
				if(!checkdate($date[1], $date[2], $date[0]))
				{
					$isValidRow = false;
					array_push($detail_erros, [$lineCount, $lineCountWF, 14, "El campo debe corresponder a una fecha válida."]);
				}	
    		}
    		else{
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 14, "El campo debe tener el formato AAAA-MM-DD"]);
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 14, "El campo no debe ser nulo"]);
		}

		//validación campo 15
    	if(isset($userSection[14])) {
    		if(strlen(trim($userSection[14])) != 1 ){
    			$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 15, "El campo debe tener un longitud igual a 1 caracteres."]);
    		}else{
    			$exists = GenerosUser::where('id_genero',$userSection[14])->first();
    			if(!$exists){
    				$isValidRow = false;
					array_push($detail_erros, [$lineCount, $lineCountWF, 15, "El valor del campo no correponde a un género definido."]);
    			}
    		}
		}else{
			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 15, "El campo no debe ser nulo"]);
		}

    }

    function validateUserAddressAndPhone(&$isValidRow, &$detail_erros, $lineCount, $lineCountWF, $userSection){

    	// Validacion campo 16 - DIRECCIÓN DEL USUARIO

    	if(!isset($userSection[15]) || (strlen(trim($userSection[15])) > 50)) {
    		$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 16, "El campo no puede ser nulo ni puede tener una longitud mayor a 50 caracteres"]);
		}

		// Validacion campo 17 - TELEFONO DEL USUARIO

		if(isset($userSection[16])) {
    		
    		$phoneNumbers = explode("-", $userSection[16]);
			if(count($phoneNumbers) > 2){
				$isValidRow = false;
				array_push($detail_erros, [$lineCount, $lineCountWF, 17, "Solo pueden registrarse dos números telefónicos en el campo"]);
			  
			} else {
				if (count($phoneNumbers) == 2) {
					
					if(!ctype_digit($phoneNumbers[0]) || !ctype_digit($phoneNumbers[1])){
						$isValidRow = false;
						array_push($detail_erros, [$lineCount, $lineCountWF, 17, "Los números telefónicos deben estar compuestos en su totalidad de dígitos numéricos"]);

					} else {
						if((strlen($phoneNumbers[0]) > 10) || (strlen($phoneNumbers[1]) > 10)){

							$isValidRow = false;
							array_push($detail_erros, [$lineCount, $lineCountWF, 17, "Los números telefónicos deben ser de máximo 10 dígitos"]);

						}

					}

				} else {
					
					if(!ctype_digit($phoneNumbers[0])){
						$isValidRow = false;
						array_push($detail_erros, [$lineCount, $lineCountWF, 17, "Los números telefónicos deben estar compuestos en su totalidad de dígitos numéricos"]);

					} else {
						if((strlen($phoneNumbers[0]) > 10)){

							$isValidRow = false;
							array_push($detail_erros, [$lineCount, $lineCountWF, 17, "Los números telefónicos deben ser de máximo 10 dígitos"]);

						}

					}

				}

			}

			Log::info("Sale del if de telefonos");

		} else {

			$isValidRow = false;
			array_push($detail_erros, [$lineCount, $lineCountWF, 17, "El campo no debe ser nulo"]);

		}
    	
    }
    
    function createZip($patchFolder,$patchStorageZip){
	    // Get real path for our folder
	    $rootPath = realpath($patchFolder);
	    
	    // Initialize archive object
	    $zip = new \ZipArchive();
	    $zip->open($patchStorageZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
	    
	    // Create recursive directory iterator
	    /** @var SplFileInfo[] $files */
	    $files = new \RecursiveIteratorIterator(
	        new \RecursiveDirectoryIterator($rootPath),
	        \RecursiveIteratorIterator::LEAVES_ONLY
	    );
	    
	    foreach ($files as $name => $file)
	    {
	        // Skip directories (they would be added automatically)
	        if (!$file->isDir())
	        {
	            // Get real and relative path for current file
	            $filePath = $file->getRealPath();
	            $relativePath = substr($filePath, strlen($rootPath) + 1);
	    
	            // Add current file to archive
	            $zip->addFile($filePath, $relativePath);
	        }
	    }
	    
	    // Zip archive will be created only after closing object
	    $zip->close();
	}

	public function dropWhiteSpace(&$array)
	{
		foreach ($array as $key => $field) {
			$array[$key] = trim($field);
		}

	}

	protected function updateStatusFile($lineCount)
	{
		$line = $lineCount;
		//se actualiza el porcentaje
		$register_num = $this->archivo->numero_registros;
		$Porcent =  $this->file_status->porcent;
		$currentPorcent = 0;
		
		try{
			$currentPorcent = ( ($lineCount - 1)  / ($register_num))*100;
		}catch(\Exception $e)
		{
			Log::info("Excepcion -> ".print_r($e->getMessage(), true));
		}

		$dif = $currentPorcent -  $Porcent;
		if ($dif >= 1){
		  $this->file_status->current_line = $line;
		  $this->file_status->porcent = intval($currentPorcent);
		  $this->file_status->save();
		}
		return true;
	}

	protected function generateFiles() 
	{

		if(count($this->wrong_rows) > 0){
		  
		  $filewrongname = $this->folder.'RegistrosErroneos.txt';
		  //dd('entro');
		  $wrongfile = fopen($filewrongname, 'w');                              
		  fprintf($wrongfile, chr(0xEF).chr(0xBB).chr(0xBF)); // darle formato unicode utf-8
		  foreach ($this->wrong_rows as $row) {
		      fputcsv($wrongfile, $row,'|');              
		  }
		  fclose($wrongfile);
		  
		  
		}

		if(count($this->detail_erros) > 1){
		  //----se genera el archivo de detalles de error
		  $detailsFilename =  $this->folder.'DetallesErrores.txt';
		  
		  $detailsFileHandler = fopen($detailsFilename, 'w');
		  fprintf($detailsFileHandler, chr(0xEF).chr(0xBB).chr(0xBF)); // darle formato unicode utf-8
		  foreach ($this->detail_erros as $row) {
		      fputcsv($detailsFileHandler, $row,',');              
		  }
		  fclose($detailsFileHandler);
		}

		if(count($this->success_rows) > 0){
		    $arrayIdsFilename = $this->folder.'RegistrosExitosos.txt';
		    
		    $arrayIdsFileHandler = fopen($arrayIdsFilename, 'w');
		    fprintf($arrayIdsFileHandler, chr(0xEF).chr(0xBB).chr(0xBF)); // darle formato unicode utf-8
		    foreach ($this->success_rows as $row) {
		        fputcsv($arrayIdsFileHandler, $row, '|');              
		    }
		    fclose($arrayIdsFileHandler);
		    
		    if(count($this->wrong_rows) > 0){
		      $this->file_status->final_status = 'REGULAR';
		      
		    }else{
		      $this->file_status->final_status = 'SUCCESS';
		    }
		    
		    $zipname = 'detalles'.time().'.zip';
		    $zipsavePath = storage_path('archivos').'/../../public/zips/'.$zipname;
		    $this->createZip($this->folder, $zipsavePath);
		    
		    $this->file_status->zipath = asset('zips/'.$zipname);
		    $this->file_status->current_status = 'COMPLETED';
		    $this->file_status->save();

		    return true;
		    
		}else{
		    
		    $this->file_status->final_status = 'FAILURE';

		    
		    $zipname = 'detalles'.time().'.zip';
		    $zipsavePath = storage_path('archivos').'/../../public/zips/'.$zipname;
		    //dd($zipsavePath);
		    $this->createZip($this->folder, $zipsavePath);
		    
		    $this->file_status->zipath = asset('zips/'.$zipname);
		    $this->file_status->current_status = 'COMPLETED';
		    $this->file_status->save();

		    return true;
		}
	}//fin function


}