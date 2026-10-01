<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inspecciones_cavidades', function (Blueprint $table) {
            if (!Schema::hasColumn('inspecciones_cavidades', 'resina_id')) {
                $table->foreignId('resina_id')->nullable()->after('molde_id')->constrained('resinas')->nullOnDelete();
            }
            if (!Schema::hasColumn('inspecciones_cavidades', 'espesor_pared')) {
                $table->decimal('espesor_pared', 8, 3)->nullable()->after('peso_medido');
            }
            if (!Schema::hasColumn('inspecciones_cavidades', 'espesor_fondo')) {
                $table->decimal('espesor_fondo', 8, 3)->nullable()->after('espesor_pared');
            }
            if (!Schema::hasColumn('inspecciones_cavidades', 'altura')) {
                $table->decimal('altura', 8, 2)->nullable()->after('espesor_fondo');
            }
            if (!Schema::hasColumn('inspecciones_cavidades', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('motivo_scrap');
            }
        });

        // Actualizar la restricción CHECK en PostgreSQL para permitir todos los estados de cavidad
        DB::statement("ALTER TABLE inspecciones_cavidades DROP CONSTRAINT IF EXISTS inspecciones_cavidades_estado_check;");
        DB::statement("ALTER TABLE inspecciones_cavidades ADD CONSTRAINT inspecciones_cavidades_estado_check CHECK (estado IN ('CONFORME', 'FUERA_DE_RANGO', 'OBSERVADO', 'PASABLE'));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE inspecciones_cavidades DROP CONSTRAINT IF EXISTS inspecciones_cavidades_estado_check;");

        Schema::table('inspecciones_cavidades', function (Blueprint $table) {
            if (Schema::hasColumn('inspecciones_cavidades', 'observaciones')) {
                $table->dropColumn('observaciones');
            }
            if (Schema::hasColumn('inspecciones_cavidades', 'altura')) {
                $table->dropColumn('altura');
            }
            if (Schema::hasColumn('inspecciones_cavidades', 'espesor_fondo')) {
                $table->dropColumn('espesor_fondo');
            }
            if (Schema::hasColumn('inspecciones_cavidades', 'espesor_pared')) {
                $table->dropColumn('espesor_pared');
            }
            if (Schema::hasColumn('inspecciones_cavidades', 'resina_id')) {
                $table->dropForeign(['resina_id']);
                $table->dropColumn('resina_id');
            }
        });
    }
};
