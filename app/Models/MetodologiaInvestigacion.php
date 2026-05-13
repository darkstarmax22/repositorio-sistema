<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodologiaInvestigacion extends Model
{
    protected $table = 'metodologia_investigacions';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado_logico'
    ];

    protected $casts = [
        'estado_logico' => 'boolean'
    ];
}
