<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda_ODS extends Model
{
    protected $table = 'agenda_ods';
    protected $primaryKey = 'id_ag_ods';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_ag_ods',
        'anio_ag_ods',
        'archivo_ag_ods',
        'estado_ag_ods'
    ];
    public function ods()
    {
        return $this->hasMany(ODS::class, 'id_ag_ods');
    }
    /**
     * Relación: Estados de esta Agenda ODS por departamento.
     */
    public function estadosDepartamentosAgendaODS(){
        return $this->hasMany(AgendaODSEstadoDepartamento::class, 'id_ag_ods', 'id_ag_ods');
    }
    public function departamentos()
    {
        return $this->belongsToMany(Departamento::class, 'agendaods_estados_departamentos', 'id_ag_ods', 'id_departamento')
                    ->withPivot('estado');
    }
    

}
