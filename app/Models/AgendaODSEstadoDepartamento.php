<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaODSEstadoDepartamento extends Model
{
    protected $table = 'agendaods_estados_departamentos';
    protected $primaryKey = 'id_agenda_ods_estado';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
       'id_ag_ods',
        'id_departamento',
        'estado',
    ];
    public function agendaods()
    {
        return $this->belongsTo(Agenda_ODS::class, 'id_ag_ods', 'id_ag_ods');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }
   
}
