<?php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Maquina;
use App\Models\MaquinaHistorial;
use Carbon\Carbon;

$now = Carbon::now('America/Lima');
$inicioSemana = $now->copy()->startOfWeek();
$finSemana = $now->copy()->endOfWeek();

$allMachines = Maquina::all();
echo "Total maquinas: " . $allMachines->count() . PHP_EOL;

$diaActualIter = $inicioSemana->copy();

while ($diaActualIter->lessThanOrEqualTo($finSemana)) {
    $fechaKey = $diaActualIter->toDateString();
    $dateEndOfDay = $diaActualIter->copy()->endOfDay();

    $activeCount = 0;
    foreach ($allMachines as $maq) {
        $latestPastHistorial = MaquinaHistorial::where('maquina_id', $maq->id)
            ->where('fecha_hora_inicio', '<=', $dateEndOfDay)
            ->latest('fecha_hora_inicio')
            ->latest('id')
            ->first();

        if ($latestPastHistorial) {
            $estadoEnFecha = $latestPastHistorial->nuevo_estado;
        } else {
            $firstFutureHistorial = MaquinaHistorial::where('maquina_id', $maq->id)
                ->where('fecha_hora_inicio', '>', $dateEndOfDay)
                ->oldest('fecha_hora_inicio')
                ->oldest('id')
                ->first();

            if ($firstFutureHistorial) {
                $estadoEnFecha = $firstFutureHistorial->estado_anterior;
            } else {
                $estadoEnFecha = $maq->estado;
            }
        }

        if ($estadoEnFecha === 'activo') {
            $activeCount++;
        }
    }

    $tope = max(2, $activeCount * 2);
    echo "Fecha: {$fechaKey} | Activas: {$activeCount} | Tope: {$tope}" . PHP_EOL;

    $diaActualIter->addDay();
}
