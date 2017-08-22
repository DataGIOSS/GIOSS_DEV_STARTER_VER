$(document).ready(function(){
	//console.log('funfs');
	$('#cod_dept').val(0);
	getMunicipios(0);

	var ut = $("#tipo_usuario").val();
	if (ut == 2) {
		$("#separador").fadeIn();
		$("#info_entidad").fadeIn();
			
	}else{
		
		$("#info_entidad").fadeOut();
	}

	$("#tipo_usuario").on('change', function () {
		if ($(this).val() == 2) {
			$("#separador_usuario").fadeOut();
			$("#info_usuario").fadeOut();
			
		}else{
			$("#separador_usuario").fadeIn();
			$("#info_usuario").fadeIn();
		}
	});

	$('#cod_dept').on('change',function(){
		console.log("Código del Departamento Seleccionado: " + $(this).val());
		var deptid = $(this).val();
		getMunicipios(deptid); // Código del Departamento
	});
});

function getMunicipios(deptid){
	console.log("Ingresa a la función getMunicipios");
	$.ajax({
		type: "get",
        data: {'departamento': deptid},
        url: routeGetMunicipios,
        success: function(msg)
        {	
        	console.log("Ingresa al success de la función AJAX getMunicipios");
        	$('#cod_muni').empty();
            for (x in msg){
            	$('#cod_muni').append('<option value="' + msg[x].cod_divipola + '">' + msg[x].cod_divipola + ' - '+msg[x].nombre + '</option>');
            }
        },
        dataType: "json",
        cache: "false",
        error: function(msg){console.log( msg)},
	});
}

