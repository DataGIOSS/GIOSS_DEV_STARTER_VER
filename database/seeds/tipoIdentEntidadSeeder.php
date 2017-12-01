<?php

use Illuminate\Database\Seeder;
use App\Models\TipoIdentificacionEntidad;

class tipoIdentEntidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TipoIdentificacionEntidad::create([
    		'id_tipo_ident' =>'MU',
    		'descripcion' => 'MUNICIPIO'
        ]);

        TipoIdentificacionEntidad::create([
            'id_tipo_ident' =>'DE',
            'descripcion' => 'DEPARTAMENTO'
        ]);

        TipoIdentificacionEntidad::create([
            'id_tipo_ident' =>'DI',
            'descripcion' => 'DEPARTAMENTO'
        ]);

        TipoIdentificacionEntidad::create([
            'id_tipo_ident' =>'NI',
            'descripcion' => 'DEPARTAMENTO'
        ]);

        TipoIdentificacionEntidad::create([
            'id_tipo_ident' =>'SD',
            'descripcion' => 'NO DEFINIDO'
        ]);

    }
}
