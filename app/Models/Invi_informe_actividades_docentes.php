<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_informe_actividades_docentes extends Model
{
    protected $table = 'invi_informe_actividades_docentes';
    protected $primaryKey = 'id_infor_act_doc';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'informe_id',
        'CIInfPer',
        'numero_semana',
        'fecha_inicio',
        'fecha_fin',
        'numero_horas',
        'id_actividades',
        'id_subactividad',
        'observaciones',
    ];
    public function invi_informes()
    {
        return $this->belongsTo(Invi_informes::class, 'informe_id', 'id_informes');
    }
    public function docentes()
    {
        return $this->belongsTo(InformacionPersonalD::class, 'CIInfPer', 'CIInfPer');
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
