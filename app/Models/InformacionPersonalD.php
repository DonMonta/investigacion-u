<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformacionPersonalD extends Model
{
    protected $table = 'informacionpersonal_d';

    protected $primaryKey = 'CIInfPer';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'CIInfPer', 'cedula_pasaporte', 'TipoDocInfPer', 'ApellInfPer', 'ApellMatInfPer', 'NombInfPer', 'NacionalidadPer', 
        'EtniaPer', 'FechNacimPer', 'LugarNacimientoPer', 'GeneroPer', 'EstadoCivilPer', 'CiudadPer', 'DirecDomicilioPer', 
        'Telf1InfPer', 'Telf2InfPer', 'CelularInfPer', 'TipoInfPer', 'StatusPer', 'mailPer', 'mailInst', 'GrupoSanguineo', 
        'tipo_discapacidad', 'carnet_conadis', 'num_carnet_conadis', 'porcentaje_discapacidad', 'fotografia', 'codigo_dactilar', 
        'huella_dactilar', 'ultima_actualizacion', 'LoginUsu', 'ClaveUsu', 'StatusUsu', 'idcarr', 'usa_biometrico', 'fecha_reg', 
        'fecha_ultimo_acceso', 'usu_registra', 'usu_modifica', 'fecha_ultima_modif', 'usu_modifica_clave', 'fecha_ultima_modif_clave', 
        'actualizoDP', 'idprovincia', 'idcanton', 'idparroquia', 'direccion2', 'numerocasa', 'idprovinciacasa', 'idcantoncasa', 
        'idparroquiacasa', 'referenciacasa', 'sectorcasa', 'barriocasa', 'viviendapropia', 'padre', 'madre', 'conyuge', 
        'nacionalidadetnia', 'fechaingreso', 'fechasalida', 'hd_posicion', 'tipoaccion', 'denominacion', 'area', 'cargo'
    ];
    protected $hidden = ['ClaveUsu', 'fotografia']; 

    // Relaciones con otras tablas (ejemplo: carrera)
    public function carrera()
    {
        return $this->belongsTo(Carreras::class, 'idcarr', 'idCarr');
    }
    public function invi_detalle_integrante()
    {
        return $this->hasMany(Invi_deta_inte::class, 'CIInfPer', 'ciinfper_doc');
    }
}