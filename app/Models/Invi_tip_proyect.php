<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_tip_proyect extends Model
{
    protected $table = 'invi_tip_proyect';
    protected $primaryKey = 'id_tip_invi_proy';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'detalle_invi_proyect',
        'tipo'
    ];
    public function invi_proyectos()
    {
        return $this->hasMany(Invi_proyectos::class, 'id_tip_invi_proy');
    }
    
    

}
