<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistroQuimioterapiaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registro_quimioterapia', function(Blueprint $table)
            {
                $table->bigInteger('id_seq', true);
                $table->bigInteger('id_registro');
                $table->string('cod_diagnostico', 8);
                $table->string('tipo_tratamiento', 2);
                $table->string('cod_protocolo', 3);
                $table->string('frecuencia_ciclo', 2);
                $table->string('motivo_finalizacion', 2);
                $table->bigInteger('fecha_inicio_tratamiento')->nullable();
                $table->bigInteger('fecha_inicio_aplicacion')->nullable();
                $table->string('cantidad_aplicaciones', 2);
                $table->string('frecuencia_aplicacion')->nullable();
                $table->bigInteger('fecha_aplicacion_indicada')->nullable();
                $table->bigInteger('fecha_aplicacion_real')->nullable();
                $table->bigInteger('fecha_finalizacion')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('registro_quimioterapia');
    }
}
