<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_result_verificables extends Model
{
    protected $table = 'invi_result_verificables';
    protected $primaryKey = 'id_result_verificables';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'estado',
    ];
   
    public function invi_detalle_result_verifi()
    {
        return $this->hasMany(Invi_detalle_result_verifi::class, 'id_result_verificables', 'id_result_verificables');
    }

}
