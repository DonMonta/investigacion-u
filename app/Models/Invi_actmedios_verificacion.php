<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_actmedios_verificacion extends Model
{
    protected $table = 'invi_actmedios_verificacion';
    protected $primaryKey = 'id_medios_verifi';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_actividades',
        'detalle_medio_verifica',
    ];
    public function invi_actividades()
    {
        return $this->belongsTo(Invi_actividades::class, 'id_actividades');
    }
}
