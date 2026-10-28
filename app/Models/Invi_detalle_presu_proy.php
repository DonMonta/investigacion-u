<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_presu_proy extends Model
{
    protected $table = 'invi_detalle_presu_proy';
    protected $primaryKey = 'id_det_presupuesto';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_aportes_utlvt',
        'id_aportes_inst',
        'total_presupuesto'
    ];
    public function invi_proyecto()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id', 'proyect_id');
    }
    public function invi_aportesutlvt()
    {
        return $this->belongsTo(Invi_aportesutlvt::class, 'id_aportes_utlvt', 'id_aportes_utlvt');
    }
    public function invi_aportesinst()
    {
        return $this->belongsTo(Invi_aportesinst::class, 'id_aportes_inst', 'id_aportes_inst');
    }
}
