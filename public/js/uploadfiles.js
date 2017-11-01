var consecutive = Date.now();
var count_files = 0;
var interval_load = null;

$(document).ready(function(){
	
	var interval = null;

	$('#add_file').on('click', function(){
		clearInterval(interval_load);
		$('#add_file').prop('disabled', true);
		$('#error_area').empty();
		$('#alert').empty();

		if (count_files != 0) {
			count_files = 0;
		}

		count_files+=1;

		$('#files_div').empty();
		var html = '<div class="form-group well" id="particular_file_div"> <button type="button" id="close_div_file" class="close" aria-hidden="true">&times;</button> <label for="tipo_file" class="form-control-label" style="font-family: \'Jura\', sans-serif; font-size: 15px;"><strong>Tipo de archivo No.'+count_files+'</strong></label><select id="tipo_file" name="tipo_file[]" style="width: 80%;position:relative;font-family: \'Jura\', sans-serif; font-size: 16px;"><option value="AAC" style="font-family: \'Jura\', sans-serif; font-size: 16px;">Archivo Atencion en Consulta AAC</option> <option value="AEH" style="font-family: \'Jura\', sans-serif; font-size: 16px;">Archivo Egresos Hospitalarios AEH</option><option value="ASM" style="font-family: \'Jura\', sans-serif; font-size: 16px;">Archivo Suministro Medicamentos ASM</option><option value="AVA" style="font-family: \'Jura\', sans-serif; font-size: 16px;">Archivo Vacunas Aplicadas AVA</option><option value="APS" style="font-family: \'Jura\', sans-serif; font-size: 16px;">Archivo Procedimientos APS</option><option value="ATP" style="font-family: \'Jura\', sans-serif; font-size: 16px;">Archivo Peso y Tensión ATP</option><option value="RAD" style="font-family: \'Jura\', sans-serif; font-size: 16px;">Archivo Registro Ayudas Diagnosticas RAD</option><option value="ARQ" style="font-family: \'Jura\', sans-serif; font-size: 16px;">Archivo Registro de Quimioterapia ARQ</option><option value="ARC" style="font-family: \'Jura\', sans-serif; font-size: 16px;">Archivo Registro de Cancer ARC</option></select><div><input type="file" name="archivo[]" id="archivo"  accept=".txt" style="width:80%;position:relative;font-family: \'Jura\', sans-serif; font-size: 16px;"></div></div>';
		$('#files_div').append(html);
		$('#files_div').fadeIn();

    	$('#btnUpload').prop('disabled', false);
    	$('#btnUpload').removeClass("col-md-3 btn btn-info btn-m disabled");
    	$('#btnUpload').addClass("col-md-3 col-md-offset-6 btn btn-info btn-m");
    	
    	console.log('Contenido del select del tipo de archivo '+$('#tipo_file').val());
    	console.log('Contenido del select del tipo de archivo '+$('#archivo').val());
	});

	$('#files_div').on('click','#close_div_file', function(){
		$('#alert').fadeOut();
		$('#add_file').prop('disabled', false);
		count_files-=1;
		$(this).parent().fadeOut(function(){
			if (count_files <= 0) {
				$('#btnUpload').prop('disabled', true);
    			$('#btnUpload').addClass("col-md-3 col-md-offset-6 btn btn-info btn-m disabled");
			}
			$(this).remove();
		});
		
	});

	$('#btnUpload').on('click', function(){
		clearInterval(interval_load);
		$('#error_area').empty();
		$('#btnUpload').prop('disabled', true);
		$('#divgif').html(loadGif);
		
		count_files = 0;
		if($('#archivo').val() == '') {
			var detalle = '<hr><hr style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong>Ningún archivo ha sido seleccionado!</strong>';
			$('#error_area').append(detalle);
			$('#alert').fadeOut();
			$('#alert').fadeIn();
			$('#add_file').fadeOut();
			$('#btnUpload').fadeOut();
			$('#add_file').fadeIn();
			$('#btnUpload').fadeIn();

		} else {
			$('#files_div').fadeOut();
			$('#div_file_statuses').empty();
			var validatorNames = validateNameFiles();
			var validatorPeriodo = validatePeriodo();
			consecutive = Date.now();

			if(!validatorNames['isValid'] || !validatorPeriodo.isValid){
				if(!validatorNames['isValid']){
					$('#add_file').prop('disabled', false);
					$('#error_area').append(validatorNames['detalle']);
					$('#divgif').empty();
				}
				
				if( !validatorPeriodo.isValid){
					$('#add_file').prop('disabled', false);
					$('#error_area').append(validatorPeriodo['detalle']);
					$('#divgif').empty();
				}
				$('#alert').fadeIn();
				$('#add_file').fadeIn();
				$('#btnUpload').fadeIn();

			}else{
				count_files = 1;
				$('#alert').fadeOut();
				$('#error_area').empty();
				$('#div_file_statuses').empty();
				interval_consecutive = setInterval(function(){
					consultStatusFiles(consecutive);
				}, 5000);
				funcionPrincipal = setTimeout( function(){uploadFile();}, 0);
			}

		}
		
	});
});

function validatePeriodo(){

	var isValid = true;
	var detalle = '<hr><hr style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong> Error en el periodo a reportar </strong><br>';
	var startDt=$('#fecha_ini').val();
	var endDt=$('#fecha_fin').val();
	
	if(startDt == "" ){
		 isValid = false;
		detalle += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- Por favor selecciona una fecha inicio de periodo valida.</p>';
	}

	if(endDt == "" ){
		isValid = false;
		 detalle += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- Por favor selecciona una fecha de fin de periodo valida.</p>';
	}

	if( (new Date(startDt).getTime() > new Date(endDt).getTime()))
	{
	    var isValid = false;
		 detalle += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- El periodo incial debe ser menor al final</p>';
	}

	return {isValid:isValid, detalle:detalle};
}

function validateNameFiles(){

	var isValid = true;
	var detalle = '<hr><hr style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong>Error en el formato del nombre de un archivo</strong>';

	if($('#files_div #particular_file_div')){
		$('#files_div #particular_file_div').each(function(i, item){
			var type_file = $(this).find('#tipo_file').val();

			var label = $(this).find('label').text();

			var file_path = $(this).find('#archivo').val();
			console.log('Contenido del select del tipo de archivo ' + $(this).find('#tipo_file').val());

			var file_array_split = file_path.split("\\");
			var file_name_array =  file_array_split[file_array_split.length -1];

			var file_name = file_name_array.split(".")[0];
			var file_ext = file_name_array.split(".")[1];

			var modulo  = file_name.substring(0,3);
			var tipo_fuente = file_name.substring(3,6);
			var tema = file_name.substring(6,9);
			var MesRepor = file_name.substring(9,15);
			var fecha_ini = file_name.substring(15,23);
			var fecha_fin = file_name.substring(23,31);
			var tipo_identificaion = file_name.substring(31,34);
			var id_entidad = file_name.substring(34,46);
			var cod_habilitacion = file_name.substring(46,58);

			//validaciones

			var mnj = '';

			if(file_ext |= 'txt'){
				isValid = false;
				mnj += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- La extensión del archivo debe ser txt</p>';
			}

			if(modulo != 'SGD') {
				isValid = false;
				mnj += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- La sección del modulo no correponde al al modulo SGD</p>';
			}

			if(!$.isNumeric(tipo_fuente)){
				isValid = false;
				mnj += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- La sección del tipo de fuente debe ser numérico</p>';
			}

			if(tema != type_file ){
				isValid = false;
				mnj += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- La sección del tipo de archivo no coincide con el tipo seleccionado</p>';
			}

			if(!MesRepor.match(/^(19|20)\d\d(0[1-9]|1[012])$/)){
				isValid = false;
				mnj += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- La sección del del mes reportado no coincide con el formato de fecha YYYYMM</p>';
			}
			
			if(!fecha_ini.match(/^(19|20)\d\d(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])$/)){
				isValid = false;
				mnj += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- La sección del inicio de periodo no corresponde al formato de fecha YYYYMMDD</p>';
			}

			if(!fecha_fin.match(/^(19|20)\d\d(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])$/)){
				isValid = false;
				mnj += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- La sección del fin de periodo no corresponde al formato de fecha YYYYMMDD</p>';
			}

			if(typeof tipo_identificaion != "string" || tipo_identificaion.length != 3 ){
				isValid = false;
				mnj += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- La seccion del tipo de indentificacion debe ser un caracter de tamaño 3</p>';
			}

			var dateini = fecha_ini.substring(0,4)+'-'+fecha_ini.substring(4,6)+'-'+fecha_ini.substring(6);
			var datefin = fecha_fin.substring(0,4)+'-'+fecha_fin.substring(4,6)+'-'+fecha_fin.substring(6);
			var mesr = MesRepor.substring(0,4)+'-'+MesRepor.substring(4,6)+'-01';

			var startDt=$('#fecha_ini').val();
			var endDt=$('#fecha_fin').val();

			if(startDt != dateini)
			{
			     isValid = false;
				 mnj += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- El periodo incial debe ser equivalente al periodo inicial de la sección nombre del archivo reportado</p>';
			}

			if(endDt != datefin)
			{
			    isValid = false;
				 mnj += '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- El periodo final debe ser equivalente con el periodo final de la sección nombre del archivo reportado</p></p>';
			}

			if(!isValid){
				mnj = '<hr style="font-family: \'Jura\', sans-serif; font-size: 16px;">Error en el archivo <strong>'+ label+'</strong>:<br>'+mnj;
			}

			detalle+=mnj;

		});

	}

	return {'isValid': isValid, 'detalle':detalle};

}

//especificar none si el resultado de la peticion ajax no sera contenida en un div
//es asincrona, no pone warning
function ConsultaAJAX_Async(parametros,filePHP,divContent)
{
	var xmlhttp;
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	if(divContent!="none")
	{
	    xmlhttp.onreadystatechange=function()
	    {
			if (xmlhttp.readyState==4 && xmlhttp.status==200)
			{
				if(document.getElementById(divContent))
				{
			    	document.getElementById(divContent).innerHTML=xmlhttp.responseText;
			    }//if existe
			}
	    }
	    
	    xmlhttp.open("GET",filePHP+"?"+parametros+"&campodiv="+divContent,true);
	    xmlhttp.send();
		
	}
	else
	{
	    xmlhttp.onreadystatechange=function()
	    {
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
		    //alert(xmlhttp.responseText);
		    console.log("Respuesta AJAX: "+xmlhttp.responseText);
		    return xmlhttp.responseText;
		}
	    }
	    
	    xmlhttp.open("GET",filePHP+"?"+parametros,true);
	    xmlhttp.send();
		
	}

}//fin funcion consulta ajax

async function uploadFile() 
{
	//console.log("Ejecuta Upload File");

	var formData = new FormData($('#cargaArchivos')[0]);
	formData.append('consecutive', consecutive);
	update_table(consecutive);

	$.ajax({
	    url: route,
	    data: formData,
	    type: 'POST',
	    dataType: 'json',
	    cache: false,
	    async: true,
	    // parametros necesarios para la carga de archivos
	    contentType: false,
	    processData: false,
	    beforeSend: function() {

	    },
	    error: function (msj) {
	        console.log(msj);
	    }
	    
	});
}

function LeerArchivoPlanoDeServidor(ruta, elemento_div)
{
	var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() 
    {
        if (xhr.readyState == 4 && xhr.status == 200) 
        {
        	var resultado=""+xhr.responseText;
        	var arrayResultado = resultado.split(",");
        	console.log(arrayResultado[6]);
        	var res_definitivo= arrayResultado[6].replace(/[\"]/g, "");
            document.getElementById(elemento_div).innerHTML = "- "+res_definitivo;
        }//fin if
    }//fin function
    xhr.open('GET', ruta);
    xhr.send();
}//fin function

function sleep(ms){
	return new Promise(resolve => setTimeout(resolve, ms));
}

function validateFirstRow(folderPath, tipoArchivo){

	var isValid = true;
	var archivoFallido = '<p style="font-family: \'Jura\', sans-serif; font-size: 16px;">- Error en el archivo '+ tipoArchivo +' </p>';

	LeerArchivoPlanoDeServidor(folderPath, 'error_area');

	$('#alert').fadeIn();
	$('#add_file').fadeIn();
	$('#btnUpload').fadeIn();

}

function load_table(){

	$('#home').removeClass('show');

	$.ajax({

        url: status_file_route,
        data: {'consecutive':consecutive},
        type: 'GET',
        dataType: 'json',
        cache: false,
        async: true,
        beforeSend: function() {
        
        },
        success: function (msj) {
        	
        	var id = '';
        	var finish = false;
        	var counter = 0;

            for (x in msj)
            {
            	var registro_leido = '';
            	id = msj[x].id_tema_informacion + msj[x].consecutive;

            	if(msj[x].current_status != 'COMPLETED'){
            		document.getElementById('loading_table_img').style.display = 'inline';
            		registro_leido = '<tr id="' + id + '"> <td style="text-align: center; max-width: 150px; overflow-x: scroll; font-family: \'Jura\', sans-serif; font-size: 15px\'">' + msj[x].nombre + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px">' + msj[x].id_tema_informacion + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px">' + msj[x].total_registers + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px">' + msj[x].porcent + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px">' + msj[x].current_status + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px">' + msj[x].final_status + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px"> <b>PROCESANDO </td> </tr>';
            		load_table();
            	} else {
            		registro_leido = '<tr id="' + id + '"> <td style="text-align: center; max-width: 150px; overflow-x: scroll; font-family: \'Jura\', sans-serif; font-size: 15px\'">' + msj[x].nombre + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px">' + msj[x].id_tema_informacion + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px">' + msj[x].total_registers + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px">' + msj[x].porcent + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px">' + msj[x].current_status + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px">' + msj[x].final_status + '</td> <td style="text-align: center; font-family: \'Jura\', sans-serif; font-size: 15px"><a href="' + msj[x].zipath + '"> <b>DESCARGAR </a></td> </tr>';
            	}

        		console.log('EL ID DEL COMPONENTES ES: ' + id);
        		
        		if (document.getElementById(id) && msj[x].current_status != 'COMPLETED') {
        			document.getElementById(id).innerHTML = registro_leido;
        		} else if (!document.getElementById(id) && msj[x].current_status != 'COMPLETED') {
        			$('#loaded_files').append(registro_leido);
        		} else if (!document.getElementById(id) && msj[x].current_status == 'COMPLETED') {
        			counter++;
        			$('#loaded_files').append(registro_leido);
        		} else if (document.getElementById(id) && msj[x].current_status == 'COMPLETED') {
        			counter++;
        			document.getElementById(id).innerHTML = registro_leido;
        		}
				
        	}

        	if(counter == msj.length){
        		document.getElementById('loading_table_img').style.display = 'none';
        	}

		}
	});

}

function update_table(consecutive){

	interval_load = setInterval(load_table(consecutive), 500);

}

window.onload = function(){
	
	 load_table();
} 

function stop_load_table(){
	
	$('#home').removeClass('show');
	clearInterval(interval_load);

}


async function consultStatusFiles(consecutive) {	
	
	var bool_fallo = false;
	update_table(consecutive);
	
	$.ajax({

        url: status_file_route,
        data: {'consecutive':consecutive},
        type: 'GET',
        dataType: 'json',
        cache: false,
        async: true,
        beforeSend: function() {
        //console.log("Antes de ajax del consultStatusFiles ");
           
        },
        success : function (msj) {
        	//console.log("Entra al SUCCESS");
        	//console.log("Longitud msj: " + msj.length);
        	$('#add_file').prop('disabled', false);
            $('#div_file_statuses').empty();
            update_table(consecutive);
            //$('#div_file_statuses').append('')
            
            var finish = false;

            for (x in msj)
            {
        		
        		// SECCIÓN DIV DE ESTADO
        		document.getElementById('loading_table_img').style.display = 'inline';
            
            	if (msj[x].consecutive == consecutive)
            	{

	            	if (msj[x].current_status == 'COMPLETED')
	            	{
	            		//$('#div_file_statuses').empty();
	            		finish = true;
	            		clearInterval(interval_load);
	            	}
	            	else
	            	{
	            		finish = false;
	            	}//fon else

	            	var html = '<div class="form-group well "> <h4 style="font-family: \'Jura\', sans-serif; font-size: 16px;"> Estado de archivos: </h4> <div class="row"> <label class="col-md-4" style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong>Nombre:</strong></label> <label class="col-md-8" style="display: inline-block; width: 300px; overflow: hidden; text-overflow: ellipsis; font-family: \'Jura\', sans-serif; font-size: 16px;">'+msj[x].nombre+'0'+msj[x].version+'.txt</label></div> <div class="row"> <label class="col-md-4" style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong>Estado:</strong></label> <label class="col-md-8" style="font-family: \'Jura\', sans-serif; font-size: 16px;">'+msj[x].current_status+'</label></div>';

	            	if (msj[x].current_line <= 0) {

	            		html += '<div class="row"> <label class="col-md-4" style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong> % Cargado: </strong></label> <label class="col-md-8" style="font-family: \'Jura\', sans-serif; font-size: 16px;">'+msj[x].porcent+'% => ' + msj[x].current_line+'/'+msj[x].total_registers+'</label></div>';

	            	} else {

	            		html += '<div class="row"> <label class="col-md-4" style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong> % Cargado: </strong></label> <label class="col-md-8" style="font-family: \'Jura\', sans-serif; font-size: 16px;">'+msj[x].porcent+'% => ' + (msj[x].current_line - 1)+'/'+msj[x].total_registers+'</label></div>';

	            	}

	            	if(msj[x].current_status == 'COMPLETED')
	            	{
	            		//$('#div_file_statuses').empty();
	            		switch(msj[x].final_status)
	            		{
		            		case 'REGULAR':
		            			bool_fallo = false;
		            			html+= '<div class="row"> <label class="col-md-4" style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong> Cal. Global: </strong></label> <label class="col-md-8" style="font-family: \'Jura\', sans-serif; font-size: 16px;">REGULAR</label></div>';
		            			//$('#alert').empty();
			        			//$('#error_area').empty();
		            			break;
		            		case 'SUCCESS':
		            			//$('#alert').empty();
	            				//$('#error_area').empty();
		            			bool_fallo = false;
		            			html+= '<div class="row"> <label class="col-md-4" style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong> Cal. Global: </strong></label> <label class="col-md-8" style="font-family: \'Jura\', sans-serif; font-size: 16px;">EXITOSO</label></div>';
		            			//$('#alert').empty();
			        			//$('#error_area').empty();
		            			break;
		            		case 'FAILURE':
		            			if(msj[x].current_line==0)
		            			{
		            				bool_fallo = true;
		            				html+= '<div class="row"> <label class="col-md-4" style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong> Cal. Global: </strong></label> <label class="col-md-8" style="font-family: \'Jura\', sans-serif; font-size: 16px;">FALLO EN LA PRIMERA LINEA</label></div>';
		            			
		            			}
			            		else
			            		{
			            			bool_fallo = false;
			            			html+= '<div class="row"> <label class="col-md-4" style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong> Cal. Global: </strong></label> <label class="col-md-8" style="font-family: \'Jura\', sans-serif; font-size: 16px;">FALLIDO</label></div>';
		            			
			            		}
		            			break;
		            	}

		            	html+= '<div class="row"> <label class="col-md-4" style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong> Detalle: </strong></label> <a href="'+msj[x].zipath+'" class="col-md-8" style="font-family: \'Jura\', sans-serif; font-size: 16px;"><strong>Descargar</strong></a></div>';
		            	
		            	if (msj[x].current_status == 'COMPLETED' && bool_fallo) 
		            	{
		            		var array_ruta = msj[x].zipath.split('/');
		            		var nombre_comprimido = array_ruta[array_ruta.length - 1];
		            		var consecutivo_comprimido = nombre_comprimido.substr(8, 10);

		            		var ruta = '../storage/archivos/'+consecutive+'/'+msj[x].id_tema_informacion+consecutive+'/DetallesErrores.txt';

		            		finish = true;
		            		validateFirstRow(ruta, msj[x].id_tema_informacion);
		            		bool_fallo = false;
		            	} 
	            		//console.log(JSON.stringify(msj[x]));

		            	$('#add_file').fadeIn();
						$('#btnUpload').fadeIn();
	            	}
	            	
	            	html+= ' </div>';
	            	$('#div_file_statuses').append(html);
	            	bool_fallo = false;
	            }
            } // FINAL FOR REGISTROS

            if(finish){
            	//console.log("Termina AJAX");
            	$('#divgif').empty();
            	document.getElementById('loading_table_img').style.display = 'none';
            	clearInterval(interval_consecutive);
            	clearInterval(interval_load);
            }

            if (!$('#div_file_statuses').is(':visible')) $('#div_file_statuses').fadeIn();
            
        },
        error: function (msj) {
            //console.log(msj);
            //console.log("Entra al ERROR");
        }
        
    });

}//fin function consultas

