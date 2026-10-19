<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';
    protected $primaryKey = 'id_departamento';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
       'id_padre',
        'nombre_departamento',
        'ci_jefe_inmediato',
    ];
    public function peisEstados()
    {
        return $this->hasMany(PeiEstadoDepartamento::class, 'id_departamento', 'id_departamento');
    }
    public function plandnesEstados()
    {
        return $this->hasMany(PlandneEstadoDepartamento::class, 'id_departamento', 'id_departamento');
    }
    public function agendaODSEstados()
    {
        return $this->hasMany(AgendaODSEstadoDepartamento::class, 'id_departamento', 'id_departamento');
    }
   
}
