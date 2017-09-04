<?php

use Illuminate\Database\Seeder;

class GiossConsultaCUPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Llenado de la tabla Gioss_Consulta_Cup
	    DB::table('consulta_cups')->delete();

    	$header = null;
       	$data = array();
       	$filename = storage_path('seeders').'/gioss_codigos_consulta_cups.txt';
        if ($handle = fopen($filename,'r'))
        {   
            $count = 0;
            while ($row = fgetcsv($handle, 0 ,','))
            {
                
                try {
                    DB::unprepared('INSERT INTO gioss_consulta_cups(cod_consulta,descripcion,cod_sistema_cups,descrip_sistema_cups,cod_grupo_cups,desc_grupo_cups,ambito_cups,sexo_cups,nivel_atencion) VALUES(\''.utf8_encode($row[0]).'\',\''.utf8_encode($row[1]).'\',\''.utf8_encode($row[2]).'\',\''.utf8_encode($row[3]).'\',\''.utf8_encode($row[4]).'\',\''.utf8_encode($row[5]).'\',\''.utf8_encode($row[6]).'\',\''.utf8_encode($row[7]).'\',\''.utf8_encode($row[8]).'\')');
                    $count++;
                } catch (Exception $e) {
                    Log::info("Consulta CUP seeder error linea  " .$count.'. '.$e->getMessage()." arreglo: ".print_r($row,true));
                    $count++;
                    continue;
                }
                   
            }
            fclose($handle);
        }
    }
}
