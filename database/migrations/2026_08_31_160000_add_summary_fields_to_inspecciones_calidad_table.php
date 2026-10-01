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
        Schema::table('inspecciones_calidad', function (Blueprint $table) {
            if (!Schema::hasColumn('inspecciones_calidad', 'codigo_inspeccion')) {
                $table->string('codigo_inspeccion', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('inspecciones_calidad', 'producto_id')) {
                $table->foreignId('producto_id')->nullable()->after('codigo_inspeccion')->constrained('productos')->nullOnDelete();
            }
            if (!Schema::hasColumn('inspecciones_calidad', 'molde_id')) {
                $table->foreignId('molde_id')->nullable()->after('maquina_id')->constrained('molde')->nullOnDelete();
            }
            if (!Schema::hasColumn('inspecciones_calidad', 'resina_id')) {
                $table->foreignId('resina_id')->nullable()->after('molde_id')->constrained('resinas')->nullOnDelete();
            }
            if (!Schema::hasColumn('inspecciones_calidad', 'esp_pared_min')) {
                $table->decimal('esp_pared_min', 8, 3)->nullable()->after('peso_max');
            }
            if (!Schema::hasColumn('inspecciones_calidad', 'esp_pared_max')) {
                $table->decimal('esp_pared_max', 8, 3)->nullable()->after('esp_pared_min');
            }
            if (!Schema::hasColumn('inspecciones_calidad', 'esp_fondo_min')) {
                $table->decimal('esp_fondo_min', 8, 3)->nullable()->after('esp_pared_max');
            }
            if (!Schema::hasColumn('inspecciones_calidad', 'esp_fondo_max')) {
                $table->decimal('esp_fondo_max', 8, 3)->nullable()->after('esp_fondo_min');
            }
            if (!Schema::hasColumn('inspecciones_calidad', 'altura_min')) {
                $table->decimal('altura_min', 8, 2)->nullable()->after('esp_fondo_max');
            }
            if (!Schema::hasColumn('inspecciones_calidad', 'altura_max')) {
                $table->decimal('altura_max', 8, 2)->nullable()->after('altura_min');
            }
            if (!Schema::hasColumn('inspecciones_calidad', 'motivo_scrap')) {
                $table->string('motivo_scrap', 255)->nullable()->after('estado_evaluacion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspecciones_calidad', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->dropForeign(['molde_id']);
            $table->dropForeign(['resina_id']);
            $table->dropColumn([
                'codigo_inspeccion',
                'producto_id',
                'molde_id',
                'resina_id',
                'esp_pared_min',
                'esp_pared_max',
                'esp_fondo_min',
                'esp_fondo_max',
                'altura_min',
                'altura_max',
                'motivo_scrap'
            ]);
        });
    }
};
