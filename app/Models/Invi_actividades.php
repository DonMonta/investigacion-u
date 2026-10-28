<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_actividades extends Model
{
    protected $table = 'invi_actividades';
    protected $primaryKey = 'id_actividades';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_obj_proy',
        'nom_actividad',
        'responsables',
        'fecha_desde',
        'fecha_hasta',
        'horas',
        'detalle_anio',
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function invi_obj_proyectos()
    {
        return $this->belongsTo(Invi_Obj_Proy::class, 'id_obj_proy');
    }
    public function invi_subactividad()
    {
        return $this->hasMany(Invi_subactividad::class, 'id_actividades');
    }
    public function invi_actprod_verificables()
    {
        return $this->hasMany(Invi_actprod_verificables::class, 'id_actividades');
    }
    public function invi_actmedios_verificacion()
    {
        return $this->hasMany(Invi_actmedios_verificacion::class, 'id_actividades');
    }
    public function invi_actindicadores()
    {
        return $this->hasMany(Invi_actindicadores::class, 'id_actividades');
    }
    public function invi_actsupuestos()
    {
        return $this->hasMany(Invi_actsupuestos::class, 'id_actividades');
    }
    public function invi_informe_actividades_seguimiento()
    {
        return $this->hasMany(Invi_informe_actividades_seguimiento::class, 'id_actividades');
    }
    public function invi_informe_actividades_docentes()
    {
        return $this->hasMany(Invi_informe_actividades_docentes::class, 'id_actividades');
    }
}