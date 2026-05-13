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
            $table->foreignId('id_asignador')->nullable()->after('id_rol')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_rol', function (Blueprint $table) {
            $table->dropForeign(['id_asignador']);
            $table->dropColumn('id_asignador');
        });
    }
};
