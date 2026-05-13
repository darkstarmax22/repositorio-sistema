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
        Schema::table('linea_investigacions', function (Blueprint $table) {
            $table->dropColumn('codigo_departamento');
            $table->foreignId('coordinacion_id')->nullable()->constrained('coordinaciones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('linea_investigacions', function (Blueprint $table) {
            $table->dropForeign(['coordinacion_id']);
            $table->dropColumn('coordinacion_id');
            $table->string('codigo_departamento')->nullable();
        });
    }
};
