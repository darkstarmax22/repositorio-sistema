<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LapsoAcademico extends Model
{
    protected $table = 'lapso_academicos';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado_lapso'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado_lapso' => 'boolean'
    ];
}
