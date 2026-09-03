<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InspeccionCalidad;
use App\Models\InspeccionCavidad;
use App\Models\Lote;
use App\Models\Maquina;
use App\Models\Molde;
use App\Models\Operario;
use App\Models\Producto;
use App\Models\Resina;
use App\Models\Turno;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InspeccionCavidadController extends Controller
{
    /**
     * Muestra el historial de inspecciones por cavidades agrupadas por codigo_inspeccion.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $inspecciones = InspeccionCavidad::query()
            ->with(['producto', 'maquina', 'operario', 'turno', 'user'])
            ->whereNotNull('codigo_inspeccion')
            ->where('codigo_inspeccion', '!=', '')
            ->when($search, function ($query, $search) {
                $query->where('codigo_inspeccion', 'ILIKE', "%{$search}%")
                    ->orWhereHas('producto', fn($q) => $q->where('codigo', 'ILIKE', "%{$search}%")->orWhere('nombre', 'ILIKE', "%{$search}%"))
                    ->orWhereHas('maquina', fn($q) => $q->where('codigo', 'ILIKE', "%{$search}%")->orWhere('nombre', 'ILIKE', "%{$search}%"))
                    ->orWhereHas('operario', fn($q) => $q->where('nombre', 'ILIKE', "%{$search}%"));
            })
            ->select(
                'codigo_inspeccion',
                'producto_id',
                'maquina_id',
                'operario_id',
                'turno_id',
                'user_id',
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('COUNT(*) as total_cavidades'),
                DB::raw("COUNT(CASE WHEN estado IN ('FUERA_DE_RANGO', 'OBSERVADO') THEN 1 END) as defectos_count"),
                DB::raw("COUNT(CASE WHEN estado = 'PASABLE' THEN 1 END) as pasables_count")
            )
            ->groupBy('codigo_inspeccion', 'producto_id', 'maquina_id', 'operario_id', 'turno_id', 'user_id')
            ->orderBy(DB::raw('MIN(created_at)'), 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('inspecciones_cavidades.index', compact('inspecciones', 'search'));
    }

    /**
     * Muestra la vista interactiva para el registro de pesos cavidad por cavidad.
     */
    public function create(Request $request): View
    {
        $productos = Producto::with('parametroPreforma')
            ->where('activo', true)
            ->orderBy('nombre', 'asc')
            ->get();

        $maquinas = Maquina::where('estado', 'activo')
            ->orderBy('codigo', 'asc')
            ->get();

        $moldes = Molde::where('activo', true)->orderBy('codigo', 'asc')->get();

        $operarios = Operario::where('activo', true)
            ->orderBy('nombre', 'asc')
            ->get();

        $turnos = Turno::all();

        $resinas = Resina::where('activo', true)->orderBy('codigo', 'asc')->get();

        $selectedProductoId = null;

        return view('inspecciones_cavidades.create', compact(
            'productos',
            'maquinas',
            'operarios',
            'turnos',
            'selectedProductoId',
            'moldes',
            'resinas'
        ));
    }

    /**
     * Descarga la plantilla oficial en formato nativo de Excel (.xlsx) con columnas separadas (Columna A a G).
     */
    public function downloadPlantillaExcel(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Cavidades');

        // Encabezados exactos según requerimiento
        $headers = [
            'CAVIDAD',
            'PESO (G)',
            'ESP. PARED',
            'ESP. FONDO',
            'ALTURA',
            'TIENE DEFECTO',
            'OBSERVACIONES'
        ];

        // Escribir encabezados en la fila 1
        $sheet->fromArray([$headers], null, 'A1');

        // Formato de estilos de cabecera
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '30732B'], // Color corporativo Grupo Fénix #30732B
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Filas de datos de ejemplo (Cavidades 1 a 16)
        $sampleData = [];
        for ($i = 1; $i <= 16; $i++) {
            $sampleData[] = [
                'C-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                28.00,
                2.500,
                3.100,
                150.00,
                0,
                ''
            ];
        }

        $sheet->fromArray($sampleData, null, 'A2');

        // Autoajustar ancho de columnas de la A a la G
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $responseHeaders = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plantilla_medicion_cavidades_fenix.xlsx"',
            'Cache-Control' => 'max-age=0',
        ];

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'plantilla_medicion_cavidades_fenix.xlsx', $responseHeaders);
    }

    /**
     * Procesa la lectura masiva del archivo Excel (.xlsx, .xls) e inserta los datos en la grilla en el frontend.
     */
    public function procesarExcel(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|max:10240',
            ], [
                'excel_file.required' => 'Debes seleccionar un archivo Excel (.xlsx, .xls) válido.',
                'excel_file.max' => 'El tamaño del archivo no puede superar los 10 MB.',
            ]);

            $file = $request->file('excel_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->getRealPath();
            $rows = [];

            if (in_array($extension, ['xlsx', 'xls'])) {
                $spreadsheet = IOFactory::load($path);
                $worksheet = $spreadsheet->getActiveSheet();
                $spreadsheetRows = $worksheet->toArray(null, true, true, false);

                foreach ($spreadsheetRows as $r) {
                    $cleanRow = array_map(fn($v) => trim((string)$v), $r);
                    if (array_filter($cleanRow)) {
                        $rows[] = $cleanRow;
                    }
                }
            } else {
                $content = file_get_contents($path);
                $content = str_replace("\xEF\xBB\xBF", '', $content);
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
                return response()->json(['success' => false, 'message' => 'El archivo Excel no contiene filas legibles.'], 422);
            }

            // Función de normalización flexible para encabezados
            $normalize = function ($str) {
                $str = mb_strtolower(trim((string)$str));
                $search = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', '"', "'", '(', ')', '.', ' ', '-'];
                $replace = ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n', '', '', '', '', '', '_', '_'];
                $str = str_replace($search, $replace, $str);
                return preg_replace('/_+/', '_', trim($str, '_'));
            };

            $rawHeader = array_shift($rows);
            $headerRow = array_map(fn($col) => $normalize($col), $rawHeader);

            $findCol = function(array $aliases) use ($headerRow) {
                foreach ($aliases as $alias) {
                    $index = array_search($alias, $headerRow);
                    if ($index !== false) return $index;
                }
                return false;
            };

            // Mapeo flexible de alias para cada columna A-G
            $colCavidad = $findCol(['cavidad', 'cav', 'c', 'num_cavidad']);
            $colPeso = $findCol(['peso_g', 'peso', 'peso_medido', 'pesog']);
            $colPared = $findCol(['esp_pared', 'espesor_pared', 'pared']);
            $colFondo = $findCol(['esp_fondo', 'espesor_fondo', 'fondo']);
            $colAltura = $findCol(['altura', 'alt']);
            $colDefecto = $findCol(['tiene_defecto', 'defecto', 'falla']);
            $colObs = $findCol(['observaciones', 'observacion', 'comentarios']);

            // Si los encabezados no se encuentran por nombre, asumir por posición fija A-G
            if ($colCavidad === false) $colCavidad = 0;
            if ($colPeso === false) $colPeso = 1;
            if ($colPared === false) $colPared = 2;
            if ($colFondo === false) $colFondo = 3;
            if ($colAltura === false) $colAltura = 4;
            if ($colDefecto === false) $colDefecto = 5;
            if ($colObs === false) $colObs = 6;

            $cavidadesImportadas = [];
            $rowCounter = 1;

            foreach ($rows as $row) {
                $cavidadRaw = isset($row[$colCavidad]) ? $row[$colCavidad] : (string)$rowCounter;
                $cavNum = (int) preg_replace('/[^0-9]/', '', $cavidadRaw);
                if ($cavNum <= 0) {
                    $cavNum = $rowCounter;
                }

                $pesoVal = (isset($row[$colPeso]) && $row[$colPeso] !== '') ? (float)str_replace(',', '.', $row[$colPeso]) : '';
                $paredVal = (isset($row[$colPared]) && $row[$colPared] !== '') ? (float)str_replace(',', '.', $row[$colPared]) : '';
                $fondoVal = (isset($row[$colFondo]) && $row[$colFondo] !== '') ? (float)str_replace(',', '.', $row[$colFondo]) : '';
                $alturaVal = (isset($row[$colAltura]) && $row[$colAltura] !== '') ? (float)str_replace(',', '.', $row[$colAltura]) : '';

                $defectoRaw = isset($row[$colDefecto]) ? trim($row[$colDefecto]) : '0';
                $tieneDefecto = in_array(strtolower($defectoRaw), ['1', 'si', 'sí', 'true', 's']);

                $obsVal = isset($row[$colObs]) ? trim($row[$colObs]) : '';

                $cavidadesImportadas[] = [
                    'cavidad_numero' => $cavNum,
                    'peso_medido' => $pesoVal !== '' ? $pesoVal : '',
                    'espesor_pared' => $paredVal !== '' ? $paredVal : '',
                    'espesor_fondo' => $fondoVal !== '' ? $fondoVal : '',
                    'altura' => $alturaVal !== '' ? $alturaVal : '',
                    'tiene_defecto' => $tieneDefecto,
                    'observaciones' => $obsVal,
                ];

                $rowCounter++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Mediciones de Excel leídas exitosamente.',
                'cavidades' => $cavidadesImportadas,
                'count' => count($cavidadesImportadas)
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al procesar archivo Excel: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Almacena el pesaje por cavidades e inserta automáticamente un resumen consolidado en inspecciones_calidad.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'maquina_id' => 'nullable|exists:maquinas,id',
            'molde_id' => 'nullable|exists:molde,id',
            'operario_id' => 'nullable|exists:operarios,id',
            'turno_id' => 'nullable|exists:turnos,id',
            'resina_id'=> 'nullable|exists:resinas,id',
            'cavidades' => 'required|array|min:1',
            'cavidades.*.cavidad_numero' => 'required|integer|min:1',
            'cavidades.*.peso_medido' => 'required_unless:cavidades.*.estado,ANULADO|nullable|numeric|min:0',
            'cavidades.*.espesor_pared' => 'nullable|numeric|min:0',
            'cavidades.*.espesor_fondo' => 'nullable|numeric|min:0',
            'cavidades.*.altura' => 'nullable|numeric|min:0',
            'cavidades.*.estado' => 'required|string|in:CONFORME,FUERA_DE_RANGO,OBSERVADO,PASABLE,ANULADO',
            'cavidades.*.motivo_scrap' => 'nullable|string|max:100',
            'cavidades.*.observaciones' => 'nullable|string|max:255',
        ], [
            'producto_id.required' => 'Debes seleccionar un producto válido.',
            'cavidades.required' => 'Debes registrar al menos una cavidad.',
            'cavidades.*.peso_medido.required' => 'El peso real es obligatorio en todas las cavidades activas.',
        ]);

        DB::beginTransaction();

        try {
            $producto = Producto::findOrFail($validated['producto_id']);
            $userId = Auth::id();
            $defectosCount = 0;
            $pasablesCount = 0;

            // Generar código único correlativo para la inspección (ej: CAV-20260831-0001)
            $prefix = 'CAV-' . date('Ymd') . '-';
            $latestCode = InspeccionCavidad::where('codigo_inspeccion', 'LIKE', "{$prefix}%")
                ->max('codigo_inspeccion');

            $seq = $latestCode ? ((int) substr($latestCode, -4) + 1) : 1;
            $codigoInspeccion = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Colecciones para calcular extremos (Min / Max)
            $pesos = [];
            $paredes = [];
            $fondos = [];
            $alturas = [];
            $motivosScrap = [];
            $observaciones = [];

            // 1. Inserción detallada e intacta cavidad por cavidad
            foreach ($validated['cavidades'] as $cavData) {
                $estadoClean = strtoupper(str_replace(' ', '_', $cavData['estado']));
                $isAnulado = ($estadoClean === 'ANULADO' || !empty($cavData['anulado']) || !empty($cavData['anulada']));

                if ($isAnulado) {
                    $estadoClean = 'ANULADO';
                }
                
                if (in_array($estadoClean, ['FUERA_DE_RANGO', 'OBSERVADO'])) {
                    $defectosCount++;
                } elseif ($estadoClean === 'PASABLE') {
                    $pasablesCount++;
                }

                if (in_array($estadoClean, ['FUERA_DE_RANGO', 'OBSERVADO'])) {
                    $motivo = $cavData['motivo_scrap'] ?? 'Sin especificar';
                } elseif ($estadoClean === 'PASABLE') {
                    $motivo = !empty($cavData['motivo_scrap']) ? $cavData['motivo_scrap'] : null;
                } else {
                    $motivo = null;
                }

                if ($motivo && !in_array($motivo, $motivosScrap)) {
                    $motivosScrap[] = $motivo;
                }

                if (!empty($cavData['observaciones']) && !in_array($cavData['observaciones'], $observaciones)) {
                    $observaciones[] = $cavData['observaciones'];
                }

                // Asignar valores predeterminados seguros (0.00) si la cavidad está ANULADA para evitar violaciones NOT NULL en PostgreSQL
                if ($isAnulado) {
                    $pesoMedido = (isset($cavData['peso_medido']) && $cavData['peso_medido'] !== '') ? (float)$cavData['peso_medido'] : 0.00;
                    $espesorPared = (isset($cavData['espesor_pared']) && $cavData['espesor_pared'] !== '') ? (float)$cavData['espesor_pared'] : 0.00;
                    $espesorFondo = (isset($cavData['espesor_fondo']) && $cavData['espesor_fondo'] !== '') ? (float)$cavData['espesor_fondo'] : 0.00;
                    $altura = (isset($cavData['altura']) && $cavData['altura'] !== '') ? (float)$cavData['altura'] : 0.00;
                } else {
                    $pesoMedido = (isset($cavData['peso_medido']) && $cavData['peso_medido'] !== '') ? (float)$cavData['peso_medido'] : null;
                    $espesorPared = (isset($cavData['espesor_pared']) && $cavData['espesor_pared'] !== '') ? (float)$cavData['espesor_pared'] : null;
                    $espesorFondo = (isset($cavData['espesor_fondo']) && $cavData['espesor_fondo'] !== '') ? (float)$cavData['espesor_fondo'] : null;
                    $altura = (isset($cavData['altura']) && $cavData['altura'] !== '') ? (float)$cavData['altura'] : null;

                    // Coleccionar valores numéricos para extremos (excluyendo cavidades anuladas)
                    if ($pesoMedido !== null) {
                        $pesos[] = $pesoMedido;
                    }
                    if ($espesorPared !== null) {
                        $paredes[] = $espesorPared;
                    }
                    if ($espesorFondo !== null) {
                        $fondos[] = $espesorFondo;
                    }
                    if ($altura !== null) {
                        $alturas[] = $altura;
                    }
                }

                InspeccionCavidad::create([
                    'codigo_inspeccion' => $codigoInspeccion,
                    'producto_id' => $producto->id,
                    'maquina_id' => $validated['maquina_id'] ?? null,
                    'molde_id' => $validated['molde_id'] ?? null,
                    'operario_id' => $validated['operario_id'] ?? null,
                    'turno_id' => $validated['turno_id'] ?? null,
                    'resina_id' => $validated['resina_id'] ?? null,
                    'user_id' => $userId,
                    'cavidad_numero' => $cavData['cavidad_numero'],
                    'peso_medido' => $pesoMedido,
                    'espesor_pared' => $espesorPared,
                    'espesor_fondo' => $espesorFondo,
                    'altura' => $altura,
                    'estado' => $estadoClean,
                    'motivo_scrap' => $motivo,
                    'observaciones' => $cavData['observaciones'] ?? null,
                ]);
            }

            // 2. Extracción de Extremos (Mínimos y Máximos)
            $pesoMin = count($pesos) > 0 ? min($pesos) : null;
            $pesoMax = count($pesos) > 0 ? max($pesos) : null;

            $espParedMin = count($paredes) > 0 ? min($paredes) : null;
            $espParedMax = count($paredes) > 0 ? max($paredes) : null;

            $espFondoMin = count($fondos) > 0 ? min($fondos) : null;
            $espFondoMax = count($fondos) > 0 ? max($fondos) : null;

            $alturaMin = count($alturas) > 0 ? min($alturas) : null;
            $alturaMax = count($alturas) > 0 ? max($alturas) : null;

            // 3. Generación del Código del Lote estructurado (ej: PET2636M04)
            $now = Carbon::now('America/Lima');
            $anio = $now->format('y'); // 2 dígitos del año (ej. '26')
            $semana = sprintf('%02d', $now->isoWeek); // 2 dígitos de la semana del año (ej. '36')

            // Prefijo del producto (Fijo 'PET' para preformas o inyección de preformas)
            $tipoProdUpper = strtoupper(trim($producto->tipo_producto ?? ''));
            if (empty($tipoProdUpper) || $tipoProdUpper === 'PREFORMA' || str_contains($tipoProdUpper, 'PREFORMA') || str_contains($tipoProdUpper, 'PET')) {
                $prefijo = 'PET';
            } elseif (strlen($tipoProdUpper) > 3) {
                $prefijo = substr($tipoProdUpper, 0, 3);
            } else {
                $prefijo = $tipoProdUpper;
            }

            // Código de la Máquina seleccionada (ej. 'M04')
            $maquinaObj = !empty($validated['maquina_id']) ? Maquina::find($validated['maquina_id']) : null;
            $codigoMaquina = $maquinaObj ? strtoupper(trim($maquinaObj->codigo)) : 'M01';

            // Código de Lote Formateado (ej: PET2636M04)
            $codigoLote = "{$prefijo}{$anio}{$semana}{$codigoMaquina}";

            $resinaObj = !empty($validated['resina_id']) ? Resina::find($validated['resina_id']) : null;
            $resinaNombre = $resinaObj ? $resinaObj->codigo : null;

            if (!empty($validated['lote_id'])) {
                $lote = Lote::find($validated['lote_id']);
            } else {
                $lote = Lote::where('codigo_lote', $codigoLote)->first();
            }

            if (!$lote) {
                $lote = Lote::create([
                    'codigo_lote' => $codigoLote,
                    'producto_id' => $producto->id,
                    'maquina_id' => $maquinaObj ? $maquinaObj->id : null,
                    'resina' => $resinaNombre,
                    'fecha_produccion' => $now->toDateString(),
                    'estado_lote' => 'liberado',
                    'cantidad_empaques' => 0,
                    'cantidad_producida_unidades' => 0,
                    'total_millares' => 0.0000,
                    'peso_total_kg' => 0.00,
                    'scrap_kg' => 0.00,
                    'scrap_porcentaje' => 0.00,
                ]);
            }

            // 4. Inserción Consolidada en la Tabla `inspecciones_calidad` según la jerarquía oficial
            $strMotivos = count($motivosScrap) > 0 ? implode(', ', $motivosScrap) : null;
            $strObs = count($observaciones) > 0 ? implode('; ', $observaciones) : null;

            if ($defectosCount > 0) {
                $estadoEvaluacionGlobal = 'OBSERVADO';
            } elseif ($pasablesCount > 0) {
                $estadoEvaluacionGlobal = 'PASABLE';
            } else {
                $estadoEvaluacionGlobal = 'CONFORME';
            }

            InspeccionCalidad::create([
                'codigo_inspeccion' => $codigoInspeccion,
                'producto_id' => $producto->id,
                'lote_id' => $lote->id,
                'maquina_id' => $validated['maquina_id'] ?? null,
                'molde_id' => $validated['molde_id'] ?? null,
                'resina_id' => $validated['resina_id'] ?? null,
                'user_id' => $userId,
                'turno_id' => $validated['turno_id'] ?? null,
                'operario_id' => $validated['operario_id'] ?? null,

                'peso_min' => $pesoMin,
                'peso_max' => $pesoMax,

                'esp_pared_medido' => $espParedMin,
                'esp_pared_min' => $espParedMin,
                'esp_pared_max' => $espParedMax,

                'esp_fondo_medido' => $espFondoMin,
                'esp_fondo_min' => $espFondoMin,
                'esp_fondo_max' => $espFondoMax,

                'altura_medida' => $alturaMin,
                'altura_min' => $alturaMin,
                'altura_max' => $alturaMax,

                'estado_evaluacion' => $estadoEvaluacionGlobal,
                'motivo_scrap' => $strMotivos,
                'causa' => $strMotivos,
                'comentarios' => $strObs,
            ]);

            // Registrar movimiento de auditoría en activity_logs
            ActivityLog::create([
                'user_id' => $userId,
                'accion' => 'CREAR_INSPECCION_CAVIDADES',
                'descripcion' => "Se registró la auditoría código {$codigoInspeccion} con lote {$codigoLote} para el producto {$producto->codigo}",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            $totalCount = count($validated['cavidades']);
            $estadoResumen = ($defectosCount === 0) ? 'CONFORME' : "{$defectosCount} cavidades con observaciones/defectos";
            $msg = "Auditoría de cavidades y resumen de calidad registrados exitosamente (Lote: {$codigoLote}, Inspección: {$codigoInspeccion}). Total evaluado: {$totalCount} cavidades ({$estadoResumen}).";

            return redirect()->route('inspecciones-cavidades.show', $codigoInspeccion)
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar la inspección: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el detalle metrológico e informe imprimible de una inspección por cavidades.
     */
    public function show(string $codigo): View
    {
        $cavidades = InspeccionCavidad::with(['producto.parametroPreforma', 'maquina', 'operario', 'resina', 'molde', 'turno', 'user'])
            ->where('codigo_inspeccion', $codigo)
            ->orderBy('cavidad_numero', 'asc')
            ->get();

        if ($cavidades->isEmpty()) {
            abort(404, 'Auditoría de cavidades no encontrada.');
        }

        $header = $cavidades->first();
        $producto = $header->producto;
        $param = $producto->parametroPreforma ?? null;

        $calidadResumen = InspeccionCalidad::with(['resina', 'molde', 'maquina', 'operario', 'turno'])
            ->where('codigo_inspeccion', $codigo)
            ->first();

        $resinaObj = $header->resina ?? ($calidadResumen->resina ?? null);

        $totalCavidades = $cavidades->count();
        $fueraDeRangoCount = $cavidades->where('estado', 'FUERA_DE_RANGO')->count();
        $observadoCount = $cavidades->where('estado', 'OBSERVADO')->count();
        $pasableCount = $cavidades->where('estado', 'PASABLE')->count();
        $anuladoCount = $cavidades->where('estado', 'ANULADO')->count();
        $conformesCount = $totalCavidades - ($fueraDeRangoCount + $observadoCount + $pasableCount + $anuladoCount);
        $promedioPeso = number_format($cavidades->where('estado', '!=', 'ANULADO')->avg('peso_medido') ?? 0, 2);

        return view('inspecciones_cavidades.show', compact(
            'codigo',
            'cavidades',
            'header',
            'producto',
            'param',
            'calidadResumen',
            'resinaObj',
            'totalCavidades',
            'fueraDeRangoCount',
            'observadoCount',
            'pasableCount',
            'conformesCount',
            'promedioPeso'
        ));
    }

    /**
     * Exporta el informe metrológico de auditoría de cavidades en formato PDF.
     */
    public function exportPdf(string $codigo)
    {
        $cavidades = InspeccionCavidad::with(['producto.parametroPreforma', 'maquina', 'operario', 'resina', 'molde', 'turno', 'user'])
            ->where('codigo_inspeccion', $codigo)
            ->orderBy('cavidad_numero', 'asc')
            ->get();

        if ($cavidades->isEmpty()) {
            abort(404, 'Auditoría de cavidades no encontrada.');
        }

        $header = $cavidades->first();
        $producto = $header->producto;
        $param = $producto->parametroPreforma ?? null;

        $calidadResumen = InspeccionCalidad::with(['resina', 'molde', 'maquina', 'operario', 'turno'])
            ->where('codigo_inspeccion', $codigo)
            ->first();

        $resinaObj = $header->resina ?? ($calidadResumen->resina ?? null);

        $totalCavidades = $cavidades->count();
        $fueraDeRangoCount = $cavidades->where('estado', 'FUERA_DE_RANGO')->count();
        $observadoCount = $cavidades->where('estado', 'OBSERVADO')->count();
        $pasableCount = $cavidades->where('estado', 'PASABLE')->count();
        $anuladoCount = $cavidades->where('estado', 'ANULADO')->count();
        $conformesCount = $totalCavidades - ($fueraDeRangoCount + $observadoCount + $pasableCount + $anuladoCount);
        $promedioPeso = number_format($cavidades->where('estado', '!=', 'ANULADO')->avg('peso_medido') ?? 0, 2);
        $porcentajeConforme = number_format(($conformesCount / max($totalCavidades, 1)) * 100, 1);

        $pdf = Pdf::loadView('inspecciones_cavidades.pdf', compact(
            'codigo',
            'cavidades',
            'header',
            'producto',
            'param',
            'calidadResumen',
            'resinaObj',
            'totalCavidades',
            'fueraDeRangoCount',
            'observadoCount',
            'pasableCount',
            'conformesCount',
            'promedioPeso',
            'porcentajeConforme'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream("Reporte_Metrologico_{$codigo}.pdf");
    }

    /**
     * Consolida y asigna el estado exclusivo de OBSERVADO a la auditoría en inspecciones_calidad.
     */
    public function consolidarObservado(string $codigo, Request $request): RedirectResponse
    {
        $inspeccionCalidad = InspeccionCalidad::where('codigo_inspeccion', $codigo)->first();

        if (!$inspeccionCalidad) {
            return back()->with('error', 'No se encontró el registro consolidado para esta auditoría.');
        }

        $inspeccionCalidad->update([
            'estado_evaluacion' => 'OBSERVADO',
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'accion' => 'CONSOLIDAR_INSPECCION_OBSERVADO',
            'descripcion' => "Se consolidó manualmente la inspección {$codigo} con estado exclusivo OBSERVADO.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('inspecciones-calidad.index')
            ->with('success', "La auditoría {$codigo} ha sido consolidada exitosamente con el estado OBSERVADO.");
    }
}