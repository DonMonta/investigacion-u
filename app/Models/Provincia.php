<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    use HasFactory;
    protected $table = 'provincia';
    protected $primaryKey = 'id_provincia';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'codigo',
        'detalle',
        'region',
    ];
    public function detalle_zona_planificacion()
    {
        return $this->hasMany(Detalle_zona_planificacion::class, 'id_provincia');
    }
    public function invi_detalle_cobe()
    {
        return $this->hasMany(Invi_detalle_cobe::class, 'id_provincia');
    }
    public function cantones()
    {
        return $this->hasMany(Canton::class, 'codigo');
    }
    public function invi_informe_beneficiarios()
    {
        return $this->hasMany(Invi_informe_beneficiarios::class, 'id_provincia');
    }

}
