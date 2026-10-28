<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_carr_proy extends Model
{
    protected $table = 'invi_detalle_carr_proy';
    protected $primaryKey = 'id_det_carr';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_carr',
        'id_carr_priori',
    ];
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }
    public function carreras()
    {
        return $this->belongsTo(Carreras::class, 'id_carr');
    }
    public function carreras_priori()
    {
        return $this->belongsTo(Carreras::class, 'id_carr_priori');
    }

}