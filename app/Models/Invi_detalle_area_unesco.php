<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_area_unesco extends Model
{
    protected $table = 'invi_detalle_area_unesco';
    protected $primaryKey = 'id_det_area_unesco';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_subarea_unesco',
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function subarea_unesco()
    {
        return $this->belongsTo(SubareaUnesco::class, 'id_subarea_unesco');
    }

}