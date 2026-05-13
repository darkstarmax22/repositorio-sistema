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
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'nombre');
            $table->string('apellido')->after('nombre')->nullable();
            $table->char('sexo', 1)->after('apellido')->nullable();
            $table->date('fecha_nacimiento')->after('sexo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('nombre', 'name');
            $table->dropColumn(['apellido', 'sexo', 'fecha_nacimiento']);
        });
    }
};
