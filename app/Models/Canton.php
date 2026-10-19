<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canton extends Model
{
    use HasFactory;
    protected $table = 'canton';
    protected $primaryKey = 'id_canton';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'codigo',
        'detalle',
        'codigoprovincia',
    ];
    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'codigoprovincia');
    }
    public function invi_detalle_cobe()
    {
        return $this->hasMany(Invi_detalle_cobe::class, 'id_canton');
    }
    public function parroquias()
    {
        return $this->hasMany(Parroquia::class, 'codigo');
    }
    public function invi_informe_beneficiarios()
    {
        return $this->hasMany(Invi_informe_beneficiarios::class, 'id_canton');
    }

}
