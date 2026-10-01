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
            if (Schema::hasColumn('pnc', 'unidad_medida_2')) {
                $table->dropColumn('unidad_medida_2');
            }
            if (Schema::hasColumn('pnc', 'cantidad_2')) {
                $table->dropColumn('cantidad_2');
            }
            if (!Schema::hasColumn('pnc', 'total_millares')) {
                $table->decimal('total_millares', 12, 4)->nullable()->after('unidad_medida');
            }
            if (!Schema::hasColumn('pnc', 'total_peso_kg')) {
                $table->decimal('total_peso_kg', 12, 4)->nullable()->after('total_millares');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pnc', function (Blueprint $table) {
            if (Schema::hasColumn('pnc', 'total_millares')) {
                $table->dropColumn('total_millares');
            }
            if (Schema::hasColumn('pnc', 'total_peso_kg')) {
                $table->dropColumn('total_peso_kg');
            }
            if (!Schema::hasColumn('pnc', 'unidad_medida_2')) {
                $table->string('unidad_medida_2', 50)->nullable();
            }
            if (!Schema::hasColumn('pnc', 'cantidad_2')) {
                $table->decimal('cantidad_2', 12, 2)->nullable();
            }
        });
    }
};
