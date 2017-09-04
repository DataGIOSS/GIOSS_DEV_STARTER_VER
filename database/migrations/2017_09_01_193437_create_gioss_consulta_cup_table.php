<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGiossConsultaCupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('gioss_consulta_cups', function(Blueprint $table)
        {
            $table->string('cod_consulta', 16)->primary('gioss_consulta_cups_pkey');
            $table->string('descripcion', 200)->nullable();
            $table->string('cod_sistema_cups', 4)->nullable();
            $table->string('descrip_sistema_cups', 200)->nullable();
            $table->string('cod_grupo_cups', 2)->nullable();
            $table->string('desc_grupo_cups', 200)->nullable();
            $table->string('ambito_cups', 4)->nullable();
            $table->string('sexo_cups', 4)->nullable();
            $table->string('nivel_atencion', 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('gioss_consulta_cups');
    }
}
