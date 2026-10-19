<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_linea_investigacion extends Model
{
    protected $table = 'invi_linea_investigacion';
    protected $primaryKey = 'id_lin_investiga';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'idfacultad',
        'nombre_lin',
        'estado_lin_investiga'

    ];
    public function facultades()
    {
        return $this->belongsTo(Facultad::class, 'idfacultad');
    }
    public function sub_linea_investigacion()
    {
        return $this->hasMany(Invi_sub_linea_inves::class, 'id_lin_investiga');
    }
    

}
