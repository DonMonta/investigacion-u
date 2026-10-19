<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_informe_asistencia_estudiantes extends Model
{
    protected $table = 'invi_informe_asistencia_estudiantes';
    protected $primaryKey = 'id_infor_asis_est';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'informe_id',
        'CIInfPer',
        'horas_asignadas',
        'horas_cumplidas',
        'asistio_sesion',
        'fecha_registro',
        'estado_evaluacion',
        'observaciones',
    ];
    public function invi_informes()
    {
        return $this->belongsTo(Invi_informes::class, 'informe_id', 'id_informes');
    }
    public function estudiantes()
    {
        return $this->belongsTo(informacionpersonal::class, 'CIInfPer', 'CIInfPer');
    }

}
