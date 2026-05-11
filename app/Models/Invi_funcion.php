<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_funcion extends Model
{
    protected $table = 'invi_funcion';
    protected $primaryKey = 'id_funcion';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_funcion',
        'tipo_funcion',//Por defecto es VINCULACIÓN
        'estado',
    ];
    public function invi_detalle_integrante()
    {
        return $this->hasMany(Invi_deta_inte::class, 'id_funcion');
    }

}