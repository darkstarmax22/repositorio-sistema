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
        Schema::create('tipo_publicacions', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->tinyInteger('mencion_honorifica')->default(0);
            $table->boolean('estado_logico')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_publicacions');
    }
};
