<?php

use App\Http\Controllers\InspeccionCalidadController;
use App\Http\Controllers\InspeccionCavidadController;
use App\Http\Controllers\MaquinaController;
use App\Http\Controllers\OperarioController;
use App\Http\Controllers\ParametroPreformaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ResinaController;
use App\Http\Controllers\MoldeController;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

use App\Http\Controllers\PncController;

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserController;

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

    // Rutas protegidas exclusivamente para Administradores
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

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
    Route::get('inspecciones-cavidades/plantilla-excel', [InspeccionCavidadController::class, 'downloadPlantillaExcel'])->name('inspecciones-cavidades.plantilla-excel');
    Route::post('inspecciones-cavidades/procesar-excel', [InspeccionCavidadController::class, 'procesarExcel'])->name('inspecciones-cavidades.procesar-excel');
    Route::post('inspecciones-cavidades', [InspeccionCavidadController::class, 'store'])->name('inspecciones-cavidades.store');
    Route::get('inspecciones-cavidades/{codigo}', [InspeccionCavidadController::class, 'show'])->name('inspecciones-cavidades.show');
    Route::get('inspecciones-cavidades/{codigo}/pdf', [InspeccionCavidadController::class, 'exportPdf'])->name('inspecciones-cavidades.pdf');
    Route::post('inspecciones-cavidades/{codigo}/consolidar-observado', [InspeccionCavidadController::class, 'consolidarObservado'])->name('inspecciones-cavidades.consolidar-observado');

    // Rutas para Módulo de Producto No Conforme (PNC - Formato FE-SIG-FOR-30-V)
    Route::get('pnc', [PncController::class, 'index'])->name('pnc.index');
    Route::get('pnc/crear', [PncController::class, 'create'])->name('pnc.create');
    Route::post('pnc', [PncController::class, 'store'])->name('pnc.store');
    Route::get('pnc/{id}', [PncController::class, 'show'])->name('pnc.show')->whereNumber('id');
    Route::get('pnc/{id}/pdf', [PncController::class, 'exportPdf'])->name('pnc.pdf')->whereNumber('id');

    // Rutas para Resumen de Inspecciones de Calidad
    Route::get('inspecciones-calidad', [InspeccionCalidadController::class, 'index'])->name('inspecciones-calidad.index');
    Route::get('inspecciones-calidad/{id}', [InspeccionCalidadController::class, 'show'])->name('inspecciones-calidad.show')->whereNumber('id');

    Route::get('maquinas/create', [MaquinaController::class, 'create'])->name('maquinas.create');
    Route::get('moldes/create', [MoldeController::class, 'create'])->name('moldes.create');
    Route::get('resinas/create', [ResinaController::class, 'create'])->name('resinas.create');
    Route::get('operarios/create', [OperarioController::class, 'create'])->name('operarios.create');

    // Recursos con restricción numérica estricta para el ID
    Route::resource('maquinas', MaquinaController::class)->whereNumber('maquina');
    Route::resource('moldes', MoldeController::class)->whereNumber('molde');
    Route::resource('resinas', ResinaController::class)->whereNumber('resina');
    Route::resource('operarios', OperarioController::class)->whereNumber('operario');
});

require __DIR__.'/auth.php';
