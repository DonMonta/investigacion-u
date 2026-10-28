<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_equipo_depart extends Model
{
    protected $table = 'invi_equipo_depart';
    protected $primaryKey = 'id_equipo_depart';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ciinfper_doc',
        'id_equipo_roles',
        'estado_equipo_dep',
        'evidencia_arch'
    ];
    public function equipo_roles()
    {
        return $this->belongsTo(Invi_equipo_roles::class, 'id_equipo_roles');
    }
    public function informacionpersonald()
    {
        return $this->belongsTo(InformacionPersonald::class, 'ciinfper_doc', 'CIInfPer');
    }
    

}