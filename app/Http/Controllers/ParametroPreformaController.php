<?php

namespace App\Http\Controllers;

use App\Models\ParametroPreforma;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ParametroPreformaController extends Controller
{
    /**
     * Almacena o actualiza los parámetros técnicos de preforma para un producto especificado.
     */
    public function storeOrUpdate(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'numero_cavidades' => 'required|integer|min:0',
            'peso_nominal' => 'required|numeric|min:0',
            'peso_min' => 'required|numeric|min:0',
            'peso_max' => 'required|numeric|min:0',
            'esp_pared_min' => 'nullable|numeric|min:0',
            'esp_pared_max' => 'nullable|numeric|min:0',
            'esp_fondo_min' => 'nullable|numeric|min:0',
            'esp_fondo_max' => 'nullable|numeric|min:0',
            'altura_min' => 'nullable|numeric|min:0',
            'altura_max' => 'nullable|numeric|min:0',
            'activo' => 'nullable|boolean',
        ], [
            'numero_cavidades.required' => 'El número de cavidades es obligatorio.',
            'peso_nominal.required' => 'El peso nominal es obligatorio.',
            'peso_min.required' => 'El peso mínimo es obligatorio.',
            'peso_max.required' => 'El peso máximo es obligatorio.',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : true;

        ParametroPreforma::updateOrCreate(
            ['producto_id' => $producto->id],
            $validated
        );

        return redirect()->route('productos.index')
            ->with('success', "Parámetros técnicos de preforma para {$producto->codigo} guardados exitosamente.");
    }
}
