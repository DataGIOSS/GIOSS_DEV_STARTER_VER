<?php

use Illuminate\Database\Seeder;

class tipoDiagnosticoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TipoDiagnostico::create([
            'cod_tipo' =>'1',
            'descripcion' => 'Impresión Diagnostica'
        ]);

        TipoDiagnostico::create([
            'cod_tipo' =>'2',
            'descripcion' => 'Confirmado Nuevo'
        ]);

        TipoDiagnostico::create([
            'cod_tipo' =>'3',
            'descripcion' => 'Confirmado Repetido'
        ]);

        return true;
    }
}
