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
        Schema::disableForeignKeyConstraints();

        // 1. Drop foreign keys and rename columns in detalle_rol
        Schema::table('detalle_rol', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
            $table->renameColumn('id_user', 'persona_id');
            // Drop id_asignador foreign key
            $table->dropForeign(['id_asignador']);
        });

        // 2. Drop foreign keys and rename columns in auditorias
        Schema::table('auditorias', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->renameColumn('user_id', 'persona_id');
        });

        // 3. Drop foreign keys and rename columns in proyectos
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
            $table->renameColumn('id_user', 'persona_id');
            $table->dropForeign(['validador_id']);
        });

        // 4. Rename the table
        Schema::rename('users', 'persona');

        // 5. Restore foreign keys
        Schema::table('detalle_rol', function (Blueprint $table) {
            $table->foreign('persona_id')->references('id')->on('persona')->onDelete('cascade');
            $table->foreign('id_asignador')->references('id')->on('persona')->onDelete('set null');
        });

        Schema::table('auditorias', function (Blueprint $table) {
            $table->foreign('persona_id')->references('id')->on('persona')->onDelete('cascade');
        });

        Schema::table('proyectos', function (Blueprint $table) {
            $table->foreign('persona_id')->references('id')->on('persona')->onDelete('cascade');
            $table->foreign('validador_id')->references('id')->on('persona')->nullOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('detalle_rol', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->renameColumn('persona_id', 'id_user');
            $table->dropForeign(['id_asignador']);
        });

        Schema::table('auditorias', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->renameColumn('persona_id', 'user_id');
        });

        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->renameColumn('persona_id', 'id_user');
            $table->dropForeign(['validador_id']);
        });

        Schema::rename('persona', 'users');

        Schema::table('detalle_rol', function (Blueprint $table) {
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_asignador')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('auditorias', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('proyectos', function (Blueprint $table) {
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('validador_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }
};
