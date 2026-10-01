<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Maquina;
use App\Models\Producto;
use App\Services\LoteService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    protected LoteService $loteService;

    public function __construct(LoteService $loteService)
    {
        $this->loteService = $loteService;
    }

    /**
     * Genera automáticamente el código de lote y registra el nuevo lote en la BD.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'maquina_id' => 'required|exists:maquinas,id',
            'resina' => 'nullable|string|max:100',
            'fecha_produccion' => 'required|date',
            'estado_lote' => 'nullable|in:en_proceso,liberado,observado_pnc',
            'cantidad_empaques' => 'nullable|integer|min:0',
            'cantidad_producida_unidades' => 'nullable|integer|min:0',
            'peso_total_kg' => 'nullable|numeric|min:0',
            'scrap_kg' => 'nullable|numeric|min:0',
        ]);

        $lote = $this->loteService->crearLote($validated);

        return response()->json([
            'message' => 'Lote generado y registrado exitosamente.',
            'data' => $lote,
        ], 201);
    }

    /**
     * Previsualiza la generación del código de lote sin registrarlo en BD.
     */
    public function previewCodigo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'maquina_id' => 'required|exists:maquinas,id',
            'fecha_produccion' => 'nullable|date',
        ]);

        $producto = Producto::findOrFail($validated['producto_id']);
        $maquina = Maquina::findOrFail($validated['maquina_id']);
        $fecha = isset($validated['fecha_produccion']) ? Carbon::parse($validated['fecha_produccion']) : Carbon::now();

        $prefijoLinea = $producto->prefijo ?? $producto->codigo;
        $codigoLote = $this->loteService->generarCodigoLote($prefijoLinea, $maquina->codigo, $fecha);

        return response()->json([
            'codigo_lote' => $codigoLote,
        ]);
    }
}
