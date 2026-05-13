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
        Schema::dropIfExists('documentos');
        Schema::dropIfExists('proyectos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse migration for weight reduction
    }
};
