<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $table = 'grupo';

    protected $fillable = [
        'id',
        'detalle',
        'observacion',
    ];



    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id', 'id_grupo');
    }


}