<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Politicas_pei extends Model
{
    protected $table = 'politicas_pei';
    protected $primaryKey = 'id_pol_pei';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_pei',
        'nombre_pol',
        'detalle_pol',
    ];
    public function pei()
    {
        return $this->belongsTo(Pei::class , 'id_pei');
    }
    public function invi_detalle_pol_pei()
    {
        return $this->belongsTo(Invi_detalle_pol_pei::class, 'id_pol_pei', 'id_pol_pei');
    }

}