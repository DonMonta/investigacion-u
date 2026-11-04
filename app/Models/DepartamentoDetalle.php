<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartamentoDetalle extends Model
{
    protected $table = 'departamento_detalles';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false; // Ponlo en true si vas a agregar created_at y updated_at a la tabla

    protected $fillable = [
        'id_departamento',
        'sobre_mi',
        'mision',
        'vision'
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }
   
}
