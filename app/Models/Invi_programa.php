<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_programa extends Model
{
    protected $table = 'invi_programa';
    protected $primaryKey = 'id_programa';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_programa',
        'dominio',
        'estado_programa',
    ];
   
    public function invi_detalle_programa()
    {
        return $this->hasMany(Invi_detalle_programa::class, 'id_programa', 'id_programa');
    }

}
