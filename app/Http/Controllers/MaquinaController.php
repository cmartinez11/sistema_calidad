<?php

namespace App\Http\Controllers;

use App\Models\Maquina;
use App\Models\MaquinaHistorial;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MaquinaController extends Controller
{
    /**
     * Muestra el listado de máquinas e inyectoras con paginación, búsqueda e historial de trazabilidad.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $maquinas = Maquina::query()
            ->with(['historiales.user'])
            ->when($search, function ($query, $search) {
                $query->where('codigo', 'ILIKE', "%{$search}%")
                    ->orWhere('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('estado', 'ILIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('maquinas.index', compact('maquinas', 'search'));
    }

    /**
     * Almacena una nueva máquina en la base de datos y registra el evento inicial de trazabilidad.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:maquinas,codigo',
            'nombre' => 'required|string|max:100',
            'estado' => 'required|string|in:activo,mantenimiento,sin_programacion,sin_resina,sin_personal',
            'motivo_observacion' => 'nullable|string|max:500',
        ], [
            'codigo.required' => 'El código de la máquina es obligatorio.',
            'codigo.unique' => 'Ya existe una máquina registrada con este código.',
            'nombre.required' => 'El nombre o descripción de la máquina es obligatorio.',
            'estado.required' => 'El estado operativo de la máquina es obligatorio.',
            'estado.in' => 'El estado operativo seleccionado no es válido.',
        ]);

        DB::transaction(function () use ($validated) {
            $maquina = Maquina::create([
                'codigo' => $validated['codigo'],
                'nombre' => $validated['nombre'],
                'estado' => $validated['estado'],
            ]);

            $now = Carbon::now('America/Lima');

            MaquinaHistorial::create([
                'maquina_id' => $maquina->id,
                'user_id' => Auth::id(),
                'estado_anterior' => null,
                'nuevo_estado' => $validated['estado'],
                'motivo_observacion' => $validated['motivo_observacion'] ?? 'Alta de máquina en el sistema',
                'fecha_hora_inicio' => $now,
                'fecha_hora_fin' => $validated['estado'] === 'activo' ? $now : null,
                'duracion_minutos' => $validated['estado'] === 'activo' ? 0 : null,
            ]);
        });

        return redirect()->route('maquinas.index')
            ->with('success', "Máquina {$validated['codigo']} registrada exitosamente con su trazabilidad de estado.");
    }

    /**
     * Actualiza la máquina especificada y registra la trazabilidad de cambio de estado / cierre de parada.
     */
    public function update(Request $request, Maquina $maquina): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:maquinas,codigo,' . $maquina->id,
            'nombre' => 'required|string|max:100',
            'estado' => 'required|string|in:activo,mantenimiento,sin_programacion,sin_resina,sin_personal',
            'motivo_observacion' => 'nullable|string|max:500',
            'fecha_hora_inicio' => 'nullable|date',
        ], [
            'codigo.required' => 'El código de la máquina es obligatorio.',
            'codigo.unique' => 'Ya existe otra máquina registrada con este código.',
            'nombre.required' => 'El nombre o descripción de la máquina es obligatorio.',
            'estado.required' => 'El estado operativo de la máquina es obligatorio.',
            'estado.in' => 'El estado operativo seleccionado no es válido.',
        ]);

        $estadoAnterior = $maquina->estado;
        $nuevoEstado = $validated['estado'];

        DB::transaction(function () use ($maquina, $validated, $estadoAnterior, $nuevoEstado) {
            $fechaEvento = !empty($validated['fecha_hora_inicio'])
                ? Carbon::parse($validated['fecha_hora_inicio'])
                : Carbon::now('America/Lima');

            // Si el estado operativo ha cambiado
            if ($estadoAnterior !== $nuevoEstado) {
                // 1. Cerrar cualquier evento de parada/mantenimiento activo previo que no tenga fecha_hora_fin
                $eventoAbierto = MaquinaHistorial::where('maquina_id', $maquina->id)
                    ->whereNull('fecha_hora_fin')
                    ->latest()
                    ->first();

                if ($eventoAbierto) {
                    $duracionMins = (int) Carbon::parse($eventoAbierto->fecha_hora_inicio)->diffInMinutes($fechaEvento);
                    $eventoAbierto->update([
                        'fecha_hora_fin' => $fechaEvento,
                        'duracion_minutos' => max(1, $duracionMins),
                    ]);
                }

                // 2. Registrar el nuevo evento de estado
                MaquinaHistorial::create([
                    'maquina_id' => $maquina->id,
                    'user_id' => Auth::id(),
                    'estado_anterior' => $estadoAnterior,
                    'nuevo_estado' => $nuevoEstado,
                    'motivo_observacion' => $validated['motivo_observacion'] ?? null,
                    'fecha_hora_inicio' => $fechaEvento,
                    'fecha_hora_fin' => $nuevoEstado === 'activo' ? $fechaEvento : null,
                    'duracion_minutos' => $nuevoEstado === 'activo' ? 0 : null,
                ]);
            }

            // Actualizar los datos de la máquina
            $maquina->update([
                'codigo' => $validated['codigo'],
                'nombre' => $validated['nombre'],
                'estado' => $nuevoEstado,
            ]);
        });

        return redirect()->route('maquinas.index')
            ->with('success', "Máquina {$maquina->codigo} actualizada exitosamente. Trazabilidad de estado registrada.");
    }

    /**
     * Elimina la máquina especificada de la base de datos.
     */
    public function destroy(Maquina $maquina): RedirectResponse
    {
        try {
            $codigo = $maquina->codigo;
            $maquina->delete();

            return redirect()->route('maquinas.index')
                ->with('success', "Máquina {$codigo} eliminada exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('maquinas.index')
                ->with('error', 'No se puede eliminar la máquina porque está asociada a lotes, inspecciones o alertas.');
        }
    }

    public function show(Maquina $maquina)
    {
        return redirect()->route('catalogos.index');
    }

    public function create()
    {
        return view('maquinas.create');
    }
}
