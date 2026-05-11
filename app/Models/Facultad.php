<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    protected $table = 'facultad';
    protected $primaryKey = 'idfacultad';
    public $incrementing = false; // Porque la PK no es autoincremental
    protected $keyType = 'int';

    protected $fillable = [
        'idfacultad',
        'idsede',
        'facultad',
        'decano',
        'cargodecano',
        'secretario',
        'cargosecretario',
        'fechacreacion',
        'siglas'
    ];

    
    protected $casts = [
        'idfacultad' => 'integer',
        'idsede' => 'integer',
    ];
    public function facultades_priori()
    {
        return $this->hasMany(Invi_detalle_fac_proy::class, 'id_facultad_priori');
    }
    public function facultades()
    {
        return $this->hasMany(Invi_detalle_fac_proy::class, 'idfacultad');
    }
    public function carreras(){
        return $this->hasMany(Carreras::class, 'idfacultad', 'idfacultad');
    }
    public function sede()
    {
        return $this->belongsTo(Sede::class, 'idsede', 'idsede');
    }
    
}