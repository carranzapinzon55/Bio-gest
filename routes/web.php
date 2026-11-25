<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\CatalogoController;
use App\Http\Controllers\Shop\CalculadoraController;
use App\Http\Controllers\Shop\ContactoFaqController;
use App\Http\Controllers\Client\CarritoController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\PedidoClienteController;
use App\Http\Controllers\Client\PerfilController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductoAdminController;
use App\Http\Controllers\Admin\InventarioAdminController;
use App\Http\Controllers\Admin\PedidoAdminController;
use App\Http\Controllers\Admin\UsuarioAdminController;
use App\Http\Controllers\Admin\EstadisticaAdminController;
use App\Http\Controllers\ReporteController;

/*
|--------------------------------------------------------------------------
| 1. MÓDULO PÚBLICO Y TIENDA (Tenebrios-Gold)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('catalogo')->name('catalogo.')->group(function () {
    Route::get('/', [CatalogoController::class, 'index'])->name('index');
    Route::get('/producto/{id_producto}', [CatalogoController::class, 'show'])->name('show');
});

Route::get('/calculadora-alimento', [CalculadoraController::class, 'index'])->name('calculadora.index');
Route::post('/calculadora-alimento/calcular', [CalculadoraController::class, 'calcular'])->name('calculadora.calcular');

Route::view('/nosotros', 'shop.nosotros')->name('nosotros');
Route::view('/envios-y-garantias', 'shop.envios')->name('envios');

Route::get('/faq', [ContactoFaqController::class, 'faq'])->name('faq');
Route::get('/api/chatbot-faq', [ContactoFaqController::class, 'chatbotQuery'])->name('faq.chatbot');
Route::get('/contacto', [ContactoFaqController::class, 'contactoForm'])->name('contacto.index');
Route::post('/contacto', [ContactoFaqController::class, 'enviarContacto'])->name('contacto.store');

/*
|--------------------------------------------------------------------------
| 2. MÓDULO DE AUTENTICACIÓN
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registro'])->name('registro.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/auth/session-status', function () {
    if (!auth()->check()) {
        return response()->json(['active' => false, 'redirect' => route('login')]);
    }
    return response()->json(['active' => true]);
})->name('auth.session.status');

/*
|--------------------------------------------------------------------------
| 3. MÓDULO DE CARRITO Y CLIENTE AUTENTICADO
|--------------------------------------------------------------------------
*/
Route::prefix('carrito')->name('carrito.')->group(function () {
    Route::get('/', [CarritoController::class, 'index'])->name('index');
    Route::post('/agregar', [CarritoController::class, 'agregar'])->name('agregar');
    Route::patch('/actualizar/{id_producto}', [CarritoController::class, 'actualizar'])->name('actualizar');
    Route::delete('/eliminar/{id_producto}', [CarritoController::class, 'eliminar'])->name('eliminar');
});

Route::middleware(['auth'])->group(function () {
    // Checkout / Finalizar Compra
    Route::get('/finalizar-compra', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/finalizar-compra', [CheckoutController::class, 'procesar'])->name('checkout.procesar');

    // Cuenta de Cliente, Perfil, Direcciones y Mis Pedidos
    Route::prefix('mi-cuenta')->name('cliente.')->group(function () {
        Route::get('/', [PerfilController::class, 'index'])->name('cuenta');
        Route::get('/editar', [PerfilController::class, 'editar'])->name('perfil.editar');
        Route::put('/editar', [PerfilController::class, 'actualizar'])->name('perfil.actualizar');

        Route::get('/direcciones', [PerfilController::class, 'direcciones'])->name('direcciones.index');
        Route::post('/direcciones', [PerfilController::class, 'guardarDireccion'])->name('direcciones.store');
        Route::delete('/direcciones/{id_direccion}', [PerfilController::class, 'eliminarDireccion'])->name('direcciones.destroy');

        Route::get('/mis-pedidos', [PedidoClienteController::class, 'index'])->name('pedidos.index');
        Route::get('/mis-pedidos/{id_pedido}', [PedidoClienteController::class, 'show'])->name('pedidos.show');
    });
});

/*
|--------------------------------------------------------------------------
| 4. MÓDULO ADMINISTRATIVO (Protegido por Rol: Administrador)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Administrador'])->prefix('admin')->name('admin.')->group(function () {
    // 1. Dashboard Principal
    Route::get('/inicio', [DashboardController::class, 'index'])->name('inicio');
    Route::get('/panel-data', [DashboardController::class, 'data'])->name('panel.data');

    // 2. Módulo Unificado de Estadísticas y Reportes
    Route::get('/estadisticas', [EstadisticaAdminController::class, 'index'])->name('estadisticas');
    Route::get('/estadisticas/exportar-csv', [EstadisticaAdminController::class, 'exportarCsv'])->name('estadisticas.csv');

    // Redirección y compatibilidad con reportes anteriores
    Route::get('/reportes/ventas', fn () => redirect()->route('admin.estadisticas'))->name('reportes.ventas');
    Route::get('/reportes/ventas/exportar-csv', [EstadisticaAdminController::class, 'exportarCsv'])->name('reportes.ventas.csv');

    // 3. Gestión y Seguimiento de Pedidos
    Route::get('/pedidos', [PedidoAdminController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{id_pedido}', [PedidoAdminController::class, 'show'])->name('pedidos.show');
    Route::patch('/pedidos/{id_pedido}/estado', [PedidoAdminController::class, 'actualizarEstado'])->name('pedidos.estado');

    // 4. Gestión de Productos (CRUD)
    Route::resource('productos', ProductoAdminController::class)->except(['show']);
    Route::post('/productos/{id_producto}/stock', [ProductoAdminController::class, 'ajustarStock'])->name('productos.stock');

    // 5. Gestión de Inventario y Movimientos
    Route::get('/inventario', [InventarioAdminController::class, 'index'])->name('inventario.index');
    Route::post('/inventario/movimiento', [InventarioAdminController::class, 'registrarMovimiento'])->name('inventario.movimiento');

    // 6. Alertas de Stock Bajo
    Route::get('/alertas-stock', [InventarioAdminController::class, 'alertasStock'])->name('alertas-stock');

    // 7. Promociones
    Route::get('/promociones', [DashboardController::class, 'promociones'])->name('promociones.index');

    // 8. Chats de Clientes
    Route::get('/chats', [DashboardController::class, 'chats'])->name('chats.index');

    // 9. Gestión de Usuarios
    Route::get('/usuarios', [UsuarioAdminController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioAdminController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{id_usuario}', [UsuarioAdminController::class, 'update'])->name('usuarios.update');
    Route::patch('/usuarios/{id_usuario}/estado', [UsuarioAdminController::class, 'toggleEstado'])->name('usuarios.estado');
    Route::delete('/usuarios/{id_usuario}', [UsuarioAdminController::class, 'destroy'])->name('usuarios.destroy');

    // 10. Seguridad y Roles
    Route::get('/seguridad', [DashboardController::class, 'seguridad'])->name('seguridad.index');

    // 11. Ajustes del Sistema
    Route::get('/ajustes', [DashboardController::class, 'ajustes'])->name('ajustes.index');
});
