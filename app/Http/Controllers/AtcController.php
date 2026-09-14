<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Atc;
use App\Models\Pnc;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AtcController extends Controller
{
    /**
     * Muestra el listado de registros ATC (Atención al Cliente).
     */
    public function index(Request $request): View
    {
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $tipo = $request->get('tipo');
        $search = trim($request->get('search'));

        $query = Atc::with(['user', 'pnc'])
            ->when($fechaInicio, fn($q) => $q->whereDate('fecha', '>=', $fechaInicio))
            ->when($fechaFin, fn($q) => $q->whereDate('fecha', '<=', $fechaFin))
            ->when($tipo, fn($q) => $q->where('tipo', $tipo))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('correlativo', 'ILIKE', "%{$search}%")
                        ->orWhere('cliente_nombre', 'ILIKE', "%{$search}%")
                        ->orWhere('cliente_ruc', 'ILIKE', "%{$search}%")
                        ->orWhere('producto', 'ILIKE', "%{$search}%")
                        ->orWhere('lote', 'ILIKE', "%{$search}%");
                });
            });

        // Contadores para métricas superiores
        $totalCount = Atc::count();
        $reclamosCount = Atc::where('tipo', 'Reclamo')->count();
        $devolucionesCount = Atc::where('tipo', 'Devolucion')->count();

        $atcs = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('atc.index', compact(
            'atcs',
            'fechaInicio',
            'fechaFin',
            'tipo',
            'search',
            'totalCount',
            'reclamosCount',
            'devolucionesCount'
        ));
    }

    /**
     * Muestra el formulario para crear un nuevo registro ATC.
     */
    public function create(): View
    {
        $now = Carbon::now('America/Lima');
        $year = $now->year;
        $today = $now->toDateString();

        // Generar correlativo proyectado anual (Ej. ATC2026-001)
        $prefix = "ATC{$year}-";
        $latest = Atc::where('correlativo', 'LIKE', "{$prefix}%")->max('correlativo');
        
        $nextSeq = 1;
        if ($latest) {
            $parts = explode('-', $latest);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $nextSeq = (int)$parts[1] + 1;
            }
        }
        $nextCorrelativo = $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);

        // Consumir clientes desde la API SIF
        $clientesSif = [];
        try {
            $response = Http::timeout(5)->get(config('services.sif.url') . '/clientes');
            if ($response->successful()) {
                $clientesSif = $response->json()['data'] ?? [];
            }
        } catch (\Throwable $e) {
            logger()->error('Error al consumir API SIF Clientes en ATC: ' . $e->getMessage());
        }

        // Obtener productos y PNCs activos para sugerencias
        $productos = Producto::where('activo', true)->orderBy('nombre', 'asc')->get();
        $pncs = Pnc::orderBy('created_at', 'desc')->limit(50)->get();

        return view('atc.create', compact(
            'nextCorrelativo',
            'today',
            'clientesSif',
            'productos',
            'pncs'
        ));
    }

    /**
     * Almacena un nuevo registro de Atención al Cliente (ATC).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'correlativo' => 'nullable|string|max:50',
            'correlativo_display' => 'nullable|string|max:50',
            'fecha' => 'nullable|date',
            'cliente_nombre' => 'required|string|max:255',
            'cliente_ruc' => 'nullable|string|max:20',
            'tipo' => 'required|in:Reclamo,Devolucion',
            'producto' => 'required|string|max:255',
            'cantidad' => 'required|numeric|min:0',
            'descripcion' => 'required|string',
            'lote' => 'nullable|string|max:100',
            'accion_correctiva' => 'nullable|string',
            'plan_accion' => 'nullable',
            'pnc_id' => 'nullable|exists:pnc,id',
        ], [
            'cliente_nombre.required' => 'El nombre del cliente es obligatorio.',
            'tipo.required' => 'Debe seleccionar el tipo (Reclamo o Devolución).',
            'producto.required' => 'El campo producto es obligatorio.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'descripcion.required' => 'La descripción es obligatoria.',
        ]);

        DB::beginTransaction();

        try {
            $fecha = $validated['fecha'] ?? Carbon::now('America/Lima')->toDateString();
            $year = Carbon::parse($fecha)->year;
            $prefix = "ATC{$year}-";

            // Obtener el correlativo enviado desde el formulario (o calcular consecutivo anual)
            $inputCorrelativo = $validated['correlativo'] ?? $validated['correlativo_display'] ?? null;

            if ($inputCorrelativo && !Atc::where('correlativo', $inputCorrelativo)->exists()) {
                $correlativo = $inputCorrelativo;
            } else {
                $latest = Atc::where('correlativo', 'LIKE', "{$prefix}%")->lockForUpdate()->max('correlativo');
                $nextSeq = 1;
                if ($latest) {
                    $parts = explode('-', $latest);
                    if (isset($parts[1]) && is_numeric($parts[1])) {
                        $nextSeq = (int)$parts[1] + 1;
                    }
                }
                $correlativo = $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
            }

            // Procesar plan_accion si se envía como arreglo o texto
            $rawPlanAccion = $request->input('plan_accion');
            $planAccionFormatted = null;

            if (!empty($rawPlanAccion)) {
                if (is_array($rawPlanAccion)) {
                    $planAccionFormatted = json_encode($rawPlanAccion, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                } else {
                    $planAccionFormatted = (string) $rawPlanAccion;
                }
            }

            $atc = Atc::create([
                'correlativo' => $correlativo,
                'fecha' => $fecha,
                'cliente_nombre' => $validated['cliente_nombre'],
                'cliente_ruc' => $validated['cliente_ruc'] ?? null,
                'tipo' => $validated['tipo'],
                'producto' => $validated['producto'],
                'cantidad' => $validated['cantidad'],
                'descripcion' => $validated['descripcion'],
                'lote' => $validated['lote'] ?? null,
                'accion_correctiva' => $validated['accion_correctiva'] ?? null,
                'plan_accion' => $planAccionFormatted,
                'pnc_id' => $validated['pnc_id'] ?? null,
                'user_id' => auth()->id(),
            ]);

            if (class_exists(ActivityLog::class)) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'accion' => 'CREAR_ATC',
                    'descripcion' => "Se registró el reporte ATC {$atc->correlativo} ({$atc->tipo}) para el cliente {$atc->cliente_nombre}",
                    'ip_address' => $request->ip(),
                ]);
            }

            DB::commit();

            return redirect()->route('atc.index')->with('success', 'Registro ATC creado exitosamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al guardar el registro ATC: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el detalle completo del registro ATC.
     */
    public function show(int $id): View
    {
        $atc = Atc::with(['user', 'pnc.producto', 'pnc.lote'])->findOrFail($id);

        return view('atc.show', compact('atc'));
    }

    /**
     * Exporta el reporte ATC a formato PDF.
     */
    public function exportPdf(int $id)
    {
        $atc = Atc::with(['user', 'pnc.producto', 'pnc.lote'])->findOrFail($id);

        $pdf = Pdf::loadView('atc.pdf', compact('atc'));

        return $pdf->download("ATC_{$atc->correlativo}.pdf");
    }
}
