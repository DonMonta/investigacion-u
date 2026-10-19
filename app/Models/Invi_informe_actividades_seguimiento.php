<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_informe_actividades_seguimiento extends Model
{
    protected $table = 'invi_informe_actividades_seguimiento';
    protected $primaryKey = 'id_informe_act_seg';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'informe_id',
        'id_actividades',
        'id_subactividad',
        'fecha_inicio_real',
        'fecha_fin_real',
        'horas_ejecutadas',
        'presupuesto_real_utlvte',
        'presupuesto_real_beneficiario',
        'presupuesto_real_total',
        'porcentaje_cumplimiento',
        'estado_cumplimiento',
        'responsable_ejecucion',
        'documento_evidencia_ref',
        'observaciones',
    ];
    public function invi_informes()
    {
        return $this->belongsTo(Invi_informes::class, 'informe_id', 'id_informes');
    }
    public function invi_actividades()
    {
        return $this->belongsTo(Invi_actividades::class, 'id_actividades', 'id_actividades');
    }
    public function invi_subactividades()
    {
        return $this->belongsTo(Invi_subactividad::class, 'id_subactividad', 'id_subactividad');
    }

}
