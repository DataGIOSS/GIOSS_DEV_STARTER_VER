<?php

use Illuminate\Database\Seeder;
use App\Models\TipoIdentificacionUser;

class tipoIdentificacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TipoIdentificacionUser::create([
        	'id_tipo_ident'=>'TI',
        	'descripcion' => 'Tarjeta de Identidad'
        ]);

        TipoIdentificacionUser::create([
        	'id_tipo_ident'=>'CE',
        	'descripcion' => 'Cedula de Extranjería'
        ]);

        TipoIdentificacionUser::create([
        	'id_tipo_ident'=>'CC',
        	'descripcion' => 'Cedula de Ciudadanía'
        ]);

        TipoIdentificacionUser::create([
        	'id_tipo_ident'=>'PA',
        	'descripcion' => 'Pasaporte'
        ]);

        TipoIdentificacionUser::create([
        	'id_tipo_ident'=>'MS',
        	'descripcion' => 'Menor sin identificación'
        ]);

        TipoIdentificacionUser::create([
        	'id_tipo_ident'=>'AS',
        	'descripcion' => 'Adulto sin Identificación'
        ]);

        TipoIdentificacionUser::create([
        	'id_tipo_ident'=>'CD',
        	'descripcion' => 'Carnet diplomático'
        ]);

        TipoIdentificacionUser::create([
        	'id_tipo_ident'=>'NV',
        	'descripcion' => 'Certificado Nacido Vivo '
        ]);

        TipoIdentificacionUser::create([
            'id_tipo_ident'=>'RC',
            'descripcion' => 'Registro Civil'
        ]);

        TipoIdentificacionUser::create([
            'id_tipo_ident'=>'NU',
            'descripcion' => 'Número Único'
        ]);

        return true;
    }
}
