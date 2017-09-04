<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiossConsultaCup extends Model {

	protected $table = 'gioss_consulta_cups';

    protected $primaryKey = 'cod_consulta';

	public $timestamps = false;

    protected $fillable = [
        'descripcion'
    ];

    protected $guarded = [];

}
