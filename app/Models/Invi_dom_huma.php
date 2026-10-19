<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_dom_huma extends Model
{
    protected $table = 'invi_dom_huma';
    protected $primaryKey = 'id_dom_huma';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'detalle_dom_huma'
    ];
    public function invi_detalle_dom_hum()
    {
        return $this->hasMany(Invi_detalle_dom_hum::class, 'id_dom_hum');
    }
    

}
