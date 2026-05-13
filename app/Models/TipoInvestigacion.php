<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoInvestigacion extends Model
{
    protected $table = 'tipo_investigacions';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado_logico'
    ];

    protected $casts = [
        'estado_logico' => 'boolean'
    ];
}
