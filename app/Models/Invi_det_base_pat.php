<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_det_base_pat extends Model
{
    protected $table = 'Invi_det_base_pat';
    protected $primaryKey = 'id_det_base_pat';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_base_pat'
    ];
    
    public function invi_base_pat()
    {
        return $this->belongsTo(Invi_base_pat::class, 'id_base_pat', 'id_base_pat');
    }
    public function invi_proyectos()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id');
    }

}
