<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_proyectos extends Model
{
    protected $table = 'invi_proyectos';
    protected $primaryKey = 'proyect_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_cod',
        'proyect_nombre',
        'proyect_titulo',
        'proyect_nombre_en',
        'proyect_titulo_en',
        'proyect_multidis',
        'proyect_tipo',
        'id_convocatoria',
        'id_tip_invi_proy',
        'proyect_cobertura',
        'proyect_antecedentes',
        'proyect_justificacion',
        'proyect_fecha_pres',
        'fechainicio',
        'fechainicio_real',
        'fechafin',
        'fechafin_real',
        'proyect_duracion_mes',
        'proyect_estado',
        'proyect_estado_cases',
        'proyect_desc_situ_act',
        'proyect_diag_probl',
        'proyec_ident_poblaobj',
        'proyec_result_invest',
        'proyec_pagina_legal',
        'proyect_num_direct_hombres',
        'proyect_num_direct_mujeres',
        'proyect_total_num_direct',
        'proyect_total_num_indirect',
        'proyect_num_personas_div_fun',
        'proyect_num_doce_part',
        'proyect_num_doce_h',
        'proyect_num_doce_m',
        'proyect_num_est_part',
        'proyect_num_est_h',
        'proyect_num_est_m',
        'proyect_fact_exito',
        'proyect_rest_supu',
        'proyect_bienes',
        'proyect_servicios',
        'proyect_bienes_servicios',
        'proyect_categorizacion',
        'proyect_metodologia',
        'proyect_viabilidad_tec',
        'proyect_equip_tec',
        'proyect_no_ejecuta',
        'proyect_resul_imp_es',
        'proyect_dispo_metodo',
        'proyect_sostenibilidad_soc',
        'proyect_transf_tecn',
        'proyect_art_cientificos',
        'proyect_prototipos',
        'proyect_reg_propin',
        'proyect_empr_spin',
        'proyect_descrip_act_i_d',
        'proyect_otros',
        'proyect_archivo'
    ];
    //COORDINADOR_DIRECTOR - se usaria el filtro por la funcion director
    //CORREO_ ELECTRONICO_ COORDINADOR - se usara el mismo invi_detalle_integrante para obtener el correo institucional del director ubicado en la tabla informacionpersonal_d
    //TELEFONO_COORDINADOR - se usara el mismo invi_detalle_integrante para obtener el numero telefonico del director ubicado en la tabla informacionpersonal_d
    public function invi_detalle_integrante()
    {
        return $this->hasMany(Invi_deta_inte::class, 'proyect_id');
    }
    //FACULTAD_ENTIDAD_RESPONABLE - usa el campo siglas de la tabla facultad
    public function invi_detalle_fac_proy()
    {
        return $this->hasMany(Invi_detalle_fac_proy::class, 'proyect_id');
    }
    public function invi_detalle_obj_pro_pei()
    {
        return $this->hasMany(Invi_detalle_obj_pei::class, 'proyect_id');
    }
    public function invi_detalle_pol_pei()
    {
        return $this->hasMany(Invi_detalle_pol_pei::class, 'proyect_id');
    }
    public function invi_detalle_obj_pol_proyect()
    {
        return $this->hasMany(Invi_detalle_obj_pol_proyect::class, 'proyect_id');
    }
    public function invi_detalle_ods_proyect()
    {
        return $this->hasMany(Invi_detalle_ods_proyect::class, 'proyect_id');
    }
    public function invi_detalle_carr_proy()
    {
        return $this->hasMany(Invi_detalle_carr_proy::class, 'proyect_id');
    }
    public function invi_detalle_dom_hum()
    {
        return $this->hasMany(Invi_detalle_dom_hum::class, 'proyect_id');
    }
    public function invi_convocatoria()
    {
        return $this->belongsTo(Invi_convocatoria::class, 'id_convocatoria');
    }
    //LINEA_INVESTIGACION
    public function invi_detalle_lin_inves()
    {
        return $this->hasMany(Invi_detalle_lin_inves::class, 'proyect_id');
    }
    public function invi_detalle_area_unesco()
    {
        return $this->hasMany(Invi_detalle_area_unesco::class, 'proyect_id');
    }
    //CAMPO_DETALLADO - se usara el valor codigo de la tabla campo_detallado_unesco
    public function invi_detalle_campo_unesco()
    {
        return $this->hasMany(Invi_detalle_campo_unesco::class, 'proyect_id', 'proyect_id');
    }
    public function invi_tip_proyectos()
    {
        return $this->hasOne(Invi_tip_proyect::class, 'id_tip_invi_proy');
    }
    public function invi_detalle_cobe()
    {
        return $this->hasMany(Invi_detalle_cobe::class, 'proyect_id');
    }
    //OBJETIVO - se toma el objetivo general
    public function invi_obj_proyectos()
    {
        return $this->hasMany(Invi_Obj_Proy::class, 'proyect_id');
    }
    //COOPERACIÓN y RED - se verificará si el proyecto tienen financiamiento externo y financiamiento de una red nacional o internacional, cumpliendo con los campos que pide el CACES: 
    /**
     * INTERNACIONAL - Se verifica en la tabla praempresa si el pais de la empresa/red es diferente a ECUADOR
     * NACIONAL - Se verifica en la tabla praempresa si el pais de la empresa/red es ECUADOR
     * INTERNACIONAL Y NACIONAL - Se verifica en la tabla praempresa si el pais de la empresa/red es diferente a ECUADOR y si el pais de la empresa/red es ECUADOR
     * NO APLICA - Si no se ha añadido ninguna empresa/red en la tabla invi_detalle_inst_proy para el proyecto
     *  */  
    public function invi_detalle_inst_proy()
    {
        return $this->hasMany(Invi_detalle_inst_proy::class, 'proyect_id');
    }
    public function invi_detalle_presu_proy()
    {
        return $this->hasMany(Invi_detalle_presu_proy::class, 'proyect_id');
    }
    public function invi_detalle_articulacion()
    {
        return $this->hasMany(Invi_detalle_articulacion::class, 'proyect_id');
    }
    public function invi_actividades()
    {
        return $this->hasMany(Invi_actividades::class, 'proyect_id');
    }
    public function invi_detalle_adqui()
    {
        return $this->hasMany(Invi_detalle_adqui::class, 'proyect_id');
    }
    public function invi_detalle_financia()
    {
        return $this->hasMany(Invi_detalle_financia::class, 'proyect_id');
    }
    //IMPACTO_SOCIAL, IMPACTO_CIENTIFICO, IMPACTO_ECONOMICO, IMPACTO_AMBIENTAL, IMPACTO_POLITICO, OTRO_IMPACTO
    public function invi_det_impactos_esperados()
    {
        return $this->hasMany(Invi_det_impactos_esperados::class, 'proyect_id');
    }
    public function invi_detalle_difusion()
    {
        return $this->hasMany(Invi_det_difusion::class, 'proyect_id');
    }
    public function invi_bibliografias()
    {
        return $this->hasMany(Invi_bibliografias::class, 'proyect_id');
    }
    public function invi_det_base_pat()
    {
        return $this->hasMany(Invi_det_base_pat::class, 'proyect_id', 'proyect_id');
    }
    //PROGRAMA
    public function invi_detalle_programa()
    {
        return $this->hasMany(Invi_detalle_programa::class, 'proyect_id', 'proyect_id');
    }
    //ALCANCE TERRITORIAL 
    public function invi_detalle_alcance_territorial()
    {
        return $this->hasMany(Invi_detalle_alcance_territorial::class, 'proyect_id', 'proyect_id');
    }
    //FUENTE_FINANCIAMIENTO
    public function invi_detalle_fuente_finan()
    {
        return $this->hasMany(Invi_detalle_fuente_finan::class, 'proyect_id', 'proyect_id');
    }
    //PARAMETRO_CUMPLIMIENTO
    public function invi_detalle_param_cumpl()
    {
        return $this->hasMany(Invi_detalle_param_cumpl::class, 'proyect_id', 'proyect_id');
    }
    //RESULTADOS_VERIFICABLES
    public function invi_detalle_result_verifi()
    {
        return $this->hasMany(Invi_detalle_result_verifi::class, 'proyect_id', 'proyect_id');
    }


}