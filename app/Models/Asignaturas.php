<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignaturas extends Model
{
    protected $table = 'asignatura';
    protected $primaryKey = 'IdAsig';
    public $incrementing = false;
    protected $keyType = 'varchar';
    public $timestamps = false;

    protected $fillable = [
        'NombAsig',
        'ColorAsig',
        'StatusAsig',
        'idcarr'
    ];
    public function carrera()
    {
        return $this->belongsTo(Carreras::class, 'idcarr');
    }
    public function invi_detalle_articulacion()
    {
        return $this->hasMany(Invi_detalle_articulacion::class, 'IdAsig');
    }
    

}
