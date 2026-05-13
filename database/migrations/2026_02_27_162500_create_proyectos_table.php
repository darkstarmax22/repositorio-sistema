<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('resumen');
            $table->date('fecha_subida');
            $table->tinyInteger('asignacion_ct')->default(0);
            $table->tinyInteger('calificacion')->nullable();
            $table->date('fecha_aprobacion')->nullable();
            $table->string('direccion_logica')->nullable();
            
            // Relaciones
            $table->foreignId('comunidad_id')->constrained('comunidades');
            $table->foreignId('linea_investigacion_id')->constrained('linea_investigacions');
            $table->foreignId('metodologia_id')->constrained('metodologia_investigacions');
            $table->foreignId('tipo_publicacion_id')->constrained('tipo_publicacions');
            $table->foreignId('tipo_investigacion_id')->constrained('tipo_investigacions');
            $table->foreignId('lapso_academico_id')->constrained('lapso_academicos');
            
            $table->boolean('estado_logico')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
