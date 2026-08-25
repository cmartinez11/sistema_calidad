<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Muestra el listado de productos con paginación y búsqueda.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $productos = Producto::query()
            ->when($search, function ($query, $search) {
                $query->where('codigo', 'ILIKE', "%{$search}%")
                    ->orWhere('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('presentacion', 'ILIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('productos.index', compact('productos', 'search'));
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:productos,codigo',
            'nombre' => 'required|string|max:150',
            'presentacion' => 'required|string|in:Caja,Saco,Jumbo,Bolsa',
            'millares_presentacion' => 'required|numeric|min:0.0001',
            'gramaje' => 'required|numeric|min:0.01',
            'unidad_peso' => 'required|string|max:20',
            'unidad_dimension' => 'required|string|max:20',
            'unidad_produccion' => 'required|string|max:20',
            'factor_conversion_kg' => 'nullable|numeric|min:0',
            'activo' => 'nullable|boolean',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : true;

        // Calcular factor_conversion_kg automáticamente si no viene dado (gramaje / 1000 * millares_presentacion)
        if (empty($validated['factor_conversion_kg']) && isset($validated['gramaje'])) {
            $validated['factor_conversion_kg'] = round(($validated['gramaje'] / 1000) * $validated['millares_presentacion'], 4);
        }

        Producto::create($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto registrado exitosamente.');
    }

    /**
     * Actualiza el producto especificado en la base de datos.
     */
    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:productos,codigo,' . $producto->id,
            'nombre' => 'required|string|max:150',
            'presentacion' => 'required|string|in:Caja,Saco,Jumbo,Bolsa',
            'millares_presentacion' => 'required|numeric|min:0.0001',
            'gramaje' => 'required|numeric|min:0.01',
            'unidad_peso' => 'required|string|max:20',
            'unidad_dimension' => 'required|string|max:20',
            'unidad_produccion' => 'required|string|max:20',
            'factor_conversion_kg' => 'nullable|numeric|min:0',
            'activo' => 'nullable|boolean',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : false;

        if (empty($validated['factor_conversion_kg']) && isset($validated['gramaje'])) {
            $validated['factor_conversion_kg'] = round(($validated['gramaje'] / 1000) * $validated['millares_presentacion'], 4);
        }

        $producto->update($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Elimina el producto especificado de la base de datos.
     */
    public function destroy(Producto $producto): RedirectResponse
    {
        try {
            $producto->delete();
            return redirect()->route('productos.index')
                ->with('success', 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('productos.index')
                ->with('error', 'No se puede eliminar el producto porque está asociado a lotes o parámetros.');
        }
    }
}
