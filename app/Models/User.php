<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'persona';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'sexo',
        'fecha_nacimiento',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'detalle_rol', 'persona_id', 'id_rol')
                    ->withPivot('id_asignador', 'estado_logico', 'coordinacion_id', 'anio')
                    ->withTimestamps();
    }

    public function asignador()
    {
        return $this->belongsTo(User::class, 'id_asignador');
    }

    public function hasRole(...$roles)
    {
        return $this->roles()
                    ->whereIn('nombre', $roles)
                    ->where('estado_logico', true)
                    ->exists() || 
               $this->roles()
                    ->where('nombre', 'administrador')
                    ->where('estado_logico', true)
                    ->exists();
    }

    public function comunidades()
    {
        return $this->belongsToMany(Comunidad::class, 'comunidad_estudiante', 'persona_id', 'comunidad_id')
                    ->withPivot('role_id', 'id')
                    ->withTimestamps();
    }
}
