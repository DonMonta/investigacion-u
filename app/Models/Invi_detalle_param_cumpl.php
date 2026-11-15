<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_param_cumpl extends Model
{
    protected $table = 'invi_detalle_param_cumpl';
    protected $primaryKey = 'id_det_param_cumpl';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_param_cumpl'
    ];
    
    public function invi_param_cumpl()
    {
        return $this->belongsTo(Invi_param_cumpl::class, 'id_param_cumpl', 'id_param_cumpl');
    }
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }

}
