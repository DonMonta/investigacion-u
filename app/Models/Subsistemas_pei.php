<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subsistemas_pei extends Model
{
    protected $table = 'subsistemas_pei';
    protected $primaryKey = 'id_sub_sistema_pei';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_pei',
        'nombre_subsistema',
    ];
    public function pei()
    {
        return $this->belongsTo(Pei::class , 'id_pei');
    }

}