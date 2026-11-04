<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_pol_pei extends Model
{
    protected $table = 'invi_detalle_pol_pei';
    protected $primaryKey = 'id_det_pol_pro_pei';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_pol_pei',
    ];
    public function proyecto()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id', 'proyect_id');
    }
    public function politicas_pei()
    {
        return $this->belongsTo(Politicas_pei::class, 'id_pol_pei', 'id_pol_pei');
    }
    

}
