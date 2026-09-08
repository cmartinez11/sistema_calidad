<?php

namespace App\Http\Controllers;

use App\Models\InspeccionCalidad;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspeccionCalidadController extends Controller
{
    /**
     * Muestra el índice consolidado de auditorías / inspecciones de calidad.
     */
    public function index(Request $request): View
    {
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $productoId = $request->get('producto_id');
        $lote = $request->get('lote');
        $estado = $request->get('estado');
        $search = $request->get('search');

        $inspecciones = InspeccionCalidad::query()
            ->with(['producto', 'maquina', 'molde', 'resina', 'lote', 'operario', 'turno', 'user'])
            ->when($fechaInicio, fn($q) => $q->whereDate('created_at', '>=', $fechaInicio))
            ->when($fechaFin, fn($q) => $q->whereDate('created_at', '<=', $fechaFin))
            ->when($productoId, fn($q) => $q->where('producto_id', $productoId))
            ->when($lote, fn($q) => $q->whereHas('lote', fn($l) => $l->where('codigo_lote', 'ILIKE', "%{$lote}%")))
            ->when($estado, fn($q) => $q->where('estado_evaluacion', $estado))
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('codigo_inspeccion', 'ILIKE', "%{$search}%")
                        ->orWhereHas('producto', fn($p) => $p->where('codigo', 'ILIKE', "%{$search}%")->orWhere('nombre', 'ILIKE', "%{$search}%"))
                        ->orWhereHas('lote', fn($l) => $l->where('codigo_lote', 'ILIKE', "%{$search}%"))
                        ->orWhereHas('maquina', fn($m) => $m->where('codigo', 'ILIKE', "%{$search}%")->orWhere('nombre', 'ILIKE', "%{$search}%"))
                        ->orWhereHas('operario', fn($o) => $o->where('nombre', 'ILIKE', "%{$search}%"));
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $productos = Producto::orderBy('nombre', 'asc')->get();

        return view('inspecciones_calidad.index', compact(
            'inspecciones',
            'productos',
            'fechaInicio',
            'fechaFin',
            'productoId',
            'lote',
            'estado',
            'search'
        ));
    }

    /**
     * Muestra el detalle metrológico consolidado de una inspección de calidad.
     */
    public function show($id): View
    {
        $inspeccion = InspeccionCalidad::with(['producto.parametroPreforma', 'maquina', 'molde', 'resina', 'lote', 'operario', 'turno', 'user'])
            ->findOrFail($id);

        return view('inspecciones_calidad.show', compact('inspeccion'));
    }
}
