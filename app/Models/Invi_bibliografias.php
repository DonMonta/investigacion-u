<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_bibliografias extends Model
{
    protected $table = 'invi_bibliografias';
    protected $primaryKey = 'id_bibliografia';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'seccion_campo',
        'autor',
        'anio',
        'titulo',
        'editorial_fuente',
    ];
    
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }

}
