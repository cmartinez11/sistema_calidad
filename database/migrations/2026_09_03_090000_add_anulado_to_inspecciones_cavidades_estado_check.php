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
            // Actualizar la restricción CHECK en PostgreSQL/MySQL para permitir el estado 'ANULADO'
            DB::statement("ALTER TABLE inspecciones_cavidades DROP CONSTRAINT IF EXISTS inspecciones_cavidades_estado_check;");
            DB::statement("ALTER TABLE inspecciones_cavidades ADD CONSTRAINT inspecciones_cavidades_estado_check CHECK (estado IN ('CONFORME', 'FUERA_DE_RANGO', 'OBSERVADO', 'PASABLE', 'ANULADO'));");
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
            DB::statement("ALTER TABLE inspecciones_cavidades DROP CONSTRAINT IF EXISTS inspecciones_cavidades_estado_check;");
            DB::statement("ALTER TABLE inspecciones_cavidades ADD CONSTRAINT inspecciones_cavidades_estado_check CHECK (estado IN ('CONFORME', 'FUERA_DE_RANGO', 'OBSERVADO', 'PASABLE'));");
        } catch (\Throwable $e) {
            // Manejo de compatibilidad
        }
    }
};
