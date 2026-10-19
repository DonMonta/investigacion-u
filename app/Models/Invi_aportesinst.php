<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_aportesinst extends Model
{
    protected $table = 'invi_aportesinst';
    protected $primaryKey = 'id_aportes_inst';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'idempresa',
        'actividad',
        'valor'
    ];
    public function praempresa()
    {
        return $this->belongsTo(Praempresa::class, 'idempresa', 'idempresa');
    }
    public function invi_detalle_presu_proy()
    {
        return $this->hasMany(Invi_detalle_presu_proy::class, 'id_aportes_inst');
    }

}
