<?php

use Illuminate\Database\Seeder;

class protocoloMedicamentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('protocolo_medicamento')->delete();

    	$header = null;
       	$data = array();
       	$filename = storage_path('seeders').'/Protocolo_Medicamentos.csv';
        if ($handle = fopen($filename,'r'))
        {   
            while ($row = fgetcsv($handle, 0,','))
            {

            	$arreglo_medicamentos = array_slice($row, 1);
            	$arreglo_medicamentos = array_map('trim', $arreglo_medicamentos);

            	$count = 0;

            	while ($count < sizeof($arreglo_medicamentos)) 
            	{
            		try 
            		{
	            		$codigo_atc = DB::table('medicamentos_atc')->where('descrip_atc', $arreglo_medicamentos[$count])->pluck('codigo_medicamento')->first();
	                	$codigo_cum = DB::table('medicamentos_cum')->where('descrip_atc', $arreglo_medicamentos[$count])->pluck('codigo_medicamento')->first();

                    	if (isset($codigo_atc) && isset($codigo_cum)) {
                    		DB::unprepared('INSERT INTO protocolo_medicamento(codigo_protocolo, codigo_medicamento_atc, codigo_medicamento_cum) VALUES(\''.$row[0].'\',\''.trim($codigo_atc).'\',\''.trim($codigo_cum).'\')');
                    	}

                    } catch (\Exception $e) {
                        Log::error("Protocolo Medicamentos Seeder error linea.  ".$e->getMessage()." Arreglo Leido: ".print_r($row,true)." Arreglo Medicamentos: ".print_r($arreglo_medicamentos,true));
                    }

                    $count++;
            	}
            }
            fclose($handle);
        }
        

        return true;
    }
}
