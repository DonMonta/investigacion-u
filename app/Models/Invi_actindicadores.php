<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_actindicadores extends Model
{
    protected $table = 'invi_actindicadores';
    protected $primaryKey = 'id_indicador';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_actividades',
        'detalle_indicador',
    ];
    public function invi_actividades()
    {
        return $this->belongsTo(Invi_actividades::class, 'id_actividades');
    }
}
