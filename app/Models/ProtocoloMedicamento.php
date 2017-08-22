<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProtocoloMedicamento extends Model
{
    protected $table = 'protocolo_medicamento';

    protected $primaryKey = 'id_seq';

    public $timestamps = false;

    protected $fillable = [
    	'codigo_protocolo',
    	'codigo_medicamento_atc',
    	'codigo_medicamento_cum'
    ];

    protected $guarded = [];
}
