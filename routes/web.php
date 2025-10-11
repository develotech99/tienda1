<?php

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProductoTipoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth/login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    //EDITAR PERFIL CON LA SESSION INICIADA
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //PRODUCTOS
    Route::prefix('productos')->name('productos.')->group(function () {

        // TIPO DE PRODUCTOS
        Route::get('tipo', [ProductoTipoController::class, 'index'])->name('tipo.index');
        Route::post('agregar/tipo', [ProductoTipoController::class, 'store'])->name('tipo.store');
        Route::get('tipo/ObtenerDatosAPI', [ProductoTipoController::class, 'show'])->name('tipo.obtener-datos');
        Route::put('actualizar/tipo', [ProductoTipoController::class, 'update'])->name('tipo.actualizar');
        Route::delete('eliminar/tipo', [ProductoTipoController::class, 'destroy'])->name('tipo.destroy');

        // REGISTROS Y STOCK DE PRODUCTOS
        Route::get('/', [ProductoController::class, 'index'])->name('index');
        Route::get('/ObtenerDatosAPI', [ProductoController::class, 'obtenerDatosAPI'])->name('obtener.datos');
        Route::get('/{id}', [ProductoController::class, 'show'])->name('show');
        Route::post('/', [ProductoController::class, 'store'])->name('store');
        Route::put('/{id}', [ProductoController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductoController::class, 'destroy'])->name('destroy');
        Route::post('/buscar-codigo', [ProductoController::class, 'buscarPorCodigo'])->name('buscar.codigo');
        Route::post('/{id}/entrada-stock', [ProductoController::class, 'entradaStock'])->name('entrada.stock');
        Route::get('/stock', [ProductoController::class, 'stockView'])->name('stock.view');
        Route::get('/barcode/{codigo}', [ProductoController::class, 'etiquetaBarcode'])->name('barcode');
        Route::post('/{id}/asignar-codigo', [ProductoController::class, 'asignarCodigo'])->name('asignar.codigo');
    });
});

require __DIR__ . '/auth.php';
