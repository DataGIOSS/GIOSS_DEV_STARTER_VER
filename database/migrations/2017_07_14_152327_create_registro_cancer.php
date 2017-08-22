<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistroCancer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registro_cancer', function(Blueprint $table)
        {

            $table->bigInteger('id_seq', true);
            $table->bigInteger('id_registro');
            $table->string('validez_registro', 2);
            $table->string('cod_diagnostico', 8);
            $table->string('tipo_estudio', 2);
            $table->string('motivo_no_diagnostico', 2);
            $table->bigInteger('fecha_diagnostico');
            $table->bigInteger('fecha_primera_consulta');
            $table->string('histologia', 2);
            $table->string('grado_diferenciacion', 2);
            $table->string('primera_estadificacion', 2);
            $table->bigInteger('fecha_estadificacion');
            $table->string('resultado_prueba_her2', 2);
            $table->string('estadificacion_dukes', 2);
            $table->bigInteger('fecha_estadificacion_dukes');
            $table->string('estadificacion_clinica', 2);
            $table->string('valor_escala_gleason', 2);
            $table->string('clasificacion_riesgo', 2);
            $table->bigInteger('fecha_clasificacion_riesgo');
            $table->string('objetivo_tratamiento', 2);
            $table->string('objetivo_intervencion', 2);
            $table->string('antecedentes_cancer_primario', 2);
            $table->bigInteger('fecha_diagnostico_cancer_primario');
            $table->string('cod_diagnostico_cancer_primario', 8);
            $table->string('recibio_quimioterapia', 2);
            $table->string('cantidad_fases_quimioterapia', 2);
            $table->string('recibio_prefase_citorreduccion', 2);
            $table->string('recibio_induccion', 2);
            $table->string('recibio_intensificacion', 2);
            $table->string('recibio_consolidacion', 2);
            $table->string('recibio_reinduccion', 2);
            $table->string('recibio_mantenimiento', 2);
            $table->string('recibio_mantenimiento_largo_final', 2);
            $table->string('recibio_quimioterapia_diferente', 2);
            $table->string('sometido_a_cirugia', 1);
            $table->string('ubicacion_temp_primera_cirugia', 2);
            $table->string('motivo_ultima_cirugia', 2);
            $table->string('ubicacion_temp_ultima_cirugia', 2);
            $table->string('estado_final_ult_cirugia', 2);
            $table->string('caracteristicas_actuales_prim_radio', 2);
            $table->string('motivo_finalizacion_prim_radio', 2);
            $table->string('ubicacion_temp_ult_radio');
            $table->string('tipo_radioterapia', 2);
            $table->string('caracteristicas_actuales_ult_radio', 2);
            $table->string('motivo_finalizacion_ult_radio', 2);
            $table->string('recibio_transplante', 2);
            $table->string('tipo_transplante', 2);
            $table->string('ubicacion_temp_transplante', 2);
            $table->bigInteger('fecha_transplante');
            $table->string('usuario_recibio_valoracion', 2);
            $table->string('recibio_consulta_espe_paliativo', 2);
            $table->string('recibio_consulta_prof_salud', 2);
            $table->string('recibio_consulta_otro_espe', 2);
            $table->string('recibio_consulta_medico_general', 2);
            $table->string('recibio_consulta_trabajador_social', 2);
            $table->string('recibio_consulta_otro_prof_salud', 2);
            $table->bigInteger('fecha_primera_consulta_paliativo');
            $table->string('tipo_tratamiento', 2);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('registro_cancer');
    }
}
