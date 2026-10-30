<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pei extends Model
{
    protected $table = 'pei';
    protected $primaryKey = 'id_pei';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_pei',
        'anios_pei',
        'estado_pei',
        'archivo_pei'
    ];
    public function subsistemas_pei()
    {
        return $this->hasMany(Subsistemas_pei::class, 'id_pei');
    }

    public function objetivos()
    {
        return $this->hasManyThrough(
            Objetivos_pei::class,
            Subsistemas_pei::class,
            'id_pei',               // Llave foránea en subsistemas_pei
            'id_sub_sistema_pei',   // Llave foránea en objetivos_pei
            'id_pei',               // Llave local en pei
            'id_sub_sistema_pei'    // Llave local en subsistemas_pei
        );
    }
    public function politicas_pei()
    {
        return $this->hasMany(Politicas_pei::class, 'id_pei');
    }
    /**
     * Relación: Estados de este PEI por departamento.
     */
    public function estadosDepartamentos()
    {
        return $this->hasMany(PeiEstadoDepartamento::class, 'id_pei', 'id_pei');
    }

    /**
     * Relación Muchos a Muchos: Departamentos que han interactuado con este PEI.
     */
    public function departamentos()
    {
        return $this->belongsToMany(Departamento::class, 'pei_estados_departamentos', 'id_pei', 'id_departamento')
                    ->withPivot('estado');
    }

}
