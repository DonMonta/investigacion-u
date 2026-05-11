<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pei extends Model
{
    protected $table = 'pei';
    protected $primaryKey = 'id_pei';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_pei',
        'anios_pei',
        'estado_pei',
        'archivo_pei'
    ];
    public function subsistemas_pei()
    {
        return $this->hasMany(Subsistemas_pei::class, 'id_pei');
    }
    public function objetivos()
    {
        return $this->hasManyThrough(
            Objetivos_pei::class,
            Subsistemas_pei::class,
            'id_pei',               // Llave foránea en subsistemas_pei
            'id_sub_sistema_pei',   // Llave foránea en objetivos_pei
            'id_pei',               // Llave local en pei
            'id_sub_sistema_pei'    // Llave local en subsistemas_pei
        );
    }
}
