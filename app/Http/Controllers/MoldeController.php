<?php

namespace App\Http\Controllers;

use App\Models\Molde;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MoldeController extends Controller
{
    /**
     * Muestra el listado de moldes con búsqueda y paginación.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $moldes = Molde::query()
            ->when($search, function ($query, $search) {
                $query->where('codigo', 'ILIKE', "%{$search}%")
                    ->orWhere('nombre', 'ILIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('moldes.index', compact('moldes', 'search'));
    }

    /**
     * Muestra el formulario para crear un nuevo molde.
     */
    public function create(): View
    {
        return view('moldes.create');
    }

    /**
     * Almacena un nuevo molde en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:molde,codigo',
            'nombre' => 'required|string|max:100',
            'numero_cavidades' => 'required|integer|min:1',
            'activo' => 'nullable|boolean',
        ], [
            'codigo.required' => 'El código del molde es obligatorio.',
            'codigo.unique' => 'Ya existe un molde registrado con este código.',
            'nombre.required' => 'El nombre o descripción del molde es obligatorio.',
            'numero_cavidades.required' => 'El número de cavidades es obligatorio.',
            'numero_cavidades.integer' => 'El número de cavidades debe ser un número entero.',
            'numero_cavidades.min' => 'El número de cavidades debe ser al menos 1.',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : true;

        Molde::create($validated);

        return redirect()->route('moldes.index')
            ->with('success', "Molde {$validated['codigo']} registrado exitosamente.");
    }

    /**
     * Muestra el detalle del molde (redirecciona a index).
     */
    public function show(Molde $molde): RedirectResponse
    {
        return redirect()->route('moldes.index');
    }

    /**
     * Muestra el formulario para editar el molde especificado.
     */
    public function edit(Molde $molde): View
    {
        return view('moldes.edit', compact('molde'));
    }

    /**
     * Actualiza el molde especificado en la base de datos.
     */
    public function update(Request $request, Molde $molde): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:molde,codigo,' . $molde->id,
            'nombre' => 'required|string|max:100',
            'numero_cavidades' => 'required|integer|min:1',
            'activo' => 'nullable|boolean',
        ], [
            'codigo.required' => 'El código del molde es obligatorio.',
            'codigo.unique' => 'Ya existe otro molde registrado con este código.',
            'nombre.required' => 'El nombre o descripción del molde es obligatorio.',
            'numero_cavidades.required' => 'El número de cavidades es obligatorio.',
            'numero_cavidades.integer' => 'El número de cavidades debe ser un número entero.',
            'numero_cavidades.min' => 'El número de cavidades debe ser al menos 1.',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : false;

        $molde->update($validated);

        return redirect()->route('moldes.index')
            ->with('success', "Molde {$molde->codigo} actualizado exitosamente.");
    }

    /**
     * Elimina el molde especificado de la base de datos.
     */
    public function destroy(Molde $molde): RedirectResponse
    {
        try {
            $codigo = $molde->codigo;
            $molde->delete();

            return redirect()->route('moldes.index')
                ->with('success', "Molde {$codigo} eliminado exitosamente.");
        } catch (\Exception $e) {
            return redirect()->route('moldes.index')
                ->with('error', 'No se puede eliminar el molde porque está asociado a inspecciones o registros del sistema.');
        }
    }
}