<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_deta_inte extends Model
{
    protected $table = 'invi_detalle_integrante';
    protected $primaryKey = 'id_deta_invi_proyect';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'proyect_id',
        'ciinfper_doc',
        'ciinfper_est',
        'horas',
        'reemplazado',
        'id_funcion',
        'idCarr',
        'anexo_integrante',
        'anexo_integrante2',
        'estado'
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function funciones()
    {
        return $this->belongsTo(Invi_funcion::class, 'id_funcion');
    }
    public function carreras()
    {
        return $this->belongsTo(Carreras::class, 'idCarr');
    }
    public function informacionPersonalD()
    {
        return $this->belongsTo(InformacionPersonalD::class, 'ciinfper_doc', 'CIInfPer');
    }
    public function informacionpersonal()
    {
        return $this->belongsTo(informacionpersonal::class, 'ciinfper_est', 'CIInfPer');
    }
}