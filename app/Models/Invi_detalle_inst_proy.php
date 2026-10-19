<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_inst_proy extends Model
{
    protected $table = 'invi_detalle_inst_proy';
    protected $primaryKey = 'id_det_instituciones';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'idempresa',
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function praempresas()
    {
        return $this->belongsTo(Praempresa::class, 'idempresa');
    }

}