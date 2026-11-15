<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_result_verifi extends Model
{
    protected $table = 'invi_detalle_result_verifi';
    protected $primaryKey = 'id_det_fuen_fin';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_fuente_finan'
    ];
    
    public function invi_result_verificables()
    {
        return $this->belongsTo(Invi_result_verificables::class, 'id_result_verificables', 'id_result_verificables');
    }
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }

}
