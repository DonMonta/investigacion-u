<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_prod_verificables extends Model
{
    protected $table = 'invi_prod_verificables';
    protected $primaryKey = 'id_prod_verifi';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_obj_proy',
        'detalle_prod_verif'
    ];
    public function invi_obj_proyectos()
    {
        return $this->belongsTo(Invi_Obj_Proy::class, 'id_obj_proy');
    }
}
