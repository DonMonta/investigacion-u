<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_actprod_verificables extends Model
{
    protected $table = 'invi_actprod_verificables';
    protected $primaryKey = 'id_prod_verifi';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_actividades',
        'detalle_prod_verif',
    ];
    public function invi_actividades()
    {
        return $this->belongsTo(Invi_actividades::class, 'id_actividades');
    }
}
