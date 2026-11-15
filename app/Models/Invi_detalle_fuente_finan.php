<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_fuente_finan extends Model
{
    protected $table = 'invi_detalle_fuente_finan';
    protected $primaryKey = 'id_det_fuen_fin';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_fuente_finan'
    ];
    
    public function invi_fuente_finan()
    {
        return $this->belongsTo(Invi_fuente_finan::class, 'id_fuente_finan', 'id_fuente_finan');
    }
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }

}
