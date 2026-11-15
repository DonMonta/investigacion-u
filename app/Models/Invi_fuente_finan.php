<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_fuente_finan extends Model
{
    protected $table = 'invi_fuente_finan';
    protected $primaryKey = 'id_fuente_finan';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'estado',
    ];
   
    public function invi_detalle_fuente_finan()
    {
        return $this->hasMany(Invi_detalle_fuente_finan::class, 'id_fuente_finan', 'id_fuente_finan');
    }

}
