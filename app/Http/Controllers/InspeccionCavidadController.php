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
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        $producto = $request->get('producto', $request->get('producto_id'));
        $lote = $request->get('lote');
        $estado = $request->get('estado');
        $search = $request->get('search');

        $query = InspeccionCavidad::query()
            ->with(['producto', 'maquina', 'operario', 'turno', 'user'])
            ->whereNotNull('codigo_inspeccion')
            ->where('codigo_inspeccion', '!=', '');

        if ($fechaInicio) {
            $query->whereDate('fecha_real', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->whereDate('fecha_real', '<=', $fechaFin);
        }

        if ($producto) {
            if (is_numeric($producto)) {
                $query->where('producto_id', $producto);
            } else {
                $query->whereHas('producto', function ($q) use ($producto) {
                    $q->where('codigo', 'ILIKE', "%{$producto}%")
                      ->orWhere('nombre', 'ILIKE', "%{$producto}%");
                });
            }
        }

        if ($lote) {
            $query->whereExists(function ($sub) use ($lote) {
                $sub->select(DB::raw(1))
                    ->from('inspecciones_calidad')
                    ->join('lotes', 'lotes.id', '=', 'inspecciones_calidad.lote_id')
                    ->whereColumn('inspecciones_calidad.codigo_inspeccion', 'inspecciones_cavidades.codigo_inspeccion')
                    ->where('lotes.codigo_lote', 'ILIKE', "%{$lote}%");
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo_inspeccion', 'ILIKE', "%{$search}%")
                    ->orWhereHas('producto', fn($p) => $p->where('codigo', 'ILIKE', "%{$search}%")->orWhere('nombre', 'ILIKE', "%{$search}%"))
                    ->orWhereHas('maquina', fn($m) => $m->where('codigo', 'ILIKE', "%{$search}%")->orWhere('nombre', 'ILIKE', "%{$search}%"))
                    ->orWhereHas('operario', fn($o) => $o->where('nombre', 'ILIKE', "%{$search}%"));
            });
        }

        if ($estado) {
            if ($estado === 'PNC') {
                $query->where(function ($q) {
                    $q->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))->from('pnc')->whereColumn('pnc.codigo_inspeccion', 'inspecciones_cavidades.codigo_inspeccion');
                    })
                    ->orWhereExists(function ($sub) {
                        $sub->select(DB::raw(1))->from('inspecciones_calidad')->whereColumn('inspecciones_calidad.codigo_inspeccion', 'inspecciones_cavidades.codigo_inspeccion')->where('estado_evaluacion', 'PNC');
                    });
                });
            } elseif ($estado === 'OBSERVADO') {
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))->from('inspecciones_calidad')->whereColumn('inspecciones_calidad.codigo_inspeccion', 'inspecciones_cavidades.codigo_inspeccion')->where('estado_evaluacion', 'OBSERVADO');
                });
            } elseif ($estado === 'CONFORME') {
                $query->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))->from('pnc')->whereColumn('pnc.codigo_inspeccion', 'inspecciones_cavidades.codigo_inspeccion');
                })
                ->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))->from('inspecciones_calidad')->whereColumn('inspecciones_calidad.codigo_inspeccion', 'inspecciones_cavidades.codigo_inspeccion')->where('estado_evaluacion', 'CONFORME');
                });
            } elseif ($estado === 'BORRADOR') {
                $query->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))->from('pnc')->whereColumn('pnc.codigo_inspeccion', 'inspecciones_cavidades.codigo_inspeccion');
                })
                ->whereIn('estado', ['FUERA_DE_RANGO', 'OBSERVADO', 'ANULADO']);
            }
        }

        $inspecciones = $query->select(
            'codigo_inspeccion',
            'producto_id',
            'maquina_id',
            'operario_id',
            'turno_id',
            'user_id',
            DB::raw('MIN(fecha_real) as fecha_real'),
            DB::raw('MIN(created_at) as created_at'),
            DB::raw('COUNT(*) as total_cavidades'),
            DB::raw("COUNT(CASE WHEN estado IN ('FUERA_DE_RANGO', 'OBSERVADO', 'ANULADO') OR (observaciones IS NOT NULL AND observaciones != '') THEN 1 END) as defectos_count"),
            DB::raw("(SELECT estado_evaluacion FROM inspecciones_calidad WHERE inspecciones_calidad.codigo_inspeccion = inspecciones_cavidades.codigo_inspeccion LIMIT 1) as estado_evaluacion"),
            DB::raw("EXISTS(SELECT 1 FROM pnc WHERE pnc.codigo_inspeccion = inspecciones_cavidades.codigo_inspeccion) as tiene_pnc")
        )
        ->groupBy('codigo_inspeccion', 'producto_id', 'maquina_id', 'operario_id', 'turno_id', 'user_id')
        ->orderBy(DB::raw('MIN(fecha_real)'), 'desc')
        ->orderBy(DB::raw('MIN(created_at)'), 'desc')
        ->paginate(10)
        ->withQueryString();

        $productos = Producto::orderBy('nombre', 'asc')->get();

        return view('inspecciones_cavidades.index', compact(
            'inspecciones',
            'productos',
            'fechaInicio',
            'fechaFin',
            'producto',
            'lote',
            'estado',
            'search'
        ));
    }

    /**
     * Muestra la vista interactiva para el registro de pesos cavidad por cavidad.
     */
    public function create(Request $request): View
    {
        $productos = Producto::with(['parametroPreforma.molde', 'molde'])
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
     * Descarga la plantilla oficial en formato nativo de Excel (.xlsx).
     */
    public function downloadPlantillaExcel(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Cavidades');

        $headers = [
            'CAVIDAD',
            'PESO (G)',
            'ESP. PARED MIN',
            'ESP. PARED MAX',
            'ESP. FONDO',
            'ALTURA',
            'TIENE DEFECTO',
            'OBSERVACIONES'
        ];

        $sheet->fromArray([$headers], null, 'A1');

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '30732B'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        $sampleData = [];
        for ($i = 1; $i <= 16; $i++) {
            $sampleData[] = [
                'C-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                28.00,
                2.450,
                2.550,
                3.100,
                150.00,
                0,
                ''
            ];
        }

        $sheet->fromArray($sampleData, null, 'A2');

        foreach (range('A', 'H') as $col) {
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

            $colCavidad = $findCol(['cavidad', 'cav', 'c', 'num_cavidad']);
            $colPeso = $findCol(['peso_g', 'peso', 'peso_medido', 'pesog']);
            $colParedMin = $findCol(['esp_pared_min', 'espesor_pared_min', 'pared_min']);
            $colParedMax = $findCol(['esp_pared_max', 'espesor_pared_max', 'pared_max']);
            $colFondo = $findCol(['esp_fondo', 'espesor_fondo', 'fondo']);
            $colAltura = $findCol(['altura', 'alt']);
            $colDefecto = $findCol(['tiene_defecto', 'defecto', 'falla']);
            $colObs = $findCol(['observaciones', 'observacion', 'comentarios']);

            if ($colCavidad === false) $colCavidad = 0;
            if ($colPeso === false) $colPeso = 1;
            if ($colParedMin === false) $colParedMin = 2;
            if ($colParedMax === false) $colParedMax = 3;
            if ($colFondo === false) $colFondo = 4;
            if ($colAltura === false) $colAltura = 5;
            if ($colDefecto === false) $colDefecto = 6;
            if ($colObs === false) $colObs = 7;

            $cavidadesImportadas = [];
            $rowCounter = 1;

            foreach ($rows as $row) {
                $cavidadRaw = isset($row[$colCavidad]) ? $row[$colCavidad] : (string)$rowCounter;
                $cavNum = (int) preg_replace('/[^0-9]/', '', $cavidadRaw);
                if ($cavNum <= 0) {
                    $cavNum = $rowCounter;
                }

                $pesoVal = (isset($row[$colPeso]) && $row[$colPeso] !== '') ? (float)str_replace(',', '.', $row[$colPeso]) : '';
                $paredMinVal = (isset($row[$colParedMin]) && $row[$colParedMin] !== '') ? (float)str_replace(',', '.', $row[$colParedMin]) : '';
                $paredMaxVal = (isset($row[$colParedMax]) && $row[$colParedMax] !== '') ? (float)str_replace(',', '.', $row[$colParedMax]) : '';
                $fondoVal = (isset($row[$colFondo]) && $row[$colFondo] !== '') ? (float)str_replace(',', '.', $row[$colFondo]) : '';
                $alturaVal = (isset($row[$colAltura]) && $row[$colAltura] !== '') ? (float)str_replace(',', '.', $row[$colAltura]) : '';

                $defectoRaw = isset($row[$colDefecto]) ? trim($row[$colDefecto]) : '0';
                $tieneDefecto = in_array(strtolower($defectoRaw), ['1', 'si', 'sí', 'true', 's']);

                $obsVal = isset($row[$colObs]) ? trim($row[$colObs]) : '';

                $cavidadesImportadas[] = [
                    'cavidad_numero' => $cavNum,
                    'peso_medido' => $pesoVal !== '' ? $pesoVal : '',
                    'espesor_pared_min' => $paredMinVal !== '' ? $paredMinVal : '',
                    'espesor_pared_max' => $paredMaxVal !== '' ? $paredMaxVal : '',
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
            'maquina_id' => 'required|exists:maquinas,id',
            'molde_id' => 'required|exists:molde,id',
            'operario_id' => 'required|exists:operarios,id',
            'turno_id' => 'required|exists:turnos,id',
            'resina_id'=> 'required|exists:resinas,id',
            'cavidades' => 'required|array|min:1',
            'cavidades.*.cavidad_numero' => 'required|integer|min:1',
            'cavidades.*.peso_medido' => 'required_unless:cavidades.*.estado,ANULADO|nullable|numeric|min:0',
            'cavidades.*.espesor_pared_min' => 'nullable|numeric|min:0',
            'cavidades.*.espesor_pared_max' => 'nullable|numeric|min:0',
            'cavidades.*.espesor_fondo' => 'nullable|numeric|min:0',
            'cavidades.*.altura' => 'nullable|numeric|min:0',
            'cavidades.*.estado' => 'required|string|in:CONFORME,FUERA_DE_RANGO,OBSERVADO,ANULADO',
            'cavidades.*.motivo_scrap' => 'nullable|string|max:100',
            'cavidades.*.observaciones' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $producto = Producto::findOrFail($validated['producto_id']);
            $userId = Auth::id();
            $msg = '';

            $now = Carbon::now('America/Lima');
            $turnoObj = Turno::find($validated['turno_id']);
            $esTurnoNoche = $turnoObj && (strtoupper($turnoObj->nombre) === 'NOCHE' || $turnoObj->id == 2);

            $fechaReal = $esTurnoNoche ? $now->copy()->subDay()->toDateString() : $now->toDateString();
            $fechaRealCarbon = Carbon::parse($fechaReal);

            $prefix = 'CAV-' . $fechaRealCarbon->format('Ymd') . '-';
            $latestCode = InspeccionCavidad::where('codigo_inspeccion', 'LIKE', "{$prefix}%")
                ->max('codigo_inspeccion');

            $seq = $latestCode ? ((int) substr($latestCode, -4) + 1) : 1;
            $codigoInspeccion = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $pesos = []; 
            $paredesMin = [];
            $paredesMax = [];
            $fondos = []; 
            $alturas = [];
            $motivosScrap = []; 
            $observaciones = [];
            
            $tieneDefectosReales = false;

            foreach ($validated['cavidades'] as $cavData) {
                $estadoClean = strtoupper(str_replace(' ', '_', $cavData['estado']));
                $isAnulado = ($estadoClean === 'ANULADO' || !empty($cavData['anulado']) || !empty($cavData['anulada']));

                if ($isAnulado) {
                    $estadoClean = 'ANULADO';
                }
                
                if (!$isAnulado && in_array($estadoClean, ['FUERA_DE_RANGO', 'OBSERVADO'])) {
                    $tieneDefectosReales = true; 
                }

                $motivo = in_array($estadoClean, ['FUERA_DE_RANGO', 'OBSERVADO']) ? ($cavData['motivo_scrap'] ?? 'Sin especificar') : null;

                if ($motivo && !in_array($motivo, $motivosScrap)) {
                    $motivosScrap[] = $motivo;
                }

                if (!empty($cavData['observaciones']) && !in_array($cavData['observaciones'], $observaciones)) {
                    $observaciones[] = $cavData['observaciones'];
                }

                if ($isAnulado) {
                    $pesoMedido = (isset($cavData['peso_medido']) && $cavData['peso_medido'] !== '') ? (float)$cavData['peso_medido'] : 0.00;
                    $espesorParedMin = 0.00;
                    $espesorParedMax = 0.00;
                    $espesorFondo = (isset($cavData['espesor_fondo']) && $cavData['espesor_fondo'] !== '') ? (float)$cavData['espesor_fondo'] : 0.00;
                    $altura = (isset($cavData['altura']) && $cavData['altura'] !== '') ? (float)$cavData['altura'] : 0.00;
                } else {
                    $pesoMedido = (isset($cavData['peso_medido']) && $cavData['peso_medido'] !== '') ? (float)$cavData['peso_medido'] : null;
                    $espesorParedMin = (isset($cavData['espesor_pared_min']) && $cavData['espesor_pared_min'] !== '') ? (float)$cavData['espesor_pared_min'] : null;
                    $espesorParedMax = (isset($cavData['espesor_pared_max']) && $cavData['espesor_pared_max'] !== '') ? (float)$cavData['espesor_pared_max'] : null;
                    $espesorFondo = (isset($cavData['espesor_fondo']) && $cavData['espesor_fondo'] !== '') ? (float)$cavData['espesor_fondo'] : null;
                    $altura = (isset($cavData['altura']) && $cavData['altura'] !== '') ? (float)$cavData['altura'] : null;

                    if ($pesoMedido !== null) $pesos[] = $pesoMedido;
                    if ($espesorParedMin !== null) $paredesMin[] = $espesorParedMin;
                    if ($espesorParedMax !== null) $paredesMax[] = $espesorParedMax;
                    if ($espesorFondo !== null) $fondos[] = $espesorFondo;
                    if ($altura !== null) $alturas[] = $altura;
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
                    'espesor_pared_min' => $espesorParedMin,
                    'espesor_pared_max' => $espesorParedMax,
                    'espesor_fondo' => $espesorFondo,
                    'altura' => $altura,
                    'estado' => $estadoClean,
                    'motivo_scrap' => $motivo,
                    'observaciones' => $cavData['observaciones'] ?? null,
                    'fecha_real' => $fechaReal,
                ]);
            }

            $pesoMin = count($pesos) > 0 ? min($pesos) : null;
            $pesoMax = count($pesos) > 0 ? max($pesos) : null;

            $espParedMin = count($paredesMin) > 0 ? min($paredesMin) : null;
            $espParedMax = count($paredesMax) > 0 ? max($paredesMax) : null;

            $espFondoMin = count($fondos) > 0 ? min($fondos) : null;
            $espFondoMax = count($fondos) > 0 ? max($fondos) : null;

            $alturaMin = count($alturas) > 0 ? min($alturas) : null;
            $alturaMax = count($alturas) > 0 ? max($alturas) : null;

            $anio = $fechaRealCarbon->format('y'); 
            $semana = sprintf('%02d', $fechaRealCarbon->isoWeek);

            $tipoProdUpper = strtoupper(trim($producto->tipo_producto ?? ''));
            if (empty($tipoProdUpper) || $tipoProdUpper === 'PREFORMA' || str_contains($tipoProdUpper, 'PREFORMA') || str_contains($tipoProdUpper, 'PET')) {
                $prefijo = 'PET';
            } elseif (strlen($tipoProdUpper) > 3) {
                $prefijo = substr($tipoProdUpper, 0, 3);
            } else {
                $prefijo = $tipoProdUpper;
            }

            $maquinaObj = !empty($validated['maquina_id']) ? Maquina::find($validated['maquina_id']) : null;
            $codigoMaquina = $maquinaObj ? strtoupper(trim($maquinaObj->codigo)) : 'M01';
            $codigoLote = "{$prefijo}{$anio}{$semana}{$codigoMaquina}";

            $resinaObj = !empty($validated['resina_id']) ? Resina::find($validated['resina_id']) : null;
            $resinaNombre = $resinaObj ? $resinaObj->codigo : null;

            $lote = Lote::where('codigo_lote', $codigoLote)->first();

            if (!$lote) {
                $lote = Lote::create([
                    'codigo_lote' => $codigoLote,
                    'producto_id' => $producto->id,
                    'maquina_id' => $maquinaObj ? $maquinaObj->id : null,
                    'resina' => $resinaNombre,
                    'fecha_produccion' => $fechaReal,
                    'estado_lote' => 'liberado',
                    'cantidad_empaques' => 0,
                    'cantidad_producida_unidades' => 0,
                    'total_millares' => 0.0000,
                    'peso_total_kg' => 0.00,
                    'scrap_kg' => 0.00,
                    'scrap_porcentaje' => 0.00,
                ]);
            }

            $strMotivos = count($motivosScrap) > 0 ? implode(', ', $motivosScrap) : null;
            $strObs = count($observaciones) > 0 ? implode('; ', $observaciones) : null;

            $estadoEvaluacionGlobal = $tieneDefectosReales ? 'OBSERVADO' : 'CONFORME';

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
                'esp_pared_min' => $espParedMin,
                'esp_pared_max' => $espParedMax,
                'esp_fondo_min' => $espFondoMin,
                'esp_fondo_max' => $espFondoMax,
                'altura_min' => $alturaMin,
                'altura_max' => $alturaMax,

                'estado_evaluacion' => $estadoEvaluacionGlobal,
                'motivo_scrap' => $strMotivos,
                'causa' => $strMotivos,
                'comentarios' => $strObs,
                'fecha_real' => $fechaReal,
            ]);

            $msg = $tieneDefectosReales 
                ? "Auditoría de cavidades registrada y consolidada automáticamente con estado: OBSERVADO (Fecha Operativa: {$fechaReal})."
                : "Auditoría de cavidades registrada exitosamente (Fecha Operativa: {$fechaReal}, Lote: {$codigoLote}). Estado: CONFORME.";

            ActivityLog::create([
                'user_id' => $userId,
                'accion' => 'CREAR_INSPECCION_CAVIDADES',
                'descripcion' => "Se registró y consolidó automáticamente la auditoría código {$codigoInspeccion} con estado {$estadoEvaluacionGlobal}",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

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

        $calidadResumen = InspeccionCalidad::with(['resina', 'molde', 'maquina', 'operario', 'turno', 'user', 'motivoObservacion'])
            ->where('codigo_inspeccion', $codigo)
            ->first();

        $pnc = \App\Models\Pnc::with(['user', 'lote'])->where('codigo_inspeccion', $codigo)->first();

        $resinaObj = $header->resina ?? ($calidadResumen->resina ?? null);

        $totalCavidades = $cavidades->count();
        $fueraDeRangoCount = $cavidades->where('estado', 'FUERA_DE_RANGO')->count();
        $observadoCount = $cavidades->where('estado', 'OBSERVADO')->count();
        $anuladoCount = $cavidades->where('estado', 'ANULADO')->count();
        $conformesCount = $totalCavidades - ($fueraDeRangoCount + $observadoCount + $anuladoCount);
        $promedioPeso = number_format($cavidades->where('estado', '!=', 'ANULADO')->avg('peso_medido') ?? 0, 2);

        $tieneDefectos = ($fueraDeRangoCount + $observadoCount + $anuladoCount > 0) ||
            $cavidades->filter(fn($c) => !empty($c->observaciones) || !empty($c->motivo_scrap))->count() > 0;

        $motivosObservacion = \App\Models\MotivoObservacion::where('activo', true)->orderBy('nombre', 'asc')->get();

        return view('inspecciones_cavidades.show', compact(
            'codigo',
            'cavidades',
            'header',
            'producto',
            'param',
            'calidadResumen',
            'pnc',
            'resinaObj',
            'totalCavidades',
            'fueraDeRangoCount',
            'observadoCount',
            'conformesCount',
            'promedioPeso',
            'motivosObservacion',
            'tieneDefectos'
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
        $anuladoCount = $cavidades->where('estado', 'ANULADO')->count();
        $conformesCount = $totalCavidades - ($fueraDeRangoCount + $observadoCount + $anuladoCount);
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
            'conformesCount',
            'promedioPeso',
            'porcentajeConforme'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream("Reporte_Metrologico_{$codigo}.pdf");
    }

    /**
     * Consolida y asigna el estado exclusivo de OBSERVADO a la auditoría en inspecciones_calidad con su motivo obligatorio.
     */
    public function consolidarObservado(string $codigo, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'motivo_observacion_id' => 'required|exists:motivos_observacion,id',
            'motivo_observacion_texto' => 'nullable|string|max:500',
        ], [
            'motivo_observacion_id.required' => 'Debes seleccionar obligatoriamente un motivo de pase con observación.',
            'motivo_observacion_id.exists' => 'El motivo de observación seleccionado no es válido.',
        ]);

        $inspeccionCalidad = InspeccionCalidad::where('codigo_inspeccion', $codigo)->first();

        if (!$inspeccionCalidad) {
            $cavidades = InspeccionCavidad::where('codigo_inspeccion', $codigo)->get();
            if ($cavidades->isEmpty()) {
                return back()->with('error', 'No se encontraron cavidades registradas para esta auditoría.');
            }

            $header = $cavidades->first();
            $pesos = $cavidades->where('estado', '!=', 'ANULADO')->pluck('peso_medido')->filter()->toArray();
            $paredesMin = $cavidades->where('estado', '!=', 'ANULADO')->pluck('espesor_pared_min')->filter()->toArray();
            $paredesMax = $cavidades->where('estado', '!=', 'ANULADO')->pluck('espesor_pared_max')->filter()->toArray();
            $fondos = $cavidades->where('estado', '!=', 'ANULADO')->pluck('espesor_fondo')->filter()->toArray();
            $alturas = $cavidades->where('estado', '!=', 'ANULADO')->pluck('altura')->filter()->toArray();
            $motivosScrap = $cavidades->pluck('motivo_scrap')->filter()->unique()->toArray();
            $observaciones = $cavidades->pluck('observaciones')->filter()->unique()->toArray();

            $now = Carbon::now('America/Lima');
            $codigoLote = "PET" . $now->format('y') . sprintf('%02d', $now->isoWeek) . ($header->maquina ? strtoupper(trim($header->maquina->codigo)) : 'M01');
            $lote = Lote::firstOrCreate(
                ['codigo_lote' => $codigoLote],
                [
                    'producto_id' => $header->producto_id,
                    'maquina_id' => $header->maquina_id,
                    'fecha_produccion' => $now->toDateString(),
                    'estado_lote' => 'liberado',
                ]
            );

            $inspeccionCalidad = InspeccionCalidad::create([
                'codigo_inspeccion' => $codigo,
                'producto_id' => $header->producto_id,
                'lote_id' => $lote->id,
                'maquina_id' => $header->maquina_id,
                'molde_id' => $header->molde_id,
                'resina_id' => $header->resina_id,
                'user_id' => Auth::id(),
                'turno_id' => $header->turno_id,
                'operario_id' => $header->operario_id,

                'peso_min' => count($pesos) > 0 ? min($pesos) : null,
                'peso_max' => count($pesos) > 0 ? max($pesos) : null,
                'esp_pared_min' => count($paredesMin) > 0 ? min($paredesMin) : null,
                'esp_pared_max' => count($paredesMax) > 0 ? max($paredesMax) : null,
                'esp_fondo_min' => count($fondos) > 0 ? min($fondos) : null,
                'esp_fondo_max' => count($fondos) > 0 ? max($fondos) : null,
                'altura_min' => count($alturas) > 0 ? min($alturas) : null,
                'altura_max' => count($alturas) > 0 ? max($alturas) : null,

                'estado_evaluacion' => 'OBSERVADO',
                'motivo_observacion_id' => $validated['motivo_observacion_id'],
                'motivo_observacion_texto' => $validated['motivo_observacion_texto'] ?? null,
                'motivo_scrap' => count($motivosScrap) > 0 ? implode(', ', $motivosScrap) : null,
                'causa' => count($motivosScrap) > 0 ? implode(', ', $motivosScrap) : null,
                'comentarios' => count($observaciones) > 0 ? implode('; ', $observaciones) : null,
            ]);
        } else {
            $inspeccionCalidad->update([
                'estado_evaluacion' => 'OBSERVADO',
                'motivo_observacion_id' => $validated['motivo_observacion_id'],
                'motivo_observacion_texto' => $validated['motivo_observacion_texto'] ?? null,
            ]);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'accion' => 'CONSOLIDAR_INSPECCION_OBSERVADO',
            'descripcion' => "Se consolidó manualmente la inspección {$codigo} con motivo de observación ID {$validated['motivo_observacion_id']}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('inspecciones-calidad.index')
            ->with('success', "La auditoría {$codigo} ha sido consolidada exitosamente en el Resumen de Calidad con el estado OBSERVADO.");
    }
}