<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_convocatoria extends Model
{
    protected $table = 'invi_convocatoria';
    protected $primaryKey = 'id_convocatoria';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'num_convocatoria',
        'titulo_convocatoria',
        'elaboracion',
        'revision',
        'aprobacion',
        'fecha_inicio',
        'fecha_fin',
        'num_resolucion',
        'estado',
        'archivo',
        'tipo_convocatoria',
        'habilitar_edicion',

    ];
    public function invi_proyectos()
    {
        return $this->hasMany(Invi_proyectos::class, 'id_convocatoria');
    }
    
    

}
