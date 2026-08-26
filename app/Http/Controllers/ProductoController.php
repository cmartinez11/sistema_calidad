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
            ->with('parametroPreforma')
            ->when($search, function ($query, $search) {
                $query->where('codigo', 'ILIKE', "%{$search}%")
                    ->orWhere('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('tipo_producto', 'ILIKE', "%{$search}%")
                    ->orWhere('unidad_medida', 'ILIKE', "%{$search}%");
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
            'tipo_producto' => 'required|string|in:PREFORMA,TERMO,LAMINADO',
            'unidad_medida' => 'required|string|max:50',
            'peso_unitario' => 'nullable|numeric|min:0',
            'activo' => 'nullable|boolean',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : true;

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
            'tipo_producto' => 'required|string|in:PREFORMA,TERMO,LAMINADO',
            'unidad_medida' => 'required|string|max:50',
            'peso_unitario' => 'nullable|numeric|min:0',
            'activo' => 'nullable|boolean',
        ]);

        $validated['activo'] = $request->has('activo') ? (bool)$request->activo : false;

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

    /**
     * Descarga la plantilla CSV de ejemplo para la subida masiva de productos.
     */
    public function downloadPlantilla(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_productos_fenix.csv"',
        ];

        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');

            // Insertar UTF-8 BOM para compatibilidad directa con MS Excel
            fputs($file, "\xEF\xBB\xBF");

            // Encabezados
            fputcsv($file, [
                'codigo',
                'nombre',
                'tipo_producto',
                'unidad_medida',
                'peso_unitario',
            ]);

            // Filas de ejemplo
            fputcsv($file, [
                'PET-500ML-28G',
                'Preforma PET 500ml 28g',
                'PREFORMA',
                'UNIDADES',
                '28.0000',
            ]);

            fputcsv($file, [
                'TERMO-VASO-16OZ',
                'Vaso Termoformado 16oz',
                'TERMO',
                'CAJAS',
                '12.5000',
            ]);

            fputcsv($file, [
                'LAM-BOBINA-BOPP',
                'Laminado Bobina BOPP 20 mic',
                'LAMINADO',
                'KILOS',
                '250.0000',
            ]);

            fclose($file);
        }, 'plantilla_productos_fenix.csv', $headers);
    }

    /**
     * Procesa la importación masiva de productos mediante archivo Excel o CSV.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo para importar.',
            'archivo.mimes' => 'El archivo debe ser de formato CSV (.csv) o Excel (.xlsx, .xls).',
            'archivo.max' => 'El tamaño máximo permitido para el archivo es 10 MB.',
        ]);

        $file = $request->file('archivo');
        $path = $file->getRealPath();

        $rows = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $firstLine = fgets($handle);
            $delimiter = (substr_count((string)$firstLine, ';') > substr_count((string)$firstLine, ',')) ? ';' : ',';
            rewind($handle);
            if ($bom === "\xEF\xBB\xBF") {
                fseek($handle, 3);
            }

            while (($data = fgetcsv($handle, 2048, $delimiter)) !== false) {
                $cleanRow = array_map(fn($v) => trim((string)$v), $data);
                if (array_filter($cleanRow)) {
                    $rows[] = $cleanRow;
                }
            }
            fclose($handle);
        }

        if (empty($rows)) {
            return redirect()->route('productos.index')
                ->with('error', 'El archivo proporcionado está vacío o no tiene contenido válido.');
        }

        $headerRow = array_map(fn($col) => strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', $col))), array_shift($rows));

        $colMap = [
            'codigo' => array_search('codigo', $headerRow),
            'nombre' => array_search('nombre', $headerRow),
            'tipo_producto' => array_search('tipo_producto', $headerRow) !== false 
                ? array_search('tipo_producto', $headerRow) 
                : array_search('tipo', $headerRow),
            'unidad_medida' => array_search('unidad_medida', $headerRow) !== false 
                ? array_search('unidad_medida', $headerRow) 
                : array_search('unidad', $headerRow),
            'peso_unitario' => array_search('peso_unitario', $headerRow) !== false 
                ? array_search('peso_unitario', $headerRow) 
                : array_search('peso', $headerRow),
        ];

        if ($colMap['codigo'] === false || $colMap['nombre'] === false) {
            return redirect()->route('productos.index')
                ->with('error', 'El archivo no contiene las columnas requeridas ("codigo" y "nombre"). Descarga la plantilla de ejemplo para guiarte.');
        }

        $importedCount = 0;
        $updatedCount = 0;

        foreach ($rows as $row) {
            $codigo = isset($row[$colMap['codigo']]) ? trim($row[$colMap['codigo']]) : null;
            $nombre = isset($row[$colMap['nombre']]) ? trim($row[$colMap['nombre']]) : null;

            if (empty($codigo) || empty($nombre)) {
                continue;
            }

            $tipoRaw = $colMap['tipo_producto'] !== false && isset($row[$colMap['tipo_producto']]) ? strtoupper(trim($row[$colMap['tipo_producto']])) : 'PREFORMA';
            $validTipos = ['PREFORMA', 'TERMO', 'LAMINADO'];
            $tipoProducto = in_array($tipoRaw, $validTipos) ? $tipoRaw : 'PREFORMA';

            $unidadMedida = $colMap['unidad_medida'] !== false && !empty($row[$colMap['unidad_medida']]) ? strtoupper(trim($row[$colMap['unidad_medida']])) : 'UNIDADES';

            $pesoVal = $colMap['peso_unitario'] !== false && isset($row[$colMap['peso_unitario']]) && $row[$colMap['peso_unitario']] !== '' ? (float) str_replace(',', '.', $row[$colMap['peso_unitario']]) : null;
            $pesoUnitario = ($pesoVal !== null && $pesoVal >= 0) ? $pesoVal : null;

            $producto = Producto::where('codigo', $codigo)->first();

            if ($producto) {
                $producto->update([
                    'nombre' => $nombre,
                    'tipo_producto' => $tipoProducto,
                    'unidad_medida' => $unidadMedida,
                    'peso_unitario' => $pesoUnitario,
                    'activo' => true,
                ]);
                $updatedCount++;
            } else {
                Producto::create([
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'tipo_producto' => $tipoProducto,
                    'unidad_medida' => $unidadMedida,
                    'peso_unitario' => $pesoUnitario,
                    'activo' => true,
                ]);
                $importedCount++;
            }
        }

        $totalProcesados = $importedCount + $updatedCount;

        if ($totalProcesados === 0) {
            return redirect()->route('productos.index')
                ->with('error', 'No se pudieron procesar filas válidas del archivo.');
        }

        $msg = "Importación completada exitosamente: {$importedCount} productos nuevos registrados y {$updatedCount} actualizados.";

        return redirect()->route('productos.index')->with('success', $msg);
    }
}
