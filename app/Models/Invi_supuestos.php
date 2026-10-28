<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_supuestos extends Model
{
    protected $table = 'invi_supuestos';
    protected $primaryKey = 'id_supuestos';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_obj_proy',
        'detalle_supuestos'
    ];
    public function invi_obj_proyectos()
    {
        return $this->belongsTo(Invi_Obj_Proy::class, 'id_obj_proy');
    }
}
