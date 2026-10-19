<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_dom_hum extends Model
{
    protected $table = 'invi_detalle_dom_hum';
    protected $primaryKey = 'id_det_dom_hum';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_dom_hum',
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function dominios_humanisticos()
    {
        return $this->belongsTo(Invi_dom_huma::class, 'id_dom_hum');
    }

}