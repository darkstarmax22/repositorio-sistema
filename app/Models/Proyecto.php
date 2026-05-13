<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $table = 'proyectos';

    protected $fillable = [
        'titulo',
        'resumen',
        'fecha_subida',
        'asignacion_ct',
        'calificacion',
        'fecha_aprobacion',
        'linea_investigacion_id',
        'metodologia_id',
        'tipo_publicacion_id',
        'tipo_investigacion_id',
        'lapso_academico_id',
        'estado_logico',
        'archivo_path',
        'estado_validacion',
        'motivo_rechazo',
        'validador_id',
        'persona_id',
        'coordinacion_id',
        'trayecto',
        'comunidad_id'
    ];

    protected $casts = [
        'fecha_subida' => 'date',
        'fecha_aprobacion' => 'date',
        'estado_logico' => 'boolean',
        'asignacion_ct' => 'boolean',
        'calificacion' => 'integer'
    ];

    public function user() { return $this->belongsTo(User::class, 'persona_id'); }
    public function linea_investigacion() { return $this->belongsTo(LineaInvestigacion::class); }
    public function metodologia() { return $this->belongsTo(MetodologiaInvestigacion::class, 'metodologia_id'); }
    public function tipo_publicacion() { return $this->belongsTo(TipoPublicacion::class); }
    public function tipo_investigacion() { return $this->belongsTo(TipoInvestigacion::class); }
    public function lapso_academico() { return $this->belongsTo(LapsoAcademico::class); }
    public function validador() { return $this->belongsTo(\App\Models\User::class, 'validador_id'); }
    public function coordinacion()
    {
        return $this->belongsTo(Coordinacion::class);
    }
    
    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'comunidad_id');
    }
}
