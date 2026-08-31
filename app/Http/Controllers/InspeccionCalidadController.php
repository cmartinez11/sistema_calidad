<?php

namespace App\Http\Controllers;

use App\Models\InspeccionCalidad;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspeccionCalidadController extends Controller
{
    /**
     * Muestra el índice consolidado de auditorías / inspecciones de calidad.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $inspecciones = InspeccionCalidad::query()
            ->with(['producto', 'maquina', 'molde', 'resina', 'lote', 'operario', 'turno', 'user'])
            ->when($search, function ($query, $search) {
                $query->where('codigo_inspeccion', 'ILIKE', "%{$search}%")
                    ->orWhereHas('producto', fn($q) => $q->where('codigo', 'ILIKE', "%{$search}%")->orWhere('nombre', 'ILIKE', "%{$search}%"))
                    ->orWhereHas('lote', fn($q) => $q->where('codigo_lote', 'ILIKE', "%{$search}%"))
                    ->orWhereHas('maquina', fn($q) => $q->where('codigo', 'ILIKE', "%{$search}%")->orWhere('nombre', 'ILIKE', "%{$search}%"))
                    ->orWhereHas('operario', fn($q) => $q->where('nombre', 'ILIKE', "%{$search}%"));
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('inspecciones_calidad.index', compact('inspecciones', 'search'));
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
