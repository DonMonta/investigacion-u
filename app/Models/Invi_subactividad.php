<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_subactividad extends Model
{
    protected $table = 'invi_subactividad';
    protected $primaryKey = 'id_subactividad';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_actividades',
        'nom_sub_actv',
        'fecha_desde',
        'fecha_hasta',
        'horas',
    ];
    public function invi_actividades()
    {
        return $this->belongsTo(Invi_actividades::class, 'id_actividades');
    }
    public function invi_informe_actividades_seguimiento()
    {
        return $this->hasMany(Invi_informe_actividades_seguimiento::class, 'id_subactividad');
    }
    public function invi_informe_actividades_docentes()
    {
        return $this->hasMany(Invi_informe_actividades_docentes::class, 'id_subactividad');
    }
}
