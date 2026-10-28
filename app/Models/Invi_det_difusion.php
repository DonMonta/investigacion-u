<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_det_difusion extends Model
{
    protected $table = 'invi_det_difusion';
    protected $primaryKey = 'id_det_difusion';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_difusion',
        'costo',
        
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function invi_difusion()
    {
        return $this->belongsTo(Invi_difusion::class, 'id_difusion');
    }
}