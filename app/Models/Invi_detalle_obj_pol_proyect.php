<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_obj_pol_proyect extends Model
{
    protected $table = 'invi_detalle_obj_pol_proyect';
    protected $primaryKey = 'id_det_pol_pro';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_pol_pladne',
    ];
    public function proyecto()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id', 'proyect_id');
    }
    public function politicas_plandne()
    {
        return $this->belongsTo(Politicas_plandne::class, 'id_pol_pladne', 'id_pol_pladne');
    }
    

}
