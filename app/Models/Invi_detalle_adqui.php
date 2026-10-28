<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_adqui extends Model
{
    protected $table = 'invi_detalle_adqui';
    protected $primaryKey = 'id_det_adqui';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_adquisicion',
        
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function invi_adquisicion()
    {
        return $this->belongsTo(Invi_adquisicion::class, 'id_adquisicion');
    }
}