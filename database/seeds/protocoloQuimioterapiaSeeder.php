<?php

use Illuminate\Database\Seeder;
use App\Models\ProtocoloQuimioterapia;

class protocoloQuimioterapiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //se borra el contenido de la tabla
        DB::table('protocolo_quimioterapia')->delete();

        $header = NULL;
        $data = array();
        $filename = storage_path('seeders').'/Protocolo_Quimioterapia.csv';

        if ($handle = fopen($filename,'r'))
        {
            while ($row = fgetcsv($handle, 0,','))
            {
                if(!$header)
                {
                    $header = $row;
                }
                else
                {
                    $data[] = array_combine($header, $row); 
                }
                    
            }
            fclose($handle);
        }

        //se adiciona el contenido de $data
        DB::table('protocolo_quimioterapia')->insert($data);

        return true;
    }
}
