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
        Schema::table('comunidades', function (Blueprint $table) {
            $table->string('representante')->nullable()->after('nombre');
            $table->string('numero_fijo')->nullable()->after('representante');
            $table->string('correo')->nullable()->after('numero_fijo');
            $table->string('rif')->nullable()->unique()->after('correo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comunidades', function (Blueprint $table) {
            $table->dropColumn(['representante', 'numero_fijo', 'correo', 'rif']);
        });
    }
};
