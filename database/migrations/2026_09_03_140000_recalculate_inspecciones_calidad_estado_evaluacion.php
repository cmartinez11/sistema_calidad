<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Recalcular la jerarquía de estado_evaluacion para auditorías existentes.
     */
    public function up(): void
    {
        $inspecciones = DB::table('inspecciones_calidad')->get();

        foreach ($inspecciones as $insp) {
            if ($insp->estado_evaluacion === 'PNC') {
                continue;
            }

            if (empty($insp->codigo_inspeccion)) {
                continue;
            }

            $cavidades = DB::table('inspecciones_cavidades')
                ->where('codigo_inspeccion', $insp->codigo_inspeccion)
                ->get();

            if ($cavidades->isEmpty()) {
                continue;
            }

            $defectosCount = $cavidades->whereIn('estado', ['FUERA_DE_RANGO', 'OBSERVADO'])->count();
            $pasablesCount = $cavidades->where('estado', 'PASABLE')->count();

            if ($defectosCount > 0) {
                $nuevoEstado = 'OBSERVADO';
            } elseif ($pasablesCount > 0) {
                $nuevoEstado = 'PASABLE';
            } else {
                $nuevoEstado = 'CONFORME';
            }

            DB::table('inspecciones_calidad')
                ->where('id', $insp->id)
                ->update(['estado_evaluacion' => $nuevoEstado]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
