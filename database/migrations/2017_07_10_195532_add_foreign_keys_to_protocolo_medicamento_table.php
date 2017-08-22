<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToProtocoloMedicamentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('protocolo_medicamento', function(Blueprint $table)
            {
                $table->foreign('codigo_protocolo', 'protocolo_medicamento_fkey1')->references('cod_protocolo')->on('protocolo_quimioterapia')->onUpdate('CASCADE')->onDelete('RESTRICT');
                $table->foreign('codigo_medicamento_atc', 'protocolo_medicamento_fkey2')->references('codigo_medicamento')->on('medicamentos_atc')->onUpdate('CASCADE')->onDelete('RESTRICT');
                $table->foreign('codigo_medicamento_cum', 'protocolo_medicamento_fkey3')->references('codigo_medicamento')->on('medicamentos_cum')->onUpdate('CASCADE')->onDelete('RESTRICT');
                $table->unique(['codigo_protocolo', 'codigo_medicamento_atc', 'codigo_medicamento_cum']);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('protocolo_medicamento', function(Blueprint $table)
        {
            $table->dropForeign('protocolo_medicamento_fkey1');
            $table->dropForeign('protocolo_medicamento_fkey2');
            $table->dropForeign('protocolo_medicamento_fkey3');
        });
    }
}
