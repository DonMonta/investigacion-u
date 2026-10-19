<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invi_informe_beneficiarios extends Model
{
    protected $table = 'invi_informe_beneficiarios';
    protected $primaryKey = 'id_informe_beneficiarios';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'informe_id',
        'tipo_beneficiario',
        'nombres_apellidos',
        'cedula_identidad',
        'sexo',
        'edad',
        'tiene_discapacidad',
        'tipo_grado_discapacidad',
        'pueblo_nacionalidad',
        'movilidad_humana',
        'id_provincia',
        'id_canton',
        'idparroquia',
        'grupo_comunidad_entidad',
        'cantidad_personas',
        'beneficio_generado',
    ];
    public function invi_informes()
    {
        return $this->belongsTo(Invi_informes::class, 'informe_id', 'id_informes');
    }
    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'id_provincia', 'id_provincia');
    }
    public function canton()
    {
        return $this->belongsTo(Canton::class, 'id_canton', 'id_canton');
    }
    public function parroquia()
    {
        return $this->belongsTo(Parroquia::class, 'idparroquia', 'idparroquia');
    }

}
