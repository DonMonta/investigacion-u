<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_medios_verificacion extends Model
{
    protected $table = 'invi_medios_verificacion';
    protected $primaryKey = 'id_medios_verifi';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_obj_proy',
        'detalle_medio_verifica'
    ];
    public function invi_obj_proyectos()
    {
        return $this->belongsTo(Invi_Obj_Proy::class, 'id_obj_proy');
    }
}
