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
        Schema::table('proyectos', function (Blueprint $row) {
            $row->foreignId('coordinacion_id')->nullable()->constrained('coordinaciones')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $row) {
            $row->dropForeign(['coordinacion_id']);
            $row->dropColumn('coordinacion_id');
        });
    }
};
