<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_obj_pei extends Model
{
    protected $table = 'invi_detalle_obj_pei';
    protected $primaryKey = 'id_det_obj_pro_pei';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_obj_pei',
    ];
    public function proyecto()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id', 'proyect_id');
    }
    public function objetivo_pei()
    {
        return $this->belongsTo(Objetivos_pei::class, 'id_obj_pei', 'id_obj_pei');
    }
    

}
