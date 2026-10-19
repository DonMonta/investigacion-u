<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_lin_inves extends Model
{
    protected $table = 'invi_detalle_lin_inves';
    protected $primaryKey = 'id_det_linea_investiga';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_sublin_investiga',
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function sublineas_investigacion()
    {
        return $this->belongsTo(Invi_sub_linea_inves::class, 'id_sublin_investiga');
    }

}