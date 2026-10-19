<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parroquia extends Model
{
    use HasFactory;
    protected $table = 'parroquia';
    protected $primaryKey = 'idparroquia';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'parroquia',
        'codigoparroquia',
        'codigocanton',
        'tipoparroquia'
    ];
    public function parroquia()
    {
        return $this->belongsTo(Parroquia::class, 'codigoparroquia');
    }
    public function canton()
    {
        return $this->belongsTo(Canton::class, 'codigocanton');
    }
    public function invi_detalle_cobe()
    {
        return $this->hasMany(Invi_detalle_cobe::class, 'id_parroquia');
    }
    public function invi_informe_beneficiarios()
    {
        return $this->hasMany(Invi_informe_beneficiarios::class, 'id_parroquia');
    }
   

}
