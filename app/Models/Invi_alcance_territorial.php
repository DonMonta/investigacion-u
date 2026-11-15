<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_alcance_territorial extends Model
{
    protected $table = 'invi_alcance_territorial';
    protected $primaryKey = 'alcance_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'estado',
    ];
   
    public function invi_detalle_alcance_territorial()
    {
        return $this->hasMany(Invi_detalle_alcance_territorial::class, 'alcance_id', 'alcance_id');
    }

}
