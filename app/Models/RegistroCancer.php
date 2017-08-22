<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RegistroCancer
 */
class RegistroCancer extends Model
{
    protected $table = 'registro_cancer';

    protected $primaryKey = 'id_seq';

	public $timestamps = false;

    protected $fillable = [
        'id_registro',
        'validez_registro',
        'cod_diagnostico',
        'tipo_estudio',
        'motivo_no_diagnostico',
        'fecha_diagnostico',
        'fecha_primera_consulta',
        'histologia',
        'grado_diferenciacion',
        'primera_estadificacion',
        'fecha_estadificacion',
        'resultado_prueba_her2',
        'estadificacion_dukes',
        'fecha_estadificacion_dukes',
        'estadificacion_clinica',
        'valor_escala_gleason',
        'clasificacion_riesgo',
        'fecha_clasificacion_riesgo',
        'objetivo_tratamiento',
        'objetivo_intervencion',
        'antecedentes_cancer_primario',
        'fecha_diagnostico_cancer_primario',
        'cod_diagnostico_cancer_primario',
        'recibio_quimioterapia',
        'cantidad_fases_quimioterapia',
        'recibio_prefase_citorreduccion',
        'recibio_induccion',
        'recibio_intensificacion',
        'recibio_consolidacion',
        'recibio_reinduccion',
        'recibio_mantenimiento',
        'recibio_mantenimiento_largo_final',
        'recibio_quimioterapia_diferente',
        'sometido_a_cirugia',
        'ubicacion_temp_primera_cirugia',
        'motivo_ultima_cirugia',
        'ubicacion_temp_ultima_cirugia',
        'estado_final_ult_cirugia',
        'caracteristicas_actuales_prim_radio',
        'motivo_finalizacion_prim_radio',
        'ubicacion_temp_ult_radio',
        'tipo_radioterapia',
        'caracteristicas_actuales_ult_radio',
        'motivo_finalizacion_ult_radio',
        'recibio_transplante',
        'tipo_transplante',
        'ubicacion_temp_transplante',
        'fecha_transplante',
        'usuario_recibio_valoracion',
        'recibio_consulta_espe_paliativo',
        'recibio_consulta_prof_salud',
        'recibio_consulta_otro_espe',
        'recibio_consulta_medico_general',
        'recibio_consulta_trabajador_social',
        'recibio_consulta_otro_prof_salud',
        'fecha_primera_consulta_paliativo',
        'tipo_tratamiento'
    ];

    protected $guarded = [];

        
}