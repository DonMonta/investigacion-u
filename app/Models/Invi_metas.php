<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_metas extends Model
{
    protected $table = 'invi_metas';
    protected $primaryKey = 'id_metas';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_obj_proy',
        'detalle_metas'
    ];
    public function invi_obj_proyectos()
    {
        return $this->belongsTo(Invi_Obj_Proy::class, 'id_obj_proy');
    }
}
