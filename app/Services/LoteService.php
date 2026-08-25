<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Maquina;
use App\Models\Producto;
use Carbon\Carbon;

class LoteService
{
    /**
     * Genera la cadena del código de lote siguiendo la nomenclatura:
     * [PrefijoLínea][Año 2 dígitos][Semana del año 2 dígitos][Código de Máquina]
     * Ejemplo: PET2635M04
     */
    public function generarCodigoLote(string $prefijoLinea, string $codigoMaquina, ?Carbon $fecha = null): string
    {
        $fecha = $fecha ?? Carbon::now();
        $anio = $fecha->format('y'); // Año a 2 dígitos (ej: '26')
        $semana = sprintf('%02d', $fecha->isoWeek); // Semana del año a 2 dígitos (ej: '35')

        $prefijoClean = strtoupper(trim($prefijoLinea));
        $maquinaClean = strtoupper(trim($codigoMaquina));

        return "{$prefijoClean}{$anio}{$semana}{$maquinaClean}";
    }

    /**
     * Crea y guarda un nuevo lote en la base de datos generando automáticamente su código
     * y calculando la equivalencia en kilos según el factor de conversión del producto.
     */
    public function crearLote(array $data): Lote
    {
        $producto = Producto::findOrFail($data['producto_id']);
        $maquina = Maquina::findOrFail($data['maquina_id']);

        $fechaProduccion = isset($data['fecha_produccion'])
            ? Carbon::parse($data['fecha_produccion'])
            : Carbon::now();

        // El prefijo de línea se toma del atributo 'prefijo' si existe, o del atributo 'codigo' del producto (ej: 'PET')
        $prefijoLinea = $producto->prefijo ?? $producto->codigo;

        $codigoLote = $this->generarCodigoLote($prefijoLinea, $maquina->codigo, $fechaProduccion);

        $cantidadUnidades = $data['cantidad_producida_unidades'] ?? null;
        $pesoTotalKg = $data['peso_total_kg'] ?? null;

        // Calcular peso_total_kg automáticamente si no viene dado y existe el factor de conversión
        if ($pesoTotalKg === null && $cantidadUnidades !== null && $producto->factor_conversion_kg) {
            $pesoTotalKg = round($cantidadUnidades * $producto->factor_conversion_kg, 2);
        }

        return Lote::create([
            'codigo_lote' => $codigoLote,
            'producto_id' => $producto->id,
            'maquina_id' => $maquina->id,
            'resina' => $data['resina'] ?? null,
            'fecha_produccion' => $fechaProduccion->toDateString(),
            'estado_lote' => $data['estado_lote'] ?? 'en_proceso',
            'cantidad_producida_unidades' => $cantidadUnidades,
            'peso_total_kg' => $pesoTotalKg,
        ]);
    }
}
