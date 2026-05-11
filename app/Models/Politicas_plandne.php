<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Politicas_plandne extends Model
{
    protected $table = 'politicas_plandne';
    protected $primaryKey = 'id_pol_pladne';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_obj_pol_pladne',
        'cod_pol',
        'detalle_pol',
    ];
    public function objetivos_plandne()
    {
        return $this->belongsTo(Obj_pol_plandne::class , 'id_obj_pol_pladne');
    }

}