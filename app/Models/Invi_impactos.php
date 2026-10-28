<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_impactos extends Model
{
    protected $table = 'invi_impactos';
    protected $primaryKey = 'id_impactos';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_impacto'
    ];
    public function invi_det_impactos_esperados()
    {
        return $this->hasMany(Invi_det_impactos_esperados::class, 'id_impactos');
    }
    

}
