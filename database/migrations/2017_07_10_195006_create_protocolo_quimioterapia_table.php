<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProtocoloQuimioterapiaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('protocolo_quimioterapia', function(Blueprint $table)
        {
            $table->string('cod_protocolo', 3)->primary('protocolo_quimioterapia_pkey');
            $table->string('descripcion_protocolo', 50);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('protocolo_quimioterapia');
    }
}
