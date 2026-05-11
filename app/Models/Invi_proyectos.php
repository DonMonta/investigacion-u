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


}