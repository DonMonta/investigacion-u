<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_detalle_ods_proyect extends Model
{
    protected $table = 'invi_detalle_ods_proyect';
    protected $primaryKey = 'id_det_ods_pro';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'id_ods',
    ];
    public function proyecto()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id', 'proyect_id');
    }
    public function ods(){
        return $this->belongsTo(ODS::class, 'id_ods');
    }
    

}
