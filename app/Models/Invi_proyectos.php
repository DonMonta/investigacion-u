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
        'fechafin',
        'proyect_duracion_mes',
        'proyect_estado',
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
        'proyect_sostenibilidad_soc',
        'proyect_transf_tecn',
        'proyect_art_cientificos',
        'proyect_prototipos',
        'proyect_reg_propin',
        'proyect_empr_spin',
        'proyect_archivo'
    ];
    public function invi_detalle_integrante()
    {
        return $this->hasMany(Invi_deta_inte::class, 'proyect_id');
    }
    public function invi_detalle_fac_proy()
    {
        return $this->hasMany(Invi_detalle_fac_proy::class, 'proyect_id');
    }
    public function invi_detalle_obj_pro_pei()
    {
        return $this->hasMany(Invi_detalle_obj_pei::class, 'proyect_id');
    }
    public function invi_detalle_obj_pol_pei()
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
    public function invi_detalle_lin_inves()
    {
        return $this->hasMany(Invi_detalle_lin_inves::class, 'proyect_id');
    }
    public function invi_detalle_area_unesco()
    {
        return $this->hasMany(Invi_detalle_area_unesco::class, 'proyect_id');
    }
    public function invi_tip_proyectos()
    {
        return $this->hasOne(Invi_tip_proyect::class, 'id_tip_invi_proy');
    }
    public function invi_detalle_cobe()
    {
        return $this->hasMany(Invi_detalle_cobe::class, 'proyect_id');
    }
    public function invi_obj_proyectos()
    {
        return $this->hasMany(Invi_Obj_Proy::class, 'proyect_id');
    }
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


}