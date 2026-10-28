<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_det_impactos_esperados extends Model
{
    protected $table = 'invi_det_impactos_esperados';
    protected $primaryKey = 'id_det_impactos_esp';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_impactos',
        'descripcion_general'
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function invi_impactos()
    {
        return $this->belongsTo(Invi_impactos::class, 'id_impactos');
    }
}