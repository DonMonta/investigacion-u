<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_responsable extends Model
{
    protected $table = 'invi_responsable';
    protected $primaryKey = 'id_responsable';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ciinfper_doc',
        'tipo_responsable',
        'estado_responsable',
        'idfacultad',
        'evidencia_arch'
    ];
    
    public function informacionpersonald()
    {
        return $this->belongsTo(InformacionPersonald::class, 'ciinfper_doc', 'CIInfPer');
    }
    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'idfacultad');
    }
    

}