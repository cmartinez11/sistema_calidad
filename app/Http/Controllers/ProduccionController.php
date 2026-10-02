<?php

namespace App\Http\Controllers;

use App\Models\Produccion;
use App\Models\Producto;
use App\Models\Operario;
use App\Models\Maquina;
use App\Models\Resina;
use Illuminate\Http\Request;
use Carbon\Carbon;


class ProduccionController extends Controller
{
    public function index()
    {
        $producciones = Produccion::with(['producto', 'operario', 'maquina', 'resina'])->latest()->paginate(10);
        return view('produccion.index', compact('producciones'));
    }

    public function create()
    {
        $productos = Producto::with(['matrizEmpaques', 'molde'])->get();
        $operarios = Operario::all();
        $maquinas = Maquina::all();
        $resinas = Resina::all();

        $lastDia = Produccion::where('turno', 'DIA')->latest('id')->first();
        $nextDiaNum = $lastDia ? intval(substr($lastDia->nro_produccion, 4)) + 1 : 1;
        $nroDia = 'PRD-' . str_pad($nextDiaNum, 4, '0', STR_PAD_LEFT);

        $lastNoche = Produccion::where('turno', 'NOCHE')->latest('id')->first();
        $nextNocheNum = $lastNoche ? intval(substr($lastNoche->nro_produccion, 4)) + 1 : 1;
        $nroNoche = 'PRN-' . str_pad($nextNocheNum, 4, '0', STR_PAD_LEFT);

        // Definir fechas: Fecha de registro = hoy, Fecha de producción por defecto = ayer
        $fechaRegistro = Carbon::now()->toDateString();
        $fechaProduccionSugerida = Carbon::yesterday()->toDateString();

        return view('produccion.create', compact('productos', 'operarios', 'maquinas', 'resinas', 'nroDia', 'nroNoche', 'fechaRegistro', 'fechaProduccionSugerida'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_produccion' => 'required|date',
            'fecha_registro' => 'required|date',
            'turnos_activos' => 'required|array|min:1',
        ]);

        $fechaProduccion = $request->fecha_produccion;
        $fechaRegistro = $request->fecha_registro;
        $turnosActivos = $request->turnos_activos;

        foreach ($turnosActivos as $turno) {
            $data = $request->input(strtolower($turno));

            if (!$data || empty($data['producto_id'])) {
                continue;
            }

            $prefix = ($turno === 'DIA') ? 'PRD-' : 'PRN-';
            $last = Produccion::where('turno', $turno)->latest('id')->first();
            $nextNum = $last ? intval(substr($last->nro_produccion, 4)) + 1 : 1;
            $nroProduccion = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $maquinaParada = isset($data['maquina_parada']) ? true : false;
            $minutosParada = 0;
            $horasOperativas = 12.00;

            if ($maquinaParada && !empty($data['hora_inicio_parada']) && !empty($data['hora_fin_parada'])) {
                $inicio = Carbon::parse($data['hora_inicio_parada']);
                $fin = Carbon::parse($data['hora_fin_parada']);
                $minutosParada = $inicio->diffInMinutes($fin);
                $horasOperativas = max(0, 12 - ($minutosParada / 60));
            }

            $totalKg = floatval($data['total_kg'] ?? 0);
            $scrap = floatval($data['scrap'] ?? 0);
            $porcentajeScrap = 0;
            if (($totalKg + $scrap) > 0) {
                $porcentajeScrap = ($scrap / ($totalKg + $scrap)) * 100;
            }

            Produccion::create([
                'nro_produccion' => $nroProduccion,
                'fecha_produccion' => $fechaProduccion,
                'fecha_registro' => $fechaRegistro,
                'turno' => $turno,
                'producto_id' => $data['producto_id'],
                'operario_id' => $data['operario_id'],
                'maquina_id' => $data['maquina_id'],
                'resina_id' => $data['resina_id'],
                'presentacion' => $data['presentacion'] ?? 'CAJA',
                'cantidad_producida' => $data['cantidad_producida'] ?? 0,
                'total_kg' => $totalKg,
                'total_millares' => $data['total_millares'] ?? 0,
                'scrap' => $scrap,
                'porcentaje_scrap' => round($porcentajeScrap, 2),
                'ciclo' => $data['ciclo'] ?? null,
                'cavidades' => $data['cavidades'] ?? 0,
                'cavidades_trabajadas' => $data['cavidades_trabajadas'] ?? 0,
                'motivo_scrap' => $data['motivo_scrap'] ?? null,
                'maquina_parada' => $maquinaParada,
                'hora_inicio_parada' => $data['hora_inicio_parada'] ?? null,
                'hora_fin_parada' => $data['hora_fin_parada'] ?? null,
                'tiempo_maquina_parada_min' => $minutosParada,
                'horas_operativas' => round($horasOperativas, 2),
            ]);
        }

        return redirect()->route('produccion.index')->with('success', 'Registros de producción guardados con éxito.');
    }
}
