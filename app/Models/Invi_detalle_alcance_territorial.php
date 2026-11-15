<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_alcance_territorial extends Model
{
    protected $table = 'invi_detalle_alcance_territorial';
    protected $primaryKey = 'id_det_alc_terr';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'alcance_id'
    ];
    
    public function invi_alcance_territorial()
    {
        return $this->belongsTo(invi_alcance_territorial::class, 'alcance_id', 'alcance_id');
    }
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }

}
