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
        Schema::table('detalle_rol', function (Blueprint $table) {
            $table->foreignId('coordinacion_id')->nullable()->constrained('coordinaciones')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_rol', function (Blueprint $table) {
            $table->dropForeign(['coordinacion_id']);
            $table->dropColumn('coordinacion_id');
        });
    }
};
