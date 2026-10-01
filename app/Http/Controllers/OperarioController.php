<?php

namespace App\Http\Controllers;

use App\Models\Operario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperarioController extends Controller
{
    /**
     * Muestra el listado de operarios y encargados con búsqueda y paginación.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $operarios = Operario::query()
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('codigo_operario', 'ILIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('operarios.index', compact('operarios', 'search'));
    }

    /**
     * Almacena un nuevo operario en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'codigo_operario' => 'nullable|string|max:50|unique:operarios,codigo_operario',
            'activo' => 'nullable|boolean',
        ], [
            'nombre.required' => 'El nombre del operario es obligatorio.',
            'codigo_operario.unique' => 'Ya existe un operario registrado con este Código / DNI.',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : true;

        Operario::create($validated);

        return redirect()->route('operarios.index')
            ->with('success', "Operario {$validated['nombre']} registrado exitosamente.");
    }

    /**
     * Actualiza el operario especificado en la base de datos.
     */
    public function update(Request $request, Operario $operario): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'codigo_operario' => 'nullable|string|max:50|unique:operarios,codigo_operario,' . $operario->id,
            'activo' => 'nullable|boolean',
        ], [
            'nombre.required' => 'El nombre del operario es obligatorio.',
            'codigo_operario.unique' => 'Ya existe otro operario registrado con este Código / DNI.',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : false;

        $operario->update($validated);

        return redirect()->route('operarios.index')
            ->with('success', "Operario {$operario->nombre} actualizado exitosamente.");
    }

    /**
     * Elimina el operario especificado de la base de datos.
     */
    public function destroy(Operario $operario): RedirectResponse
    {
        try {
            $nombre = $operario->nombre;
            $operario->delete();

            return redirect()->route('operarios.index')
                ->with('success', "Operario {$nombre} eliminado exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('operarios.index')
                ->with('error', 'No se puede eliminar el operario porque está asociado a registros de producción o inspecciones.');
        }
    }

    public function create(){
        return view('operarios.create');
    }
}
