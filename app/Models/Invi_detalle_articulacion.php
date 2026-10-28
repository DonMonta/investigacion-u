<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_articulacion extends Model
{
    protected $table = 'invi_detalle_articulacion';
    protected $primaryKey = 'id_det_articu_proyect';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'IdAsig',
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function asignaturas()
    {
        return $this->belongsTo(Asignaturas::class, 'IdAsig');
    }

}