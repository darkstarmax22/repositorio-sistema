<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LineaInvestigacion extends Model
{
    protected $fillable = [
        'nombre_investigacion',
        'descripcion',
        'area_de_investigacion',
        'coordinacion_id',
        'activo'
    ];

    public function coordinacion()
    {
        return $this->belongsTo(Coordinacion::class);
    }
}
