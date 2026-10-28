<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_difusion extends Model
{
    protected $table = 'invi_difusion';
    protected $primaryKey = 'id_difusion';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_actividad',
    ];
    
    public function invi_detalle_difusion()
    {
        return $this->hasMany(Invi_det_difusion::class, 'id_difusion');
    }

}
