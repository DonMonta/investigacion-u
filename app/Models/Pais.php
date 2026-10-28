<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;
    protected $table = 'pais';
    protected $primaryKey = 'cod_pais';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'nomb_pais',
        'nacionalidad_pais',
        'estado',
        'codigo',
    ];
     public function academicosDocente()
    {
        return $this->hasMany(AcademicoDocente::class, 'ad_pais', 'cod_pais');
    }

}
