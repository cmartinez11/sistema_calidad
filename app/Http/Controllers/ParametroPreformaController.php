<?php

namespace App\Http\Controllers;

use App\Models\MatrizEmpaque;
use App\Models\ParametroPreforma;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ParametroPreformaController extends Controller
{
    /**
     * Almacena o actualiza los parámetros técnicos de preforma y su matriz de empaque para un producto especificado.
     */
    public function storeOrUpdate(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'molde_id' => 'nullable|exists:molde,id',
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

            // Matriz de empaque opcional
            'caja_factor_millares' => 'nullable|numeric|min:0',
            'caja_factor_peso_kg' => 'nullable|numeric|min:0',
            'saco_factor_millares' => 'nullable|numeric|min:0',
            'saco_factor_peso_kg' => 'nullable|numeric|min:0',
        ], [
            'numero_cavidades.required' => 'El número de cavidades es obligatorio.',
            'peso_nominal.required' => 'El peso nominal es obligatorio.',
            'peso_min.required' => 'El peso mínimo es obligatorio.',
            'peso_max.required' => 'El peso máximo es obligatorio.',
            'molde_id.exists' => 'El molde seleccionado no es válido.',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : true;
        $validated['molde_id'] = $request->filled('molde_id') ? $request->molde_id : null;

        // Extraer campos no pertenecientes a parametros_preforma
        $cajaMillares = $request->filled('caja_factor_millares') ? (float)$request->caja_factor_millares : null;
        $cajaKg = $request->filled('caja_factor_peso_kg') ? (float)$request->caja_factor_peso_kg : null;
        $sacoMillares = $request->filled('saco_factor_millares') ? (float)$request->saco_factor_millares : null;
        $sacoKg = $request->filled('saco_factor_peso_kg') ? (float)$request->saco_factor_peso_kg : null;

        $paramData = collect($validated)->except([
            'caja_factor_millares',
            'caja_factor_peso_kg',
            'saco_factor_millares',
            'saco_factor_peso_kg'
        ])->toArray();

        ParametroPreforma::updateOrCreate(
            ['producto_id' => $producto->id],
            $paramData
        );

        $producto->update([
            'molde_id' => $validated['molde_id'],
            'peso_unitario' => $validated['peso_nominal'],
        ]);

        // Guardar o actualizar Matriz de Empaque para CAJA
        if ($cajaMillares !== null || $cajaKg !== null) {
            $millares = $cajaMillares ?? 0;
            $kg = $cajaKg ?? 0;
            MatrizEmpaque::updateOrCreate(
                ['producto_id' => $producto->id, 'presentacion' => 'CAJA'],
                [
                    'gramaje' => $validated['peso_nominal'],
                    'factor_millares' => $millares,
                    'factor_peso_kg' => $kg,
                    'cant_unidades' => (int) round($millares * 1000),
                ]
            );
        }

        // Guardar o actualizar Matriz de Empaque para SACO
        if ($sacoMillares !== null || $sacoKg !== null) {
            $millares = $sacoMillares ?? 0;
            $kg = $sacoKg ?? 0;
            MatrizEmpaque::updateOrCreate(
                ['producto_id' => $producto->id, 'presentacion' => 'SACO'],
                [
                    'gramaje' => $validated['peso_nominal'],
                    'factor_millares' => $millares,
                    'factor_peso_kg' => $kg,
                    'cant_unidades' => (int) round($millares * 1000),
                ]
            );
        }

        return redirect()->route('productos.index')
            ->with('success', "Parámetros técnicos y matriz de empaque para {$producto->codigo} guardados exitosamente.");
    }
}
