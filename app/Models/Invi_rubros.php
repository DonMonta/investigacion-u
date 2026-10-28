<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_rubros extends Model
{
    protected $table = 'invi_rubros';
    protected $primaryKey = 'id_rubro';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_rubro',
    ];
    public function invi_detalle_financia()
    {
        return $this->hasMany(Invi_detalle_financia::class, 'id_rubro');
    }
    

}
