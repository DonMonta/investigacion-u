<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $table = 'sede';
    protected $primaryKey = 'idsede';
    public $incrementing = false; // La PK no es auto-incremental
    protected $keyType = 'int';

    protected $fillable = [
        'idsede',
        'sede',
        'sedeglobal',
        'direccion',
        'codigocampusceaaces',
        'coordinador',
        'cargo',
    ];

    protected $casts = [
        'idsede' => 'integer',
    ];

    // Relaciones si aplican
    public function facultades()
    {
        return $this->hasMany(Facultad::class, 'idsede');
    }
    
}