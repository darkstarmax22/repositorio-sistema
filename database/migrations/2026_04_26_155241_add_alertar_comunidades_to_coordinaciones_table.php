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
        Schema::table('coordinaciones', function (Blueprint $table) {
            $table->boolean('alertar_comunidades')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coordinaciones', function (Blueprint $table) {
            $table->dropColumn('alertar_comunidades');
        });
    }
};
