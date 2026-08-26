<?php

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
    Route::post('productos/{producto}/parametros', [ParametroPreformaController::class, 'storeOrUpdate'])->name('productos.parametros.store');
    Route::resource('productos', ProductoController::class)->except(['create', 'edit']);
});

require __DIR__.'/auth.php';
