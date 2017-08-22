<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;
use App\Models\Municipio;
use Illuminate\Support\Facades\Log;

class DepartamentoController extends Controller
{
    public function getMunicipios(Request $request){
    	Log::info("----------------------------- Entra a la función de DepartamentosController getMunicipios ------------------------------------------");
    	try {
    		$municipios = Municipio::where('cod_depto', $request->departamento)->orderBy('nombre','asc')->get();	
    	} catch (\Exception $e) {
    		Log::error('Error al consultar los municipios del departamento '.$request->departamento." Error-> ".$e.getMessage());
    	}

    	echo json_encode($municipios);
    }
}

