<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campo_detallado_unesco extends Model
{
    use HasFactory;
    protected $table = 'campo_detallado_unesco';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'codigo', 'campo_amplio', 'codigo_amplio', 
        'campo_especifico', 'codigo_especifico', 
        'campo_detallado', 'nivel', 'activo'
    ];
    public function invi_detalle_campo_unesco()
    {
        return $this->hasMany(Invi_detalle_campo_unesco::class, 'id_campo_unesco', 'id');
    }
   

}
