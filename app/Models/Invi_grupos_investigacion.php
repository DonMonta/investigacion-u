<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_grupos_investigacion extends Model
{
    protected $table = 'invi_grupos_investigacion';
    protected $primaryKey = 'id_grupo';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'proyect_id',
        'nombre_grupo',
        'acronimo',
        'estado',
    ];

    // Relación con el Proyecto
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id', 'proyect_id');
    }

    // Relación con los integrantes del grupo (1 a Muchos)
    public function integrantes()
    {
        return $this->hasMany(Invi_deta_inte::class, 'id_grupo', 'id_grupo');
    }

    // Relación Muchos a Muchos con Objetivos del Proyecto
    public function objetivos()
    {
        return $this->belongsToMany(Invi_obj_proy::class, 'invi_grupo_objetivo', 'id_grupo', 'id_obj_proy')
                    ->withPivot('id_grupo_obj'); // Incluye el ID de la tabla pivote si lo necesitas
    }

    // Relación Muchos a Muchos con Actividades del Proyecto
    public function actividades()
    {
        return $this->belongsToMany(Invi_actividades::class, 'invi_grupo_actividad', 'id_grupo', 'id_actividades')
                    ->withPivot('id_grupo_act'); // Incluye el ID de la tabla pivote si lo necesitas
    }

}