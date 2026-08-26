<?php

namespace App\Http\Controllers;

use App\Models\InspeccionCavidad;
use App\Models\Maquina;
use App\Models\Operario;
use App\Models\Producto;
use App\Models\Turno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InspeccionCavidadController extends Controller
{
    /**
     * Muestra el historial de inspecciones por cavidades agrupadas por codigo_inspeccion.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $inspecciones = InspeccionCavidad::query()
            ->with(['producto', 'maquina', 'operario', 'turno', 'user'])
            ->whereNotNull('codigo_inspeccion')
            ->where('codigo_inspeccion', '!=', '')
            ->when($search, function ($query, $search) {
                $query->where('codigo_inspeccion', 'ILIKE', "%{$search}%")
                    ->orWhereHas('producto', fn($q) => $q->where('codigo', 'ILIKE', "%{$search}%")->orWhere('nombre', 'ILIKE', "%{$search}%"))
                    ->orWhereHas('maquina', fn($q) => $q->where('codigo', 'ILIKE', "%{$search}%")->orWhere('nombre', 'ILIKE', "%{$search}%"))
                    ->orWhereHas('operario', fn($q) => $q->where('nombre', 'ILIKE', "%{$search}%"));
            })
            ->select(
                'codigo_inspeccion',
                'producto_id',
                'maquina_id',
                'operario_id',
                'turno_id',
                'user_id',
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('COUNT(*) as total_cavidades'),
                DB::raw("COUNT(CASE WHEN estado = 'FUERA_DE_RANGO' THEN 1 END) as fuera_de_rango_count")
            )
            ->groupBy('codigo_inspeccion', 'producto_id', 'maquina_id', 'operario_id', 'turno_id', 'user_id')
            ->orderBy(DB::raw('MIN(created_at)'), 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('inspecciones_cavidades.index', compact('inspecciones', 'search'));
    }

    /**
     * Muestra la vista interactiva para el registro de pesos cavidad por cavidad.
     */
    public function create(Request $request): View
    {
        $productos = Producto::with('parametroPreforma')
            ->where('activo', true)
            ->orderBy('nombre', 'asc')
            ->get();

        $maquinas = Maquina::where('estado', 'activo')
            ->orderBy('codigo', 'asc')
            ->get();

        $operarios = Operario::where('activo', true)
            ->orderBy('nombre', 'asc')
            ->get();

        $turnos = Turno::all();

        $selectedProductoId = $request->get('producto_id');

        return view('inspecciones_cavidades.create', compact(
            'productos',
            'maquinas',
            'operarios',
            'turnos',
            'selectedProductoId'
        ));
    }

    /**
     * Almacena autónomamente el pesaje por cavidades generando un codigo_inspeccion único.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'maquina_id' => 'nullable|exists:maquinas,id',
            'operario_id' => 'nullable|exists:operarios,id',
            'turno_id' => 'nullable|exists:turnos,id',
            'cavidades' => 'required|array|min:1',
            'cavidades.*.cavidad_numero' => 'required|integer|min:1',
            'cavidades.*.peso_medido' => 'required|numeric|min:0',
            'cavidades.*.estado' => 'required|string',
            'cavidades.*.motivo_scrap' => 'nullable|string|max:100',
        ], [
            'producto_id.required' => 'Debes seleccionar un producto válido.',
            'cavidades.required' => 'Debes registrar al menos una cavidad.',
            'cavidades.*.peso_medido.required' => 'El peso real es obligatorio en todas las cavidades.',
        ]);

        DB::beginTransaction();

        try {
            $producto = Producto::findOrFail($validated['producto_id']);
            $userId = Auth::id();
            $fueraDeRangoCount = 0;

            // Generar código único correlativo (ej: CAV-20260826-0001)
            $prefix = 'CAV-' . date('Ymd') . '-';
            $latestCode = InspeccionCavidad::where('codigo_inspeccion', 'LIKE', "{$prefix}%")
                ->max('codigo_inspeccion');

            if ($latestCode) {
                $seq = (int) substr($latestCode, -4) + 1;
            } else {
                $seq = 1;
            }

            $codigoInspeccion = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Guardar autónomamente cada registro de cavidad con el mismo código identificador
            foreach ($validated['cavidades'] as $cavData) {
                $estadoClean = strtoupper(str_replace(' ', '_', $cavData['estado']));
                if ($estadoClean === 'FUERA_DE_RANGO') {
                    $fueraDeRangoCount++;
                }

                $motivo = ($estadoClean === 'FUERA_DE_RANGO') ? ($cavData['motivo_scrap'] ?? 'Sin especificar') : null;

                InspeccionCavidad::create([
                    'codigo_inspeccion' => $codigoInspeccion,
                    'producto_id' => $producto->id,
                    'maquina_id' => $validated['maquina_id'] ?? null,
                    'operario_id' => $validated['operario_id'] ?? null,
                    'turno_id' => $validated['turno_id'] ?? null,
                    'user_id' => $userId,
                    'cavidad_numero' => $cavData['cavidad_numero'],
                    'peso_medido' => $cavData['peso_medido'],
                    'estado' => $estadoClean,
                    'motivo_scrap' => $motivo,
                ]);
            }

            DB::commit();

            $totalCount = count($validated['cavidades']);
            $estadoResumen = ($fueraDeRangoCount === 0) ? 'CONFORME' : "{$fueraDeRangoCount} cavidades fuera de rango";
            $msg = "Auditoría de cavidades registrada exitosamente con el código {$codigoInspeccion}. Total evaluado: {$totalCount} cavidades ({$estadoResumen}).";

            return redirect()->route('inspecciones-cavidades.show', $codigoInspeccion)
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar el pesaje de cavidades: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el detalle metrológico e informe imprimible de una inspección por cavidades.
     */
    public function show(string $codigo): View
    {
        $cavidades = InspeccionCavidad::with(['producto.parametroPreforma', 'maquina', 'operario', 'turno', 'user'])
            ->where('codigo_inspeccion', $codigo)
            ->orderBy('cavidad_numero', 'asc')
            ->get();

        if ($cavidades->isEmpty()) {
            abort(404, 'Auditoría de cavidades no encontrada.');
        }

        $header = $cavidades->first();
        $producto = $header->producto;
        $param = $producto->parametroPreforma ?? null;

        $totalCavidades = $cavidades->count();
        $fueraDeRangoCount = $cavidades->where('estado', 'FUERA_DE_RANGO')->count();
        $conformesCount = $totalCavidades - $fueraDeRangoCount;
        $promedioPeso = number_format($cavidades->avg('peso_medido'), 2);

        return view('inspecciones_cavidades.show', compact(
            'codigo',
            'cavidades',
            'header',
            'producto',
            'param',
            'totalCavidades',
            'fueraDeRangoCount',
            'conformesCount',
            'promedioPeso'
        ));
    }

    /**
     * Exporta el informe metrológico de auditoría de cavidades en formato PDF.
     */
    public function exportPdf(string $codigo)
    {
        $cavidades = InspeccionCavidad::with(['producto.parametroPreforma', 'maquina', 'operario', 'turno', 'user'])
            ->where('codigo_inspeccion', $codigo)
            ->orderBy('cavidad_numero', 'asc')
            ->get();

        if ($cavidades->isEmpty()) {
            abort(404, 'Auditoría de cavidades no encontrada.');
        }

        $header = $cavidades->first();
        $producto = $header->producto;
        $param = $producto->parametroPreforma ?? null;

        $totalCavidades = $cavidades->count();
        $fueraDeRangoCount = $cavidades->where('estado', 'FUERA_DE_RANGO')->count();
        $conformesCount = $totalCavidades - $fueraDeRangoCount;
        $promedioPeso = number_format($cavidades->avg('peso_medido'), 2);
        $porcentajeConforme = number_format(($conformesCount / max($totalCavidades, 1)) * 100, 1);

        $pdf = Pdf::loadView('inspecciones_cavidades.pdf', compact(
            'codigo',
            'cavidades',
            'header',
            'producto',
            'param',
            'totalCavidades',
            'fueraDeRangoCount',
            'conformesCount',
            'promedioPeso',
            'porcentajeConforme'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream("Reporte_Metrologico_{$codigo}.pdf");
    }
}
