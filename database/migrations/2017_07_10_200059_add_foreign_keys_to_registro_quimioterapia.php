<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToRegistroQuimioterapia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('registro_quimioterapia', function(Blueprint $table)
            {
                $table->foreign('id_registro', 'registro_quimioterapia_fkey1')->references('id_registro_seq')->on('registro')->onUpdate('CASCADE')->onDelete('RESTRICT');
                $table->foreign('cod_diagnostico', 'registro_quimioterapia_fkey2')->references('cod_diagnostico')->on('diagnostico_ciex')->onUpdate('CASCADE')->onDelete('RESTRICT');
                $table->foreign('cod_protocolo', 'registro_quimioterapia_fkey3')->references('cod_protocolo')->on('protocolo_quimioterapia')->onUpdate('CASCADE')->onDelete('RESTRICT');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('registro_quimioterapia', function(Blueprint $table)
            {
                $table->dropForeign('registro_quimioterapia_fkey1');
                $table->dropForeign('registro_quimioterapia_fkey2');
                $table->dropForeign('registro_quimioterapia_fkey3');
            });
    }
}
