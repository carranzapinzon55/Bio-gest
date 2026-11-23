<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProductoApiController;
use App\Http\Controllers\Api\PedidoApiController;

/*
|--------------------------------------------------------------------------
| API Routes - BIO-GEST (Tenebrios Gold)
|--------------------------------------------------------------------------
|
| Rutas oficiales de la API REST de BIO-GEST. Se cargan bajo el prefijo /api.
|
*/

// ========================================================
// 1. AUTENTICACIÓN PÚBLICA
// ========================================================
Route::post('/login', [AuthApiController::class, 'login'])->name('api.login');
Route::post('/registro', [AuthApiController::class, 'registro'])->name('api.registro');

// ========================================================
// 2. CATÁLOGO PÚBLICO DE PRODUCTOS
// ========================================================
Route::get('/productos', [ProductoApiController::class, 'index'])->name('api.productos.index');
Route::get('/productos/{id}', [ProductoApiController::class, 'show'])->name('api.productos.show');

// ========================================================
// 3. RUTAS PROTEGIDAS CON AUTH:SANCTUM
// ========================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- Perfil y Cierre de Sesión ---
    Route::get('/perfil', [AuthApiController::class, 'perfil'])->name('api.perfil');
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('api.logout');

    // --- Gestión de Productos (Exclusivo Administrador: auth:sanctum + role:Administrador) ---
    Route::middleware('role:Administrador')->group(function () {
        Route::post('/productos', [ProductoApiController::class, 'store'])->name('api.productos.store');
        Route::put('/productos/{id}', [ProductoApiController::class, 'update'])->name('api.productos.update');
        Route::delete('/productos/{id}', [ProductoApiController::class, 'destroy'])->name('api.productos.destroy');
    });

    // --- Gestión de Pedidos ---
    // GET /api/pedidos (Admin ve todos, Cliente ve solo los suyos)
    Route::get('/pedidos', [PedidoApiController::class, 'index'])->name('api.pedidos.index');
    // GET /api/pedidos/{id} (Admin o propietario)
    Route::get('/pedidos/{id}', [PedidoApiController::class, 'show'])->name('api.pedidos.show');
    // POST /api/pedidos (Transacción, validación y descuento de stock)
    Route::post('/pedidos', [PedidoApiController::class, 'store'])->name('api.pedidos.store');
    // PUT /api/pedidos/{id} (Admin actualiza estado)
    Route::put('/pedidos/{id}', [PedidoApiController::class, 'update'])->name('api.pedidos.update');
    // DELETE /api/pedidos/{id} (Cancela y restaura stock)
    Route::delete('/pedidos/{id}', [PedidoApiController::class, 'destroy'])->name('api.pedidos.destroy');
});
