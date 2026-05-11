<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obj_pol_plandne extends Model
{
    protected $table = 'obj_pol_plandne';
    protected $primaryKey = 'id_obj_pol_pladne';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_pladne',
        'cod_obj_pol',
        'detalle_obj_pol',
    ];
    public function plandne()
    {
        return $this->belongsTo(Plandne::class, 'id_pladne');
    }

}