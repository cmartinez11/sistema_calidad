<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            ->paginate(20)
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
     * Procesa la importación masiva de productos mediante archivo Excel (.xlsx, .xls) o CSV (.csv, .txt).
     */
    public function import(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'archivo' => 'required|file|max:10240',
            ], [
                'archivo.required' => 'Debes seleccionar un archivo para importar.',
                'archivo.max' => 'El tamaño máximo permitido es 10 MB.',
            ]);

            $file = $request->file('archivo');
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->getRealPath();
            $rows = [];

            if (in_array($extension, ['xlsx', 'xls'])) {
                try {
                    $spreadsheet = IOFactory::load($path);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $spreadsheetRows = $worksheet->toArray(null, true, true, false);

                    foreach ($spreadsheetRows as $r) {
                        $cleanRow = array_map(fn($v) => trim((string)$v), $r);
                        if (array_filter($cleanRow)) {
                            $rows[] = $cleanRow;
                        }
                    }
                } catch (\Exception $e) {
                    return redirect()->route('productos.index')
                        ->with('error', 'Error al leer el archivo Excel (.xlsx / .xls): ' . $e->getMessage());
                }
            } else {
                $content = file_get_contents($path);
                $content = str_replace("\xEF\xBB\xBF", '', $content); // Quitar BOM
                
                $lines = preg_split("/\r\n|\n|\r/", $content);
                $lines = array_filter(array_map('trim', $lines));

                if (!empty($lines)) {
                    $firstLine = reset($lines);
                    $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

                    foreach ($lines as $line) {
                        if (empty($line)) continue;
                        $data = str_getcsv($line, $delimiter);
                        $cleanRow = array_map(fn($val) => trim(str_replace(['"', "'"], '', (string)$val)), $data);
                        if (array_filter($cleanRow)) {
                            $rows[] = $cleanRow;
                        }
                    }
                }
            }

            if (empty($rows)) {
                return redirect()->route('productos.index')
                    ->with('error', 'El archivo proporcionado está vacío o no contiene filas válidas.');
            }

            // Función auxiliar para normalizar textos de cabecera
            $normalize = function ($str) {
                $str = mb_strtolower(trim((string)$str));
                $search = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', '"', "'"];
                $replace = ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n', '', ''];
                return str_replace($search, $replace, $str);
            };

            // Limpiar cabeceras aplicando normalización
            $rawHeader = array_shift($rows);
            $headerRow = array_map(fn($col) => $normalize(preg_replace('/[^a-zA-Z0-9_áéíóúÁÉÍÓÚñÑ]/', '', $col)), $rawHeader);

            // Búsqueda flexible de columnas clave
            $findCol = function(array $aliases) use ($headerRow) {
                foreach ($aliases as $alias) {
                    $index = array_search($alias, $headerRow);
                    if ($index !== false) return $index;
                }
                return false;
            };

            $colCode = $findCol(['codigo', 'cod']);
            $colName = $findCol(['nombre', 'descripcion', 'producto']);
            $colType = $findCol(['tipo_producto', 'tipo']);
            $colUnit = $findCol(['unidad_medida', 'unidad', 'medida']);
            $colPeso = $findCol(['peso_unitario', 'peso', 'gramaje']);

            if ($colCode === false || $colName === false) {
                return redirect()->route('productos.index')
                    ->with('error', 'El archivo no contiene las columnas requeridas ("codigo" y "nombre"). Revisa la plantilla.');
            }

            $importedCount = 0;
            $updatedCount = 0;

            foreach ($rows as $row) {
                $codigo = isset($row[$colCode]) ? trim($row[$colCode]) : null;
                
                // Limpieza y conversión blindada a UTF-8 para evitar errores en PostgreSQL
                $nombreRaw = isset($row[$colName]) ? trim($row[$colName]) : null;
                $nombre = $nombreRaw ? mb_convert_encoding($nombreRaw, 'UTF-8', 'UTF-8, ISO-8859-1, WINDOWS-1252') : null;

                if (empty($codigo) || empty($nombre)) {
                    continue;
                }

                $tipoRaw = ($colType !== false && isset($row[$colType])) ? strtoupper(trim($row[$colType])) : 'PREFORMA';
                $validTipos = ['PREFORMA', 'TERMO', 'LAMINADO'];
                $tipoProducto = in_array($tipoRaw, $validTipos) ? $tipoRaw : 'PREFORMA';

                $unidadRaw = ($colUnit !== false && !empty($row[$colUnit])) ? trim($row[$colUnit]) : 'UNIDADES';
                $unidadMedida = mb_convert_encoding(strtoupper($unidadRaw), 'UTF-8', 'UTF-8, ISO-8859-1, WINDOWS-1252');

                $pesoVal = null;
                if ($colPeso !== false && isset($row[$colPeso]) && $row[$colPeso] !== '') {
                    $pesoVal = (float) str_replace(',', '.', $row[$colPeso]);
                }
                $pesoUnitario = ($pesoVal !== null && $pesoVal >= 0) ? $pesoVal : null;

                $producto = Producto::updateOrCreate(
                    ['codigo' => $codigo],
                    [
                        'nombre' => $nombre,
                        'tipo_producto' => $tipoProducto,
                        'unidad_medida' => $unidadMedida,
                        'peso_unitario' => $pesoUnitario,
                        'activo' => true,
                    ]
                );

                if ($producto->wasRecentlyCreated) {
                    $importedCount++;
                } else {
                    $updatedCount++;
                }
            }

            $totalProcesados = $importedCount + $updatedCount;

            if ($totalProcesados === 0) {
                return redirect()->route('productos.index')
                    ->with('error', 'No se pudieron procesar filas válidas del archivo.');
            }

            $msg = "Importación completada exitosamente: {$importedCount} productos nuevos registrados y {$updatedCount} actualizados.";

            return redirect()->route('productos.index')->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->route('productos.index')
                ->with('error', 'Ocurrió un error inesperado al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Devuelve en formato JSON los parámetros técnicos (cavidades, tolerancias de peso) del producto.
     */
    public function getParametrosJson(Producto $producto)
    {
        $producto->load('parametroPreforma');
        $param = $producto->parametroPreforma;

        return response()->json([
            'codigo' => $producto->codigo,
            'nombre' => $producto->nombre,
            'numero_cavidades' => $param->numero_cavidades ?? 1,
            'peso_nominal' => $param->peso_nominal ?? 0,
            'peso_min' => $param->peso_min ?? 0,
            'peso_max' => $param->peso_max ?? 0,
            
            // Usando los nombres exactos que muestra tu base de datos pgAdmin
            'espesor_pared_min' => $param->esp_pared_min ?? 0,
            'espesor_pared_max' => $param->esp_pared_max ?? 0,
            'espesor_fondo_min' => $param->esp_fondo_min ?? 0,
            'espesor_fondo_max' => $param->esp_fondo_max ?? 0,
            'altura_min' => $param->altura_min ?? 0,
            'altura_max' => $param->altura_max ?? 0,
        ]);
    }
}
