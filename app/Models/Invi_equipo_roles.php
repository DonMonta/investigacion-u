<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_equipo_roles extends Model
{
    protected $table = 'invi_equipo_roles';
    protected $primaryKey = 'id_equipo_roles';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_rol',
        'detalle_rol',
        'funciones_rol',
        'tipo_rol',
        'estado_rol',
    ];
    public function equipo_depart()
    {
        return $this->hasMany(Invi_equipo_depart::class, 'id_equipo_roles');
    }
    
    

}