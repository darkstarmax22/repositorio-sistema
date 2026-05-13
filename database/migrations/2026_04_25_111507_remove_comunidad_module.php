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

        if (Schema::hasColumn('proyectos', 'comunidad_id')) {
            Schema::table('proyectos', function (Blueprint $table) {
                // Ignore errors if foreign key doesn't exist
                try {
                    $table->dropForeign(['comunidad_id']);
                } catch (\Exception $e) {
                    // silent
                }
                $table->dropColumn('comunidad_id');
            });
        }

        Schema::dropIfExists('comunidades');
        
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration logic easily restorable as data is lost.
    }
};
