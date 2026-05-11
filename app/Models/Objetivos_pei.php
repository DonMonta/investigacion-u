<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objetivos_pei extends Model
{
    protected $table = 'objetivos_pei';
    protected $primaryKey = 'id_obj_pei';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_sub_sistema_pei',
        'cod_obj',
        'detalle_obj',
    ];
    public function subsistemas_pei()
    {
        return $this->belongsTo(Subsistemas_pei::class , 'id_sub_sistema_pei');
    }

}