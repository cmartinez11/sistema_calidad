<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InspeccionCalidad;
use App\Models\InspeccionCavidad;
use App\Models\Lote;
use App\Models\Pnc;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PncController extends Controller
{
    /**
     * Muestra el listado de reportes de Producto No Conforme (PNC).
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $pncs = Pnc::with(['producto', 'lote', 'user'])
            ->when($search, function ($query, $search) {
                $query->where('codigo_pnc', 'ILIKE', "%{$search}%")
                    ->orWhere('codigo_inspeccion', 'ILIKE', "%{$search}%")
                    ->orWhereHas('producto', fn($q) => $q->where('codigo', 'ILIKE', "%{$search}%")->orWhere('nombre', 'ILIKE', "%{$search}%"))
                    ->orWhereHas('lote', fn($q) => $q->where('codigo_lote', 'ILIKE', "%{$search}%"));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pnc.index', compact('pncs', 'search'));
    }

    /**
     * Muestra el formulario para crear un nuevo reporte de PNC.
     */
    public function create(Request $request): View
    {
        $codigoInspeccion = $request->get('codigo_inspeccion');

        $productos = Producto::where('activo', true)->orderBy('nombre', 'asc')->get();
        $lotes = Lote::orderBy('codigo_lote', 'desc')->limit(50)->get();

        // Datos iniciales heredados de la auditoría de cavidades
        $inspeccion = null;
        $selectedProducto = null;
        $selectedLote = null;
        $motivosScrapStr = '';
        $cantidadSugerida = 0;

        if ($codigoInspeccion) {
            $cavidades = InspeccionCavidad::with(['producto', 'operario', 'maquina'])
                ->where('codigo_inspeccion', $codigoInspeccion)
                ->get();

            if ($cavidades->isNotEmpty()) {
                $firstCav = $cavidades->first();
                $selectedProducto = $firstCav->producto;

                $calidad = InspeccionCalidad::with('lote')->where('codigo_inspeccion', $codigoInspeccion)->first();
                if ($calidad && $calidad->lote) {
                    $selectedLote = $calidad->lote;
                    $motivosScrapStr = $calidad->motivo_scrap ?? '';
                } else {
                    // Si aún no está en inspecciones_calidad (bloqueo preventivo), buscar o crear el lote asignado
                    $now = Carbon::now('America/Lima');
                    $codigoLoteCalculado = "PET" . $now->format('y') . sprintf('%02d', $now->isoWeek) . ($firstCav->maquina ? strtoupper(trim($firstCav->maquina->codigo)) : 'M01');
                    $selectedLote = Lote::where('codigo_lote', $codigoLoteCalculado)->first();
                    if (!$selectedLote) {
                        $selectedLote = Lote::create([
                            'codigo_lote' => $codigoLoteCalculado,
                            'producto_id' => $firstCav->producto_id,
                            'maquina_id' => $firstCav->maquina_id,
                            'fecha_produccion' => $now->toDateString(),
                            'estado_lote' => 'liberado',
                        ]);
                    }
                    $motivosScrap = $cavidades->pluck('motivo_scrap')->filter()->unique()->toArray();
                    $motivosScrapStr = implode(', ', $motivosScrap);
                }

                $inspeccion = $firstCav;
                $defectuosas = $cavidades->whereIn('estado', ['FUERA_DE_RANGO', 'OBSERVADO', 'PASABLE'])->count();
                $cantidadSugerida = $defectuosas;
            }
        }

        // Generar código correlativo de PNC (ej: PNC-20260903-0001)
        $prefix = 'PNC-' . date('Ymd') . '-';
        $latest = Pnc::where('codigo_pnc', 'LIKE', "{$prefix}%")->max('codigo_pnc');
        $seq = $latest ? ((int) substr($latest, -4) + 1) : 1;
        $nextCodigoPnc = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

        $today = Carbon::now('America/Lima')->toDateString();

        return view('pnc.create', compact(
            'codigoInspeccion',
            'productos',
            'lotes',
            'inspeccion',
            'selectedProducto',
            'selectedLote',
            'motivosScrapStr',
            'cantidadSugerida',
            'nextCodigoPnc',
            'today'
        ));
    }

    /**
     * Guarda el reporte oficial de PNC y actualiza el estado de la auditoría a PNC.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_pnc' => 'required|string|unique:pnc,codigo_pnc',
            'codigo_inspeccion' => 'nullable|string|max:50',
            'producto_id' => 'required|exists:productos,id',
            'lote_id' => 'nullable|exists:lotes,id',
            'fecha' => 'required|date',
            'cantidad' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string|max:50',
            'cliente_proveedor' => 'nullable|string|max:150',
            'descripcion_nc' => 'required|string',
            'detectado_area' => 'nullable|string|max:100',
            'detectado_fecha' => 'nullable|date',
            'detectado_responsable' => 'nullable|string|max:150',
            'originado_area' => 'nullable|string|max:100',
            'originado_fecha' => 'nullable|date',
            'originado_responsable' => 'nullable|string|max:150',

            // Pruebas
            'eval_revision_registros' => 'nullable|boolean',
            'eval_inspeccion_visual' => 'nullable|boolean',
            'eval_analisis_pruebas' => 'nullable|boolean',
            'eval_otros_check' => 'nullable|boolean',
            'eval_otros_texto' => 'nullable|string|max:255',

            // Tratamientos
            'tratamiento_devolucion' => 'nullable|boolean',
            'tratamiento_reproceso' => 'nullable|boolean',
            'tratamiento_reclasificado' => 'nullable|boolean',
            'tratamiento_molido' => 'nullable|boolean',
            'tratamiento_desperdicio' => 'nullable|boolean',
            'tratamiento_refilado' => 'nullable|boolean',
            'tratamiento_concesion' => 'nullable|boolean',
            'tratamiento_desviacion' => 'nullable|boolean',
            'tratamiento_otros' => 'nullable|boolean',
            'tratamiento_autorizado_por' => 'nullable|string|max:150',
            'tratamiento_fecha' => 'nullable|date',

            // Causa Raíz (5M)
            'causa_mano_obra' => 'nullable|boolean',
            'causa_maquina' => 'nullable|boolean',
            'causa_material' => 'nullable|boolean',
            'causa_metodo' => 'nullable|boolean',
            'causa_medio_ambiente' => 'nullable|boolean',
            'causa_principal' => 'nullable|string',
            'accion_correctiva' => 'nullable|string',
        ], [
            'producto_id.required' => 'El producto es obligatorio.',
            'descripcion_nc.required' => 'La descripción de la no conformidad es obligatoria.',
            'cantidad.required' => 'La cantidad de producto no conforme es obligatoria.',
        ]);

        DB::beginTransaction();

        try {
            $userId = Auth::id();

            $pnc = Pnc::create([
                'codigo_pnc' => $validated['codigo_pnc'],
                'codigo_inspeccion' => $validated['codigo_inspeccion'] ?? null,
                'producto_id' => $validated['producto_id'],
                'lote_id' => $validated['lote_id'] ?? null,
                'user_id' => $userId,
                'fecha' => $validated['fecha'],
                'cantidad' => $validated['cantidad'],
                'unidad_medida' => $validated['unidad_medida'],
                'cliente_proveedor' => $validated['cliente_proveedor'] ?? null,
                'descripcion_nc' => $validated['descripcion_nc'],

                'detectado_area' => $validated['detectado_area'] ?? 'Inyección / Calidad',
                'detectado_fecha' => $validated['detectado_fecha'] ?? $validated['fecha'],
                'detectado_responsable' => $validated['detectado_responsable'] ?? Auth::user()->name,

                'originado_area' => $validated['originado_area'] ?? 'Producción',
                'originado_fecha' => $validated['originado_fecha'] ?? $validated['fecha'],
                'originado_responsable' => $validated['originado_responsable'] ?? null,

                'eval_revision_registros' => $request->has('eval_revision_registros'),
                'eval_inspeccion_visual' => $request->has('eval_inspeccion_visual'),
                'eval_analisis_pruebas' => $request->has('eval_analisis_pruebas'),
                'eval_otros_check' => $request->has('eval_otros_check'),
                'eval_otros_texto' => $validated['eval_otros_texto'] ?? null,

                'tratamiento_devolucion' => $request->has('tratamiento_devolucion'),
                'tratamiento_reproceso' => $request->has('tratamiento_reproceso'),
                'tratamiento_reclasificado' => $request->has('tratamiento_reclasificado'),
                'tratamiento_molido' => $request->has('tratamiento_molido'),
                'tratamiento_desperdicio' => $request->has('tratamiento_desperdicio'),
                'tratamiento_refilado' => $request->has('tratamiento_refilado'),
                'tratamiento_concesion' => $request->has('tratamiento_concesion'),
                'tratamiento_desviacion' => $request->has('tratamiento_desviacion'),
                'tratamiento_otros' => $request->has('tratamiento_otros'),
                'tratamiento_autorizado_por' => $validated['tratamiento_autorizado_por'] ?? null,
                'tratamiento_fecha' => $validated['tratamiento_fecha'] ?? null,

                'causa_mano_obra' => $request->has('causa_mano_obra'),
                'causa_maquina' => $request->has('causa_maquina'),
                'causa_material' => $request->has('causa_material'),
                'causa_metodo' => $request->has('causa_metodo'),
                'causa_medio_ambiente' => $request->has('causa_medio_ambiente'),
                'causa_principal' => $validated['causa_principal'] ?? null,
                'accion_correctiva' => $validated['accion_correctiva'] ?? null,

                'estado_pnc' => 'EMITIDO',
            ]);

            // Cierre y Actualización o Creación de Estado en la inspección de calidad consolidada
            if (!empty($validated['codigo_inspeccion'])) {
                $inspeccionCalidad = InspeccionCalidad::where('codigo_inspeccion', $validated['codigo_inspeccion'])->first();

                if ($inspeccionCalidad) {
                    $inspeccionCalidad->update([
                        'estado_evaluacion' => 'PNC',
                        'causa' => $validated['causa_principal'] ?? ('Producto No Conforme Emitido (' . $validated['codigo_pnc'] . ')')
                    ]);
                } else {
                    $cavidades = InspeccionCavidad::where('codigo_inspeccion', $validated['codigo_inspeccion'])->get();
                    if ($cavidades->isNotEmpty()) {
                        $header = $cavidades->first();
                        $pesos = $cavidades->where('estado', '!=', 'ANULADO')->pluck('peso_medido')->filter()->toArray();
                        $paredes = $cavidades->where('estado', '!=', 'ANULADO')->pluck('espesor_pared')->filter()->toArray();
                        $fondos = $cavidades->where('estado', '!=', 'ANULADO')->pluck('espesor_fondo')->filter()->toArray();
                        $alturas = $cavidades->where('estado', '!=', 'ANULADO')->pluck('altura')->filter()->toArray();
                        $motivosScrap = $cavidades->pluck('motivo_scrap')->filter()->unique()->toArray();
                        $observaciones = $cavidades->pluck('observaciones')->filter()->unique()->toArray();

                        InspeccionCalidad::create([
                            'codigo_inspeccion' => $validated['codigo_inspeccion'],
                            'producto_id' => $validated['producto_id'],
                            'lote_id' => $validated['lote_id'] ?? null,
                            'maquina_id' => $header->maquina_id,
                            'molde_id' => $header->molde_id,
                            'resina_id' => $header->resina_id,
                            'user_id' => $userId,
                            'turno_id' => $header->turno_id,
                            'operario_id' => $header->operario_id,
                            'peso_min' => count($pesos) > 0 ? min($pesos) : null,
                            'peso_max' => count($pesos) > 0 ? max($pesos) : null,
                            'esp_pared_min' => count($paredes) > 0 ? min($paredes) : null,
                            'esp_pared_max' => count($paredes) > 0 ? max($paredes) : null,
                            'esp_fondo_min' => count($fondos) > 0 ? min($fondos) : null,
                            'esp_fondo_max' => count($fondos) > 0 ? max($fondos) : null,
                            'altura_min' => count($alturas) > 0 ? min($alturas) : null,
                            'altura_max' => count($alturas) > 0 ? max($alturas) : null,
                            'estado_evaluacion' => 'PNC',
                            'motivo_scrap' => count($motivosScrap) > 0 ? implode(', ', $motivosScrap) : null,
                            'causa' => $validated['causa_principal'] ?? ('Producto No Conforme Emitido (' . $validated['codigo_pnc'] . ')'),
                            'comentarios' => count($observaciones) > 0 ? implode('; ', $observaciones) : null,
                        ]);
                    }
                }
            }

            ActivityLog::create([
                'user_id' => $userId,
                'accion' => 'CREAR_PNC',
                'descripcion' => "Se emitió el reporte oficial PNC {$pnc->codigo_pnc} asociado a la auditoría {$pnc->codigo_inspeccion}",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return redirect()->route('pnc.show', $pnc->id)
                ->with('success', "¡Excelente! El reporte de Producto No Conforme {$pnc->codigo_pnc} ha sido guardado exitosamente y la auditoría ha sido marcada como PNC.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al guardar el PNC: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el detalle oficial en formato metrológico FE-SIG-FOR-30-V.
     */
    public function show(int $id): View
    {
        $pnc = Pnc::with(['producto', 'lote', 'user', 'inspeccionCalidad'])
            ->findOrFail($id);

        return view('pnc.show', compact('pnc'));
    }

    /**
     * Exporta el reporte de PNC en formato PDF.
     */
    public function exportPdf(int $id)
    {
        $pnc = Pnc::with(['producto', 'lote', 'user', 'inspeccionCalidad'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pnc.pdf', compact('pnc'));

        return $pdf->download("PNC_{$pnc->codigo_pnc}.pdf");
    }
}
