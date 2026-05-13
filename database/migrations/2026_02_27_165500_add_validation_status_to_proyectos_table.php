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
        Schema::table('proyectos', function (Blueprint $table) {
            $table->enum('estado_validacion', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente')->after('estado_logico');
            $table->text('motivo_rechazo')->nullable()->after('estado_validacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn(['estado_validacion', 'motivo_rechazo']);
        });
    }
};
