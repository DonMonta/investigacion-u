<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_sub_linea_inves extends Model
{
    protected $table = 'invi_sub_linea_inves';
    protected $primaryKey = 'id_sublin_investiga';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_lin_investiga',
        'idCarr',
        'nombre_sublin',
        'objeto_estudio_sublin',
    ];
    public function linea_investigacion()
    {
        return $this->belongsTo(Invi_linea_investigacion::class, 'id_lin_investiga');
    }
    public function carreras()
    {
        return $this->belongsTo(Carreras::class, 'idCarr');
    }
    public function detalle_sublineas_investigacion()
    {
        return $this->hasMany(Invi_detalle_lin_inves::class, 'id_sublin_investiga');
    }
    

}
