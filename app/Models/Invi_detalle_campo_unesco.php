<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_campo_unesco extends Model
{
    protected $table = 'invi_detalle_campo_unesco';
    protected $primaryKey = 'id_det_campo_unesco';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_campo_unesco',
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function campo_detallado()
    {
        return $this->belongsTo(Campo_detallado_unesco::class, 'id_campo_unesco', 'id');
    }

}