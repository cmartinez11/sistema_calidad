<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Actualizar registros existentes con estados obsoletos ('PASABLE', 'OBSERVADO_PNC') a 'OBSERVADO'
        DB::table('inspecciones_calidad')
            ->whereIn('estado_evaluacion', ['PASABLE', 'OBSERVADO_PNC'])
            ->update(['estado_evaluacion' => 'OBSERVADO']);

        // 2. Recalcular registros donde todas las cavidades estén conformes para asegurar rigor
        $inspecciones = DB::table('inspecciones_calidad')->get();

        foreach ($inspecciones as $insp) {
            if ($insp->estado_evaluacion === 'PNC') {
                continue; // No alterar registros que hayan sido derivados a PNC
            }

            if (!empty($insp->codigo_inspeccion)) {
                $cavidades = DB::table('inspecciones_cavidades')
                    ->where('codigo_inspeccion', $insp->codigo_inspeccion)
                    ->get();

                if ($cavidades->isNotEmpty()) {
                    $noConformesCount = $cavidades->filter(function ($cav) {
                        return $cav->estado !== 'CONFORME' || !empty($cav->observaciones) || !empty($cav->motivo_scrap);
                    })->count();

                    $nuevoEstado = ($noConformesCount > 0) ? 'OBSERVADO' : 'CONFORME';

                    DB::table('inspecciones_calidad')
                        ->where('id', $insp->id)
                        ->update(['estado_evaluacion' => $nuevoEstado]);
                }
            }
        }

        // 3. Modificar la restricción CHECK en PostgreSQL/MySQL para permitir exclusivamente CONFORME, OBSERVADO, PNC
        try {
            DB::statement("ALTER TABLE inspecciones_calidad DROP CONSTRAINT IF EXISTS inspecciones_calidad_estado_evaluacion_check;");
            DB::statement("ALTER TABLE inspecciones_calidad ADD CONSTRAINT inspecciones_calidad_estado_evaluacion_check CHECK (estado_evaluacion IN ('CONFORME', 'OBSERVADO', 'PNC'));");
        } catch (\Throwable $e) {
            // Compatibilidad para motores de base de datos sin soporte directo de DROP/ADD CHECK CONSTRAINT
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE inspecciones_calidad DROP CONSTRAINT IF EXISTS inspecciones_calidad_estado_evaluacion_check;");
            DB::statement("ALTER TABLE inspecciones_calidad ADD CONSTRAINT inspecciones_calidad_estado_evaluacion_check CHECK (estado_evaluacion IN ('CONFORME', 'PASABLE', 'OBSERVADO', 'OBSERVADO_PNC', 'PNC'));");
        } catch (\Throwable $e) {
            // Manejo de compatibilidad
        }
    }
};
