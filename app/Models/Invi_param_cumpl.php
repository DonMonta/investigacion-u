<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_param_cumpl extends Model
{
    protected $table = 'invi_param_cumpl';
    protected $primaryKey = 'id_param_cumpl';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'estado',
    ];
   
    public function invi_detalle_param_cumpl()
    {
        return $this->hasMany(Invi_detalle_param_cumpl::class, 'id_param_cumpl', 'id_param_cumpl');
    }

}
