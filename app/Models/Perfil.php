<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'perfil';
    protected $primaryKey = 'idperfil';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'idperfil',
        'nombperfil',
        'nivel',
        'status',
    ];

    // Relación con usuarios (si los usuarios tienen un campo idperfil)
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'idperfil', 'idperfil');
    }
}