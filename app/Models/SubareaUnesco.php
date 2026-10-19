<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubareaUnesco extends Model
{
    use HasFactory;
    protected $table = 'subarea_unesco';
    protected $primaryKey = 'sau_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = [
        'sau_pdid',
        'sau_descripcion',
    ];
    public function detalle_area_unesco()
    {
        return $this->hasMany(Invi_detalle_area_unesco::class, 'id_subarea_unesco');
    }
   

}
