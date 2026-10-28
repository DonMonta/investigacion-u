<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_Obj_Proy extends Model
{
    protected $table = 'invi_obj_proy';
    protected $primaryKey = 'id_obj_proy';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'proyect_id',
        'detalle_obj_proy',
        'tipo_obj_proy'
    ];
    public function invi_proyecto()
    {
        return $this->belongsTo(Invi_proyectos::class, 'proyect_id', 'proyect_id');
    }
    public function invi_indicadores()
    {
        return $this->hasMany(Invi_indicadores::class, 'id_obj_proy');
    }
    public function invi_metas()
    {
        return $this->hasMany(Invi_metas::class, 'id_obj_proy');
    }
    public function invi_supuestos()
    {
        return $this->hasMany(Invi_supuestos::class, 'id_obj_proy');
    }
    public function invi_medios_verificacion()
    {
        return $this->hasMany(Invi_medios_verificacion::class, 'id_obj_proy');
    }
    public function invi_prod_verificables()
    {
        return $this->hasMany(Invi_prod_verificables::class, 'id_obj_proy');
    }
    public function invi_actividades()
    {
        return $this->hasMany(Invi_actividades::class, 'id_obj_proy');
    }
}
