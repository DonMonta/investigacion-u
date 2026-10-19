<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_informes extends Model
{
    protected $table = 'invi_informes';
    protected $primaryKey = 'id_informes';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'proyecto_id',
        'id_proyecto_formato',
        'tipo_informe',
        'idper',
        'parcial',
        'porcentaje_avance_global',
        'CIInfPer',
        'estado',
        'fecha_presentacion',
    ];
    public function proyecto()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyecto_id', 'proyect_id');
    }
    public function invi_proyectos_formatos()
    {
        return $this->hasMany(Invi_proyectos_formatos::class, 'id_proyecto_formato', 'id_proyecto_formato');
    }
    public function periodolectivo()
    {
        return $this->belongsTo(PeriodoLectivo::class, 'idper', 'idper');
    }
    public function invi_informe_actividades_seguimiento()
    {
        return $this->hasMany(Invi_informe_actividades_seguimiento::class, 'informe_id', 'id_informes');
    }
    public function invi_informe_asistencia_estudiantes()
    {
        return $this->hasMany(Invi_informe_asistencia_estudiantes::class, 'informe_id', 'id_informes');
    }
    public function invi_informe_actividades_docentes()
    {
        return $this->hasMany(Invi_informe_actividades_docentes::class, 'informe_id', 'id_informes');
    }

}
