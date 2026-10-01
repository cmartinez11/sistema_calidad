<?php

namespace App\Http\Controllers;

use App\Models\Resina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResinaController extends Controller
{
    /**
     * Muestra el listado de resinas con búsqueda y paginación.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $resinas = Resina::query()
            ->when($search, function ($query, $search) {
                $query->where('codigo', 'ILIKE', "%{$search}%")
                    ->orWhere('nombre', 'ILIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('resinas.index', compact('resinas', 'search'));
    }

    /**
     * Muestra el formulario para crear una nueva resina.
     */
    public function create(): View
    {
        return view('resinas.create');
    }

    /**
     * Almacena una nueva resina en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:resinas,codigo',
            'nombre' => 'required|string|max:100',
            'activo' => 'nullable|boolean',
        ], [
            'codigo.required' => 'El código de la resina es obligatorio.',
            'codigo.unique' => 'Ya existe una resina registrada con este código.',
            'nombre.required' => 'El nombre de la resina es obligatorio.',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : true;

        Resina::create($validated);

        return redirect()->route('resinas.index')
            ->with('success', "Resina {$validated['codigo']} registrada exitosamente.");
    }

    /**
     * Muestra el detalle de la resina (redirecciona a index).
     */
    public function show(Resina $resina): RedirectResponse
    {
        return redirect()->route('resinas.index');
    }

    /**
     * Muestra el formulario para editar la resina especificada.
     */
    public function edit(Resina $resina): View
    {
        return view('resinas.edit', compact('resina'));
    }

    /**
     * Actualiza la resina especificada en la base de datos.
     */
    public function update(Request $request, Resina $resina): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:resinas,codigo,' . $resina->id,
            'nombre' => 'required|string|max:100',
            'activo' => 'nullable|boolean',
        ], [
            'codigo.required' => 'El código de la resina es obligatorio.',
            'codigo.unique' => 'Ya existe otra resina registrada con este código.',
            'nombre.required' => 'El nombre de la resina es obligatorio.',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : false;

        $resina->update($validated);

        return redirect()->route('resinas.index')
            ->with('success', "Resina {$resina->codigo} actualizada exitosamente.");
    }

    /**
     * Elimina la resina especificada de la base de datos.
     */
    public function destroy(Resina $resina): RedirectResponse
    {
        try {
            $codigo = $resina->codigo;
            $resina->delete();

            return redirect()->route('resinas.index')
                ->with('success', "Resina {$codigo} eliminada exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('resinas.index')
                ->with('error', 'No se puede eliminar la resina porque está asociada a inspecciones o registros del sistema.');
        }
    }
}
