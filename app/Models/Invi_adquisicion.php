<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_adquisicion extends Model
{
    protected $table = 'invi_adquisicion';
    protected $primaryKey = 'id_adquisicion';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'tipo_adqui',
        'detalle',
        'porcent_nacio',
        'detalle_iinsu_nac',
        'porcent_importado',
        'detalle_insu_import',
    ];
    public function invi_detalle_adqui()
    {
        return $this->hasMany(Invi_detalle_adqui::class, 'id_adquisicion');
    }
    

}
