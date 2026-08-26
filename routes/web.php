<?php

use App\Http\Controllers\InspeccionCavidadController;
use App\Http\Controllers\MaquinaController;
use App\Http\Controllers\OperarioController;
use App\Http\Controllers\ParametroPreformaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    $serverTime = Carbon::now('America/Lima');
    return view('dashboard',[
        'serverTime' => $serverTime->toIso8601String(),
        'formattedDate' => $serverTime->translatedFormat('l, d F Y'),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas para Gestión de Productos y Parámetros
    Route::get('productos/plantilla', [ProductoController::class, 'downloadPlantilla'])->name('productos.plantilla');
    Route::post('productos/importar', [ProductoController::class, 'import'])->name('productos.import');
    Route::get('productos/{producto}/parametros-json', [ProductoController::class, 'getParametrosJson'])->name('productos.parametros-json');
    Route::post('productos/{producto}/parametros', [ParametroPreformaController::class, 'storeOrUpdate'])->name('productos.parametros.store');
    Route::resource('productos', ProductoController::class)->except(['create', 'edit']);

    // Rutas para Gestión de Máquinas e Inyectoras
    Route::resource('maquinas', MaquinaController::class)->except(['create', 'edit']);

    // Rutas para Gestión de Operarios y Encargados
    Route::resource('operarios', OperarioController::class)->except(['create', 'edit']);

    // Rutas para Inspección Cavidad por Cavidad
    Route::get('inspecciones-cavidades', [InspeccionCavidadController::class, 'index'])->name('inspecciones-cavidades.index');
    Route::get('inspecciones-cavidades/crear', [InspeccionCavidadController::class, 'create'])->name('inspecciones-cavidades.create');
    Route::post('inspecciones-cavidades', [InspeccionCavidadController::class, 'store'])->name('inspecciones-cavidades.store');
    Route::get('inspecciones-cavidades/{codigo}', [InspeccionCavidadController::class, 'show'])->name('inspecciones-cavidades.show');
    Route::get('inspecciones-cavidades/{codigo}/pdf', [InspeccionCavidadController::class, 'exportPdf'])->name('inspecciones-cavidades.pdf');
});

require __DIR__.'/auth.php';
