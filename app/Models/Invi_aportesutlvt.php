<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_aportesutlvt extends Model
{
    protected $table = 'invi_aportesutlvt';
    protected $primaryKey = 'id_aportes_utlvt';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'actividad',
        'valor'
    ];
    public function invi_detalle_presu_proy()
    {
        return $this->hasMany(Invi_Detalle_presu_proy::class, 'id_aportes_utlvt');
    }
    

}
