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
        'fechainicio',
        'fechafin',
        'proyect_tipo',
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



}