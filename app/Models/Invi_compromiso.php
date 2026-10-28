<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_compromiso extends Model
{
    protected $table = 'invi_compromiso';
    protected $primaryKey = 'id_compromiso';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_deta_invi_proyect',
        'detalle_compromiso'
    ];
    public function invi_detalle_integrante(){
        return $this->belongsTo(Invi_deta_inte::class, 'id_deta_invi_proyect');
    }
    

}
