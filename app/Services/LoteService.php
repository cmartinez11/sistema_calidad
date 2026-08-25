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
     * Crea y guarda un nuevo lote en la base de datos aplicando los cálculos automáticos de planta:
     * - total_millares = cantidad_empaques * millares_presentacion
     * - peso_total_kg = gramaje * total_millares
     * - scrap_porcentaje = (scrap_kg / peso_total_kg) * 100
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

        // Cantidad producida en número de empaques (ej. 10 sacos, 20 cajas, 2 jumbos)
        $cantidadEmpaques = $data['cantidad_empaques'] ?? $data['cantidad_producida'] ?? null;
        
        // Millares por empaque del producto (ej. 1.5 para saco, 0.55 para caja, 15 para jumbo)
        $millaresPresentacion = (float) ($producto->millares_presentacion ?? 1.0);
        
        // Gramaje unitario de la preforma en gramos (ej. 28.00 g)
        $gramaje = (float) ($producto->gramaje ?? ($producto->factor_conversion_kg ? $producto->factor_conversion_kg * 1000 : 0));

        // 1. total_millares = cantidad_empaques * millares_presentacion
        $totalMillares = $cantidadEmpaques !== null ? round($cantidadEmpaques * $millaresPresentacion, 4) : ($data['total_millares'] ?? null);

        // Unidades totales individuales
        $cantidadUnidades = $totalMillares !== null ? (int) round($totalMillares * 1000) : ($data['cantidad_producida_unidades'] ?? null);

        // 2. total_kg = gramaje * total_millares (ej: 28g * 15 millares = 420 kg)
        $pesoTotalKg = ($gramaje > 0 && $totalMillares !== null)
            ? round($gramaje * $totalMillares, 2)
            : ($data['peso_total_kg'] ?? null);

        // 3. scrap_porcentaje = (scrap_kg / total_kg) * 100
        $scrapKg = (float) ($data['scrap_kg'] ?? 0.0);
        $scrapPorcentaje = ($pesoTotalKg > 0)
            ? round(($scrapKg / $pesoTotalKg) * 100, 2)
            : 0.00;

        return Lote::create([
            'codigo_lote' => $codigoLote,
            'producto_id' => $producto->id,
            'maquina_id' => $maquina->id,
            'resina' => $data['resina'] ?? null,
            'fecha_produccion' => $fechaProduccion->toDateString(),
            'estado_lote' => $data['estado_lote'] ?? 'en_proceso',
            'cantidad_empaques' => $cantidadEmpaques,
            'cantidad_producida_unidades' => $cantidadUnidades,
            'total_millares' => $totalMillares,
            'peso_total_kg' => $pesoTotalKg,
            'scrap_kg' => $scrapKg,
            'scrap_porcentaje' => $scrapPorcentaje,
        ]);
    }
}
