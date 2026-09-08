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
        Schema::table('pnc', function (Blueprint $table) {
            if (!Schema::hasColumn('pnc', 'cantidad_2')) {
                $table->decimal('cantidad_2', 12, 2)->nullable()->after('unidad_medida_2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pnc', function (Blueprint $table) {
            if (Schema::hasColumn('pnc', 'cantidad_2')) {
                $table->dropColumn('cantidad_2');
            }
        });
    }
};
