<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_cobe extends Model
{
    protected $table = 'invi_detalle_cobe';
    protected $primaryKey = 'id_detalle_cobe';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_detalle_zona_plan',
        'id_provincia',
        'id_canton',
        'idparroquia',
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function detalle_zona_plan()
    {
        return $this->belongsTo(Detalle_zona_planificacion::class, 'id_detalle_zona_plan');
    }
    public function provincias()
    {
        return $this->belongsTo(Provincia::class, 'id_provincia');
    }
    public function cantones()
    {
        return $this->belongsTo(Canton::class, 'id_canton');
    }
    public function parroquias()
    {
        return $this->belongsTo(Parroquia::class, 'idparroquia');
    }

}