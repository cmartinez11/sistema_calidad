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
        Schema::table('inspecciones_cavidades', function (Blueprint $table) {
            if (Schema::hasColumn('inspecciones_cavidades', 'inspeccion_id')) {
                $table->dropColumn('inspeccion_id');
            }
            $table->string('codigo_inspeccion', 50)->nullable()->index()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspecciones_cavidades', function (Blueprint $table) {
            $table->dropColumn('codigo_inspeccion');
            $table->foreignId('inspeccion_id')->nullable()->constrained('inspecciones_calidad')->onDelete('cascade');
        });
    }
};
