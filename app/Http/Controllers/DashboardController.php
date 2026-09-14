<?php

namespace App\Http\Controllers;

use App\Models\Atc;
use App\Models\InspeccionCalidad;
use App\Models\InspeccionCavidad;
use App\Models\Maquina;
use App\Models\Molde;
use App\Models\Pnc;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Renderiza el tablero principal de control gerencial de calidad con filtros dinámicos.
     */
    public function index(Request $request): View
    {
        $now = Carbon::now('America/Lima');
        $serverTime = $now->toIso8601String();
        $formattedDate = ucfirst($now->translatedFormat('l, d F Y'));

        // Capturar periodo o fechas personalizadas
        $periodo = $request->input('periodo', 'este_mes');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        switch ($periodo) {
            case 'mes_anterior':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'ultimos_3_meses':
                $start = $now->copy()->subMonths(3)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'anio_actual':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
            case 'personalizado':
                $start = $fechaInicio ? Carbon::parse($fechaInicio)->startOfDay() : $now->copy()->startOfMonth();
                $end = $fechaFin ? Carbon::parse($fechaFin)->endOfDay() : $now->copy()->endOfMonth();
                break;
            case 'este_mes':
            default:
                $periodo = 'este_mes';
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
        }

        // 1. Cumplimiento del plan de inspecciones en el periodo
        $metaPlanificada = max(1, (int) ($start->diffInDays($end) + 1) * 5); // 5 inspecciones objetivo por día
        $inspeccionesEjecutadas = InspeccionCalidad::whereBetween('created_at', [$start, $end])->count();
        $cumplimientoPorcentaje = min(100, round(($inspeccionesEjecutadas / $metaPlanificada) * 100, 1));

        // Avance diario (Plan vs Real) para el periodo (últimos 7 días del rango o dentro del rango)
        $diasSemana = [];
        $planDiario = [];
        $realDiario = [];
        
        $iterStart = $end->copy()->subDays(6)->greaterThanOrEqualTo($start) ? $end->copy()->subDays(6) : $start->copy();
        $currentDay = $iterStart->copy();

        while ($currentDay->lessThanOrEqualTo($end) && count($diasSemana) < 7) {
            $diasSemana[] = $currentDay->format('d/m');
            $planDiario[] = 5;
            $realDiario[] = InspeccionCalidad::whereDate('created_at', $currentDay->toDateString())->count();
            $currentDay->addDay();
        }

        // 2. Porcentaje Conforme vs No Conforme en el periodo
        $totalInspecciones = InspeccionCalidad::whereBetween('created_at', [$start, $end])->count();
        $conformesCount = InspeccionCalidad::whereBetween('created_at', [$start, $end])
            ->where('estado_evaluacion', 'CONFORME')
            ->count();
        $noConformesCount = InspeccionCalidad::whereBetween('created_at', [$start, $end])
            ->whereIn('estado_evaluacion', ['OBSERVADO', 'PNC', 'RECHAZADO'])
            ->count();

        $pctConforme = $totalInspecciones > 0 ? round(($conformesCount / $totalInspecciones) * 100, 1) : 100;
        $pctNoConforme = $totalInspecciones > 0 ? round(($noConformesCount / $totalInspecciones) * 100, 1) : 0;

        // 3. Top Productos que Más Fallan (Ranking de Mayor Volumen de Rechazos/PNC/ATC)
        $topProductosDefectuosos = InspeccionCavidad::select('producto_id', DB::raw('count(*) as total_fallas'))
            ->where('estado', '!=', 'CONFORME')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('producto_id')
            ->orderBy('total_fallas', 'desc')
            ->with('producto')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'producto' => ($item->producto->codigo ?? '') . ' ' . ($item->producto->nombre ?? 'Producto ' . $item->producto_id),
                'total' => $item->total_fallas
            ]);

        if ($topProductosDefectuosos->isEmpty()) {
            // Fallback con productos de muestra para visualización previa
            $topProductosDefectuosos = collect([
                ['producto' => 'PRE-01 Preforma 20g 28mm', 'total' => 18],
                ['producto' => 'PRE-02 Preforma 28g 30mm', 'total' => 12],
                ['producto' => 'PRE-03 Preforma 35g 38mm', 'total' => 8],
                ['producto' => 'PRE-04 Preforma 42g PCO', 'total' => 5],
            ]);
        }

        // 4. Rechazo por Máquina, Molde y Cavidad en el periodo
        $rechazosPorMaquina = InspeccionCalidad::select('maquina_id', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('estado_evaluacion', ['OBSERVADO', 'PNC', 'RECHAZADO'])
            ->groupBy('maquina_id')
            ->with('maquina')
            ->get()
            ->map(fn($item) => [
                'maquina' => $item->maquina->codigo ?? 'M' . $item->maquina_id,
                'total' => $item->total
            ]);

        $rechazosPorMolde = InspeccionCalidad::select('molde_id', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('estado_evaluacion', ['OBSERVADO', 'PNC', 'RECHAZADO'])
            ->groupBy('molde_id')
            ->with('molde')
            ->get()
            ->map(fn($item) => [
                'molde' => $item->molde->codigo ?? 'Molde ' . $item->molde_id,
                'total' => $item->total
            ]);

        $rechazosPorCavidad = InspeccionCavidad::select('cavidad_numero', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$start, $end])
            ->where('estado', '!=', 'CONFORME')
            ->groupBy('cavidad_numero')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'cavidad' => 'Cav. ' . $item->cavidad_numero,
                'total' => $item->total
            ]);

        // 5. Estatus y Cantidad de PNC
        $pncPendientes = Pnc::whereBetween('created_at', [$start, $end])->where('estado_pnc', 'PENDIENTE')->count();
        $pncProcesados = Pnc::whereBetween('created_at', [$start, $end])->where('estado_pnc', 'PROCESADO')->count();
        $pncRetenidos = Pnc::whereBetween('created_at', [$start, $end])->whereNotNull('lote_id')->count();
        $pncAccionesVencidas = Pnc::where('estado_pnc', 'PENDIENTE')
            ->where('fecha', '<=', $now->copy()->subDays(7)->toDateString())
            ->count();

        // 6. Pareto de Defectos Principales
        $defectosRaw = InspeccionCavidad::select('motivo_scrap', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('motivo_scrap')
            ->where('motivo_scrap', '!=', '')
            ->groupBy('motivo_scrap')
            ->orderBy('total', 'desc')
            ->get();

        $totalDefectosCount = $defectosRaw->sum('total');
        $paretoLabels = [];
        $paretoCantidades = [];
        $paretoAcumulado = [];
        $sumaAcumulada = 0;

        foreach ($defectosRaw as $def) {
            $paretoLabels[] = $def->motivo_scrap;
            $paretoCantidades[] = $def->total;
            $sumaAcumulada += $def->total;
            $paretoAcumulado[] = $totalDefectosCount > 0 ? round(($sumaAcumulada / $totalDefectosCount) * 100, 1) : 0;
        }

        if (empty($paretoLabels)) {
            $paretoLabels = ['Soplado', 'Rebaba', 'Mancha', 'Deformación', 'Otros'];
            $paretoCantidades = [45, 25, 15, 10, 5];
            $paretoAcumulado = [45, 70, 85, 95, 100];
        }

        // Desviación de Gramaje
        $desviacionesGramaje = InspeccionCalidad::with(['producto.parametroPreforma'])
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('peso_min')
            ->whereNotNull('peso_max')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'serverTime',
            'formattedDate',
            'periodo',
            'fechaInicio',
            'fechaFin',
            'start',
            'end',
            'cumplimientoPorcentaje',
            'inspeccionesEjecutadas',
            'metaPlanificada',
            'diasSemana',
            'planDiario',
            'realDiario',
            'totalInspecciones',
            'conformesCount',
            'noConformesCount',
            'pctConforme',
            'pctNoConforme',
            'topProductosDefectuosos',
            'rechazosPorMaquina',
            'rechazosPorMolde',
            'rechazosPorCavidad',
            'pncPendientes',
            'pncProcesados',
            'pncRetenidos',
            'pncAccionesVencidas',
            'paretoLabels',
            'paretoCantidades',
            'paretoAcumulado',
            'desviacionesGramaje'
        ));
    }
}
