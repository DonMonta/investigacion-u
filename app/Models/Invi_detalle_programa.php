<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_programa extends Model
{
    protected $table = 'invi_detalle_programa';
    protected $primaryKey = 'id_det_prog';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_programa'
    ];
    
    public function invi_programa()
    {
        return $this->belongsTo(Invi_programa::class, 'id_programa', 'id_programa');
    }
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }

}
