<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroQuimioterapia extends Model
{
    protected $table = 'registro_quimioterapia';

    protected $primaryKey = 'id_seq';

    public $timestamps = false;

    protected $fillable = [
    	'id_registro',
    	'cod_diagnostico',
    	'tipo_tratamiento',
    	'cod_protocolo',
    	'frecuencia_ciclo',
    	'motivo_finalizacion',
    	'fecha_inicio_tratamiento',
    	'fecha_inicio_aplicacion',
    	'cantidad_aplicaciones',
    	'frecuencia_aplicacion',
    	'fecha_aplicacion_indicada',
    	'fecha_aplicacion_real',
    	'fecha_finalizacion'
    ];

    protected $guarded = [];
}
