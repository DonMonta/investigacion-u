<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_financia extends Model
{
    protected $table = 'invi_detalle_financia';
    protected $primaryKey = 'id_det_financia';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_rubro',
        'cantidad',
        'valor',
        'utlvte_anio1',
        'utlvte_anio2',
        'utlvte_anio3',
        'utlvte_anio4',
        'utlvte_anio5',
        'otros_anio1',
        'otros_anio2',
        'otros_anio3',
        'otros_anio4',
        'otros_anio5',
        'total_efectivo',
        
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function invi_rubros()
    {
        return $this->belongsTo(Invi_rubros::class, 'id_rubro');
    }
}