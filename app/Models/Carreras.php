<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carreras extends Model
{
    use HasFactory;
     protected $table = 'carrera';
    protected $primaryKey = 'idCarr';
    public $incrementing = false; // Porque la PK no es autoincremental
    protected $keyType = 'string';

    protected $fillable = [
        'idCarr',
        'NombCarr',
        'nivelCarr',
        'StatusCarr',
        'codCarr_senescyt',
        'mod_id',
        'sau_id',
        'id_tc',
        'inst_cod',
        'idcarr_utelvt',
        'idsede',
        'idfacultad',
        'culminacion',
        'optativa',
        'carreracol',
        'habilitada',
        'tituloh',
        'titulom',
        'folio',
        'cantidadestudiante',
        'cantidadporpagina',
        'cantidadlibro',
        'fechaaprobacion',
        'resolucion',
        'duracion',
        'titulo',
        'director',
        'equivalencia',
        'secretaria',
        'carrerahomologada'
    ];

    protected $casts = [
        'StatusCarr' => 'integer',
        'mod_id' => 'integer',
        'id_tc' => 'integer',
        'idsede' => 'integer',
        'idfacultad' => 'integer',
        'culminacion' => 'integer',
        'optativa' => 'boolean',
        'habilitada' => 'boolean',
        'folio' => 'integer',
        'cantidadestudiante' => 'integer',
        'cantidadporpagina' => 'integer',
        'cantidadlibro' => 'integer',
        'fechaaprobacion' => 'datetime',
        'duracion' => 'integer'
    ];
    public function invi_detalle_integrante()
    {
        return $this->hasMany(Invi_deta_inte::class, 'idCarr');
    }
    public function sede()
    {
        return $this->belongsTo(Sede::class, 'idsede', 'idsede');
    }
    public function facultades()
    {
        return $this->hasMany(Facultad::class, 'idfacultad', 'idfacultad');
    }

   
}