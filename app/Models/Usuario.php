<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';
    protected $primaryKey = 'LoginUsu';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'LoginUsu',
        'ClaveUsu',
        'StatusUsu',
        'NombUsu',
        'email',
        'movil',
        'idperfil',
        'ciinfper',
        'idcarr',
        'id_actdist',
        'usa_biometrico',
        'fecha_reg',
        'fecha_ultimo_acceso',
        'titulo',
        'homologar',
        'crearnota',
        'posgrado',
        'idcampus',
        'inscribir',
        'equivalencia',
        'id_grupo',
        'usuarioreg',
    ];

    protected $casts = [
        'usa_biometrico' => 'boolean',
        'homologar' => 'boolean',
        'inscribir' => 'boolean',
        'equivalencia' => 'boolean',
        'fecha_reg' => 'datetime',
        'fecha_ultimo_acceso' => 'datetime',
    ];

    // Relación con la tabla informacionpersonal_d (si aplica)
    public function persona()
    {
        return $this->belongsTo(InformacionPersonalD::class, 'ciinfper', 'CIInfPer');
    }

    // Relación con la tabla perfil (si aplica)
    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'idperfil', 'idperfil');
    }

    // Relación con la tabla carreras (si aplica)
    public function carrera()
    {
        return $this->belongsTo(Carreras::class, 'idcarr', 'idCarr');
    }
    // Relación con la tabla grupo (si aplica)
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id');
    }
    
}