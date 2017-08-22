<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProtocoloMedicamentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('protocolo_medicamento', function(Blueprint $table)
            {
                $table->bigInteger('id_seq', true);
                $table->string('codigo_protocolo', 3);
                $table->string('codigo_medicamento_atc', 40);
                $table->string('codigo_medicamento_cum', 40);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('protocolo_medicamento');
    }
}
