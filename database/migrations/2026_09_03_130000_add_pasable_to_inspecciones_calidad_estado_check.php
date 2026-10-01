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
        try {
            // Actualizar la restricción CHECK en PostgreSQL/MySQL para permitir los estados CONFORME, PASABLE, OBSERVADO, OBSERVADO_PNC y PNC
            DB::statement("ALTER TABLE inspecciones_calidad DROP CONSTRAINT IF EXISTS inspecciones_calidad_estado_evaluacion_check;");
            DB::statement("ALTER TABLE inspecciones_calidad ADD CONSTRAINT inspecciones_calidad_estado_evaluacion_check CHECK (estado_evaluacion IN ('CONFORME', 'PASABLE', 'OBSERVADO', 'OBSERVADO_PNC', 'PNC'));");
        } catch (\Throwable $e) {
            // Manejo de compatibilidad en entornos sin restricción CHECK estricta
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE inspecciones_calidad DROP CONSTRAINT IF EXISTS inspecciones_calidad_estado_evaluacion_check;");
            DB::statement("ALTER TABLE inspecciones_calidad ADD CONSTRAINT inspecciones_calidad_estado_evaluacion_check CHECK (estado_evaluacion IN ('CONFORME', 'OBSERVADO', 'OBSERVADO_PNC', 'PNC'));");
        } catch (\Throwable $e) {
            // Manejo de compatibilidad
        }
    }
};
