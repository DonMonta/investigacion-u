<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_grupo_objetivo extends Model
{
    protected $table = 'invi_grupo_objetivo';
    protected $primaryKey = 'id_grupo_obj';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_grupo',
        'id_obj_proy'
    ];

}