<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlandneEstadoDepartamento extends Model
{
    protected $table = 'plandne_estados_departamentos';
    protected $primaryKey = 'id_plandne_estado';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_pladne',
        'id_departamento',
        'estado',
    ];
    public function plandne()
    {
        return $this->belongsTo(Plandne::class, 'id_pladne', 'id_pladne');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }
}
