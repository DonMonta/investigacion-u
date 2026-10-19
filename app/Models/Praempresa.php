<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Praempresa extends Model
{
    use HasFactory;
    protected $table = 'praempresa';
    protected $primaryKey = 'idempresa';
    protected $fillable = [
        'idempresa',
        'ruc',
        'empresa',
        'empresacorta',
        'lugar',
        'direccion',
        'telefono',
        'email',
        'url',
        'tipo',
        'titulo',
        'representante',
        'ci_representante',
        'cargo',
        'actividad',
        'fechafin',
        'tipoinstitucion',
        'pais',
        'vision',
        'mision',
        'estado_empr',
        'imagen',
        'ciudad',
        'usuario_id',
        'archivo'
    ];
    protected $hidden = ['imagen'];
    public function invi_detalle_inst_proy()
    {
        return $this->hasMany(Invi_detalle_inst_proy::class, 'idempresa');
    }
    public function invi_aportesinst()
    {
        return $this->hasMany(Invi_aportesinst::class, 'idempresa');
    }

}
