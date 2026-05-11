<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_fac_proy extends Model
{
    protected $table = 'invi_detalle_fac_proy';
    protected $primaryKey = 'id_det_fac';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'idfacultad',
        'id_facultad_priori',
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function facultades()
    {
        return $this->belongsTo(Facultad::class, 'idfacultad');
    }
    public function facultades_priori()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad_priori');
    }

}