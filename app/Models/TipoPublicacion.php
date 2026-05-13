<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPublicacion extends Model
{
    protected $table = 'tipo_publicacions';

    protected $fillable = [
        'nombre',
        'mencion_honorifica',
        'estado_logico'
    ];

    protected $casts = [
        'estado_logico' => 'boolean',
        'mencion_honorifica' => 'boolean'
    ];
}
