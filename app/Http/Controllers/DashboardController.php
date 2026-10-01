<?php

namespace App\Http\Controllers;

use App\Models\Atc;
use App\Models\InspeccionCalidad;
use App\Models\InspeccionCavidad;
use App\Models\Maquina;
use App\Models\MaquinaHistorial;
use App\Models\Molde;
use App\Models\ParametroPreforma;
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

        // 1. Cumplimiento del plan de inspecciones para la semana actual en curso (Lunes a Domingo)
        $inicioSemana = $now->copy()->startOfWeek();
        $finSemana = $now->copy()->endOfWeek();

        $diasSemana = [];
        $planDiario = [];
        $realDiario = [];
        $pendienteDiario = [];

        $inspeccionesSemana = InspeccionCalidad::whereBetween('created_at', [
                $inicioSemana->copy()->startOfDay(),
                $finSemana->copy()->endOfDay()
            ])
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->pluck('total', 'fecha');

        $allMachines = Maquina::all();
        $diaActualIter = $inicioSemana->copy();

        while ($diaActualIter->lessThanOrEqualTo($finSemana)) {
            $fechaKey = $diaActualIter->toDateString();
            $dateEndOfDay = $diaActualIter->copy()->endOfDay();

            $nombreDia = ucfirst($diaActualIter->locale('es')->dayName);
            $diasSemana[] = $nombreDia . ' ' . $diaActualIter->format('d/m');

            $activeMachinesCount = 0;
            foreach ($allMachines as $maq) {
                $latestPastHistorial = MaquinaHistorial::where('maquina_id', $maq->id)
                    ->where('fecha_hora_inicio', '<=', $dateEndOfDay)
                    ->latest('fecha_hora_inicio')
                    ->latest('id')
                    ->first();

                $estadoEnFecha = $latestPastHistorial ? $latestPastHistorial->nuevo_estado : 'activo';

                if ($estadoEnFecha === 'activo') {
                    $activeMachinesCount++;
                }
            }

            $metaDiaria = max(2, $activeMachinesCount * 2);
            $planDiario[] = $metaDiaria;

            $ejecutadasCount = (int) ($inspeccionesSemana[$fechaKey] ?? 0);
            $realDiario[] = $ejecutadasCount;

            $pendienteDiario[] = max(0, $metaDiaria - $ejecutadasCount);

            $diaActualIter->addDay();
        }

        $metaPlanificada = max(1, array_sum($planDiario));
        $inspeccionesEjecutadas = array_sum($realDiario);
        $cumplimientoPorcentaje = min(100, round(($inspeccionesEjecutadas / $metaPlanificada) * 100, 1));

        // Contadores para las tarjetas principales de Calidad
        $conformesCount = InspeccionCalidad::whereBetween('created_at', [$start, $end])
            ->where('estado_evaluacion', 'CONFORME')
            ->count();

        $observadosCount = InspeccionCalidad::whereBetween('created_at', [$start, $end])
            ->where('estado_evaluacion', 'OBSERVADO')
            ->count();

        $pncEvaluadosCount = InspeccionCalidad::whereBetween('created_at', [$start, $end])
            ->where('estado_evaluacion', 'PNC')
            ->count();

        // Alertas de Reclamos ATC filtradas por el periodo seleccionado
        $alertasAtcQuery = Atc::with(['user'])
            ->whereBetween('created_at', [$start, $end]);

        $totalAtcPeriodo = $alertasAtcQuery->count(); // Cantidad exacta en el periodo
        
        $alertasAtc = $alertasAtcQuery->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 3. Top Productos que Más Fallan (Excluyendo explícitamente las cavidades ANULADAS)
        $topProductosDefectuosos = InspeccionCavidad::select('producto_id', DB::raw('count(*) as total_fallas'))
            ->whereIn('estado', ['FUERA_DE_RANGO', 'OBSERVADO']) // Solo cuenta defectos reales de medición
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
            $topProductosDefectuosos = collect([
                ['producto' => 'PRE-01 Preforma 20g 28mm', 'total' => 0],
                ['producto' => 'PRE-02 Preforma 28g 30mm', 'total' => 0],
                ['producto' => 'PRE-03 Preforma 35g 38mm', 'total' => 0],
                ['producto' => 'PRE-04 Preforma 42g PCO', 'total' => 0],
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
            ->whereIn('estado', ['FUERA_DE_RANGO', 'OBSERVADO']) // Excluyendo ANULADO
            ->whereBetween('created_at', [$start, $end])
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
            ->whereIn('estado', ['FUERA_DE_RANGO', 'OBSERVADO']) // Excluyendo ANULADO
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

        // 7. Variaciones de Datos / Defectos en Cavidades (Peso, Espesor, Altura)
        $desviacionesGramaje = InspeccionCalidad::with(['producto.parametroPreforma', 'maquina'])
            ->whereBetween('created_at', [$start, $end])
            ->whereExists(function($sub) {
                $sub->select(DB::raw(1))
                    ->from('inspecciones_cavidades')
                    ->whereColumn('inspecciones_cavidades.codigo_inspeccion', 'inspecciones_calidad.codigo_inspeccion')
                    ->whereIn('estado', ['FUERA_DE_RANGO']);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 8. Estado Actual y Disponibilidad de Máquinas
        $maquinasTotal = $allMachines->count();
        $maquinasActivasCount = $allMachines->where('estado', 'activo')->count();
        $maquinasMantenimientoCount = $allMachines->where('estado', 'mantenimiento')->count();
        $maquinasInactivasCount = $allMachines->whereIn('estado', ['sin_programacion', 'sin_resina', 'sin_personal', 'inactivo'])->count();

        $maquinasDetenidas = Maquina::with(['historiales' => fn($q) => $q->latest('fecha_hora_inicio')->limit(1)])
            ->whereIn('estado', ['mantenimiento', 'sin_programacion', 'sin_resina', 'sin_personal', 'inactivo'])
            ->get();

        // 9. Disponibilidad de Moldes por Cavidades Activas y Anuladas
        $fechaMoldeSeleccionada = $request->get('fecha_molde', now('America/Lima')->toDateString());

        $moldesDisponibilidad = collect();
        $moldesActivos = Molde::where('activo', true)->get();

        foreach ($moldesActivos as $molde) {
            $gramajesDesdeParametros = ParametroPreforma::with('producto')
                ->where('molde_id', $molde->id)
                ->get()
                ->map(fn($p) => (object)[
                    'peso_nominal' => $p->peso_nominal,
                    'producto_id' => $p->producto_id
                ]);

            $gramajesDesdeProductos = Producto::where('molde_id', $molde->id)
                ->with('parametroPreforma')
                ->get()
                ->map(fn($prod) => (object)[
                    'peso_nominal' => $prod->parametroPreforma->peso_nominal ?? $prod->peso_nominal ?? $prod->gramaje ?? 0,
                    'producto_id' => $prod->id
                ]);

            $gramajesAsociados = $gramajesDesdeParametros->concat($gramajesDesdeProductos)
                ->filter(fn($item) => $item->peso_nominal > 0)
                ->unique('peso_nominal')
                ->values();

            if ($gramajesAsociados->isEmpty()) {
                $gramajesAsociados = collect([
                    (object)[
                        'peso_nominal' => null,
                        'producto_id' => null
                    ]
                ]);
            }

            foreach ($gramajesAsociados as $itemGramaje) {
                $gramajeVal = $itemGramaje->peso_nominal;
                $prodId = $itemGramaje->producto_id;

                $subquery = InspeccionCavidad::select(
                        'cavidad_numero', 
                        'molde_id', 
                        'producto_id', 
                        'estado', 
                        'observaciones', 
                        'motivo_scrap',
                        DB::raw('ROW_NUMBER() OVER (PARTITION BY molde_id, cavidad_numero ORDER BY fecha_real DESC, created_at DESC) as rn')
                    )
                    ->where('fecha_real', '<=', $fechaMoldeSeleccionada)
                    ->where('molde_id', $molde->id);

                $cavidadesAnuladasDb = DB::query()->fromSub($subquery, 'latest_cavidades')
                    ->where('rn', 1)
                    ->where('estado', 'ANULADO')
                    ->get()
                    ->sortBy('cavidad_numero')
                    ->values();

                $cavAnuladas = $cavidadesAnuladasDb->count();
                $cavTotales = $molde->numero_cavidades ?? 24;
                $totalCavs = max($cavTotales, $cavAnuladas);

                $activas = max(0, $totalCavs - $cavAnuladas);
                $disponibilidad = $totalCavs > 0 ? round(($activas / $totalCavs) * 100) : 100;

                $listaCavsAnuladas = [];
                foreach ($cavidadesAnuladasDb as $itemCav) {
                    $numStr = 'C-' . str_pad($itemCav->cavidad_numero, 2, '0', STR_PAD_LEFT);
                    $motivoTexto = trim($itemCav->observaciones ?? $itemCav->motivo_scrap ?? 'Anulado sin motivo especificado');
                    
                    $listaCavsAnuladas[] = [
                        'etiqueta' => $numStr,
                        'motivo' => $motivoTexto
                    ];
                }

                $sufijoGramaje = $gramajeVal ? " (" . number_format($gramajeVal, ($gramajeVal == intval($gramajeVal) ? 0 : 1), '.', '') . "g)" : "";
                $nombreMoldeLimpio = ($molde->codigo === $molde->nombre || str_contains($molde->nombre, $molde->codigo))
                    ? "{$molde->nombre}{$sufijoGramaje}" 
                    : "{$molde->codigo} - {$molde->nombre}{$sufijoGramaje}";

                if ($totalCavs > 0) {
                    $moldesDisponibilidad->push([
                        'nombre_molde' => $nombreMoldeLimpio,
                        'cav_totales' => $totalCavs,
                        'cav_anuladas' => $cavAnuladas,
                        'cavidades_anuladas_lista' => $listaCavsAnuladas,
                        'activas' => $activas,
                        'disponibilidad' => $disponibilidad,
                    ]);
                }
            }
        }

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
            'pendienteDiario',
            'conformesCount',
            'observadosCount',
            'pncEvaluadosCount',
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
            'desviacionesGramaje',
            'maquinasTotal',
            'maquinasActivasCount',
            'maquinasMantenimientoCount',
            'maquinasInactivasCount',
            'maquinasDetenidas',
            'moldesDisponibilidad',
            'fechaMoldeSeleccionada',
            'alertasAtc'
        ));
    }
}