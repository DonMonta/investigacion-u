<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_base_pat extends Model
{
    protected $table = 'Invi_base_pat';
    protected $primaryKey = 'id_base_pat';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_base',
        'denominacion',                     
        'no_patente',
        'otros',
    ];
    
   
     public function invi_det_base_pat()
    {
        return $this->hasMany(Invi_det_base_pat::class, 'id_base_pat', 'id_base_pat');
    }

}
