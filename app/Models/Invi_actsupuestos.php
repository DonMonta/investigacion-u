<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_actsupuestos extends Model
{
    protected $table = 'invi_actsupuestos';
    protected $primaryKey = 'id_supuestos';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_actividades',
        'detalle_supuestos',
    ];
    public function invi_actividades()
    {
        return $this->belongsTo(Invi_actividades::class, 'id_actividades');
    }
}
