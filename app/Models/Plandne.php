<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plandne extends Model
{
    protected $table = 'plandne';
    protected $primaryKey = 'id_pladne';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_plandne',
        'anio_plandne',
        'link_plandne',
        'estado_plandne'
    ];
    public function objetivos_plandne()
    {
        return $this->hasMany(Obj_pol_plandne::class, 'id_pladne');
    }
    public function politicas_plandne()
    {
         return $this->hasManyThrough(
            Politicas_plandne::class,
            Obj_pol_plandne::class,
            'id_pladne',               
            'id_obj_pol_pladne',   
            'id_pladne',               
            'id_obj_pol_pladne'   
        );
    }
     /**
     * Relación: Estados de este PlanDNE por departamento.
     */
    public function estadosDepartamentosPlanDNE()
    {
        return $this->hasMany(PlandneEstadoDepartamento::class, 'id_pladne', 'id_pladne');
    }

    /**
     * Relación Muchos a Muchos: Departamentos que han interactuado con este PlanDNE.
     */
    public function departamentos()
    {
        return $this->belongsToMany(Departamento::class, 'plandne_estados_departamentos', 'id_pladne', 'id_departamento')
                    ->withPivot('estado');
    }

}
