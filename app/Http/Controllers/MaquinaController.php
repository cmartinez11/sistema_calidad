<?php

namespace App\Http\Controllers;

use App\Models\Maquina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaquinaController extends Controller
{
    /**
     * Muestra el listado de máquinas e inyectoras con paginación y búsqueda.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $maquinas = Maquina::query()
            ->when($search, function ($query, $search) {
                $query->where('codigo', 'ILIKE', "%{$search}%")
                    ->orWhere('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('estado', 'ILIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('maquinas.index', compact('maquinas', 'search'));
    }

    /**
     * Almacena una nueva máquina en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:maquinas,codigo',
            'nombre' => 'required|string|max:100',
            'estado' => 'required|string|in:activo,mantenimiento,inactivo',
        ], [
            'codigo.required' => 'El código de la máquina es obligatorio.',
            'codigo.unique' => 'Ya existe una máquina registrada con este código.',
            'nombre.required' => 'El nombre o descripción de la máquina es obligatorio.',
            'estado.required' => 'El estado operativo de la máquina es obligatorio.',
        ]);

        Maquina::create($validated);

        return redirect()->route('maquinas.index')
            ->with('success', "Máquina {$validated['codigo']} registrada exitosamente.");
    }

    /**
     * Actualiza la máquina especificada en la base de datos.
     */
    public function update(Request $request, Maquina $maquina): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:maquinas,codigo,' . $maquina->id,
            'nombre' => 'required|string|max:100',
            'estado' => 'required|string|in:activo,mantenimiento,inactivo',
        ], [
            'codigo.required' => 'El código de la máquina es obligatorio.',
            'codigo.unique' => 'Ya existe otra máquina registrada con este código.',
            'nombre.required' => 'El nombre o descripción de la máquina es obligatorio.',
            'estado.required' => 'El estado operativo de la máquina es obligatorio.',
        ]);

        $maquina->update($validated);

        return redirect()->route('maquinas.index')
            ->with('success', "Máquina {$maquina->codigo} actualizada exitosamente.");
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
}
