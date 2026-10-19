<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zona_planificacion extends Model
{
    use HasFactory;
    protected $table = 'zona_planificacion';
    protected $primaryKey = 'id_zona_plan';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'nombre_zona',
    ];
    public function detalle_zona_planificacion()
    {
        return $this->hasMany(Detalle_zona_planificacion::class, 'id_zona_plan');
    }

}
