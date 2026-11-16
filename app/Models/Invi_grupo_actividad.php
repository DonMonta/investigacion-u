<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_grupo_actividad extends Model
{
    protected $table = 'invi_grupo_actividad';
    protected $primaryKey = 'id_grupo_act';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_grupo',
        'id_actividades'
    ];

}