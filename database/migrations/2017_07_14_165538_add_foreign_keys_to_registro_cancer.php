<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToRegistroCancer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('registro_cancer', function(Blueprint $table)
        {

            $table->foreign('id_registro', 'registro_cancer_fkey1')->references('id_registro_seq')->on('registro')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('cod_diagnostico', 'registro_cancer_fkey2')->references('cod_diagnostico')->on('diagnostico_ciex')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('cod_diagnostico_cancer_primario', 'registro_cancer_fkey3')->references('cod_diagnostico')->on('diagnostico_ciex')->onUpdate('CASCADE')->onDelete('RESTRICT');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('registro_cancer', function(Blueprint $table)
            {
                $table->dropForeign('registro_cancer_fkey1');
                $table->dropForeign('registro_cancer_fkey2');
                $table->dropForeign('registro_cancer_fkey3');
            });
    }
}
