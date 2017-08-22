<?php

use Illuminate\Database\Seeder;
use App\Models\TemaInformacion;

class temaInformacion_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TemaInformacion::create([
        	'id_tema_informacion' => 'AAC',
        	'descripcion' =>'Archivo atención en consulta.',
  
        ]);

        TemaInformacion::create([
        	'id_tema_informacion' => 'ASM',
        	'descripcion' =>'Archivo Suministro de Medicamentos.',
  
        ]);

        TemaInformacion::create([
        	'id_tema_informacion' => 'AEH',
        	'descripcion' =>'Archivo egresos hospitalarios.',
  
        ]);

        TemaInformacion::create([
        	'id_tema_informacion' => 'AVA',
        	'descripcion' =>'Archivo vacunas aplicadas.',
  
        ]);

        TemaInformacion::create([
        	'id_tema_informacion' => 'APS',
        	'descripcion' =>'Archivo procedemientos',
  
        ]);

        TemaInformacion::create([
            'id_tema_informacion' => 'RAD',
            'descripcion' =>'Archivo ayudas diagnosticas',
  
        ]);

        TemaInformacion::create([
            'id_tema_informacion' => 'ATP',
            'descripcion' =>'Archivo talla, peso, tension',
  
        ]);

        TemaInformacion::create([
            'id_tema_informacion' => 'ARQ',
            'descripcion' =>'Archivo registro quimioterapia',
  
        ]);

        TemaInformacion::create([
            'id_tema_informacion' => 'ARC',
            'descripcion' =>'Archivo registro quimioterapia',
  
        ]);

        return true;
    }
}
