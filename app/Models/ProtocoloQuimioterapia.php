<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProtocoloQuimioterapia extends Model
{
    protected $table = 'protocolo_quimioterapia';

    protected $primaryKey = 'cod_protocolo';

    public $timestamps = false;

    protected $fillable = [
    	'descripcion_protocolo'
    ];

    protected $guarded = [];
}
