<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detalle_zona_planificacion extends Model
{
    use HasFactory;
    protected $table = 'detalle_zona_planificacion';
    protected $primaryKey = 'id_detalle_zona_plan';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'id_zona_plan',
        'id_provincia',
    ];
    public function zona_planificacion()
    {
        return $this->belongsTo(Zona_planificacion::class, 'id_zona_plan');
    }
    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'id_provincia');
    }
    public function invi_detalle_cobe()
    {
        return $this->hasMany(Invi_detalle_cobe::class, 'id_detalle_zona_plan');
    }

}
