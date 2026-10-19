<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ODS extends Model
{
    protected $table = 'ods';
    protected $primaryKey = 'id_ods';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_ag_ods',
        'cod_ods',
        'detalle_ods',
    ];
    public function agenda_ods()
    {
        return $this->belongsTo(Agenda_ODS::class, 'id_ag_ods');
    }
    public function invi_detalle_ods_proyect()
    {
        return $this->hasMany(Invi_detalle_ods_proyect::class, 'id_ods');
    }

}