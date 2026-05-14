<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comunidad extends Model
{
    use HasFactory;

    protected $table = 'comunidades';

    protected $fillable = [
        'nombre',
        'direccion',
        'rif',
        'correo',
        'numero_telefono',
        'estado',
        'anio',
        'profesor_id',
        'coordinacion_id'
    ];

    public function coordinacion()
    {
        return $this->belongsTo(Coordinacion::class, 'coordinacion_id');
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function estudiantes()
    {
        return $this->belongsToMany(User::class, 'comunidad_estudiante', 'comunidad_id', 'persona_id')
                    ->withPivot('role_id', 'id')
                    ->withTimestamps();
    }
}
