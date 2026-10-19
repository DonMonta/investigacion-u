<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeiEstadoDepartamento extends Model
{
    protected $table = 'pei_estados_departamentos';
    protected $primaryKey = 'id_pei_estado';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
       'id_pei',
        'id_departamento',
        'estado',
    ];
    public function pei()
    {
        return $this->belongsTo(Pei::class, 'id_pei', 'id_pei');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }
   
}
