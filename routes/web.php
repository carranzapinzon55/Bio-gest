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
use App\Http\Controllers\Client\ChatClienteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductoAdminController;
use App\Http\Controllers\Admin\InventarioAdminController;
use App\Http\Controllers\Admin\PedidoAdminController;
use App\Http\Controllers\Admin\UsuarioAdminController;
use App\Http\Controllers\Admin\EstadisticaAdminController;
use App\Http\Controllers\Admin\PromocionAdminController;
use App\Http\Controllers\Admin\SeguridadAdminController;
use App\Http\Controllers\Admin\ChatAdminController;
use App\Http\Controllers\Admin\AjusteAdminController;
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

// Alias amigables para /productos y /productos/{id}
Route::get('/productos', [CatalogoController::class, 'index'])->name('productos.index');
Route::get('/productos/{id_producto}', [CatalogoController::class, 'show'])->name('productos.show');

// Ruta JSON para sugerencias del buscador en tiempo real
Route::get('/buscar-sugerencias', [CatalogoController::class, 'sugerencias'])->name('buscar.sugerencias');

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
        return response()->json(['active' => false, 'redirect' => route('home')]);
    }
    if (auth()->user()->esAdmin()) {
        \App\Models\Configuracion::set('admin_last_seen', (string) now()->timestamp, 'sistema', 'Última actividad del administrador');
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
    Route::post('/cupon', [CarritoController::class, 'aplicarCupon'])->name('cupon.aplicar');
    Route::delete('/cupon', [CarritoController::class, 'quitarCupon'])->name('cupon.quitar');
});

Route::middleware(['auth'])->group(function () {
    // Checkout / Finalizar Compra (soporta /finalizar-compra y /checkout)
    Route::get('/finalizar-compra', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/finalizar-compra', [CheckoutController::class, 'procesar'])->name('checkout.procesar');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.alias');

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

        // Chat en Vivo del Cliente Autenticado con el Administrador
        Route::get('/chat', [ChatClienteController::class, 'index'])->name('chat.index');
        Route::post('/chat', [ChatClienteController::class, 'enviar'])->name('chat.enviar');
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
    Route::get('/estadisticas/exportar-excel', [EstadisticaAdminController::class, 'exportarExcel'])->name('estadisticas.excel');
    Route::get('/estadisticas/exportar-pdf', [EstadisticaAdminController::class, 'exportarPdf'])->name('estadisticas.pdf');

    // Redirección y compatibilidad con reportes anteriores
    Route::get('/reportes/ventas', fn () => redirect()->route('admin.estadisticas'))->name('reportes.ventas');
    Route::get('/reportes/ventas/exportar-csv', [EstadisticaAdminController::class, 'exportarCsv'])->name('reportes.ventas.csv');

    // 3. Gestión y Seguimiento de Pedidos
    Route::get('/pedidos', [PedidoAdminController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{id_pedido}', [PedidoAdminController::class, 'show'])->name('pedidos.show');
    Route::patch('/pedidos/{id_pedido}/estado', [PedidoAdminController::class, 'actualizarEstado'])->name('pedidos.estado');

    // 4. Gestión de Productos (CRUD y Categorías)
    Route::resource('productos', ProductoAdminController::class)->except(['show']);
    Route::post('/productos/{id_producto}/stock', [ProductoAdminController::class, 'ajustarStock'])->name('productos.stock');
    Route::post('/categorias', [ProductoAdminController::class, 'storeCategoria'])->name('categorias.store');

    // 5. Gestión de Inventario y Movimientos
    Route::get('/inventario', [InventarioAdminController::class, 'index'])->name('inventario.index');
    Route::post('/inventario/movimiento', [InventarioAdminController::class, 'registrarMovimiento'])->name('inventario.movimiento');

    // 6. Alertas de Stock Bajo
    Route::get('/alertas-stock', [InventarioAdminController::class, 'alertasStock'])->name('alertas-stock');

    // 7. Promociones
    Route::get('/promociones', [PromocionAdminController::class, 'index'])->name('promociones.index');
    Route::post('/promociones', [PromocionAdminController::class, 'store'])->name('promociones.store');
    Route::put('/promociones/{id_promocion}', [PromocionAdminController::class, 'update'])->name('promociones.update');
    Route::patch('/promociones/{id_promocion}/estado', [PromocionAdminController::class, 'toggleEstado'])->name('promociones.estado');
    Route::delete('/promociones/{id_promocion}', [PromocionAdminController::class, 'destroy'])->name('promociones.destroy');

    // 8. Centro de Mensajes y Chatbot BIO-GEST
    Route::get('/chats', [ChatAdminController::class, 'index'])->name('chats.index');
    Route::post('/chats/responder', [ChatAdminController::class, 'responderChat'])->name('chats.responder');
    Route::patch('/chats/contacto/{id_mensaje}/atender', [ChatAdminController::class, 'atenderContacto'])->name('chats.contacto.atender');
    Route::post('/chats/faq', [ChatAdminController::class, 'storeFaq'])->name('chats.faq.store');
    Route::put('/chats/faq/{id_faq}', [ChatAdminController::class, 'updateFaq'])->name('chats.faq.update');
    Route::delete('/chats/faq/{id_faq}', [ChatAdminController::class, 'destroyFaq'])->name('chats.faq.destroy');

    // 9. Gestión de Usuarios (Exclusivo Administrador y Ficha / Activación)
    Route::get('/usuarios', [UsuarioAdminController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/{id_usuario}', [UsuarioAdminController::class, 'show'])->name('usuarios.show');
    Route::post('/usuarios', [UsuarioAdminController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{id_usuario}', [UsuarioAdminController::class, 'update'])->name('usuarios.update');
    Route::match(['patch', 'post'], '/usuarios/{id_usuario}/estado', [UsuarioAdminController::class, 'toggleEstado'])->name('usuarios.estado');
    Route::delete('/usuarios/{id_usuario}', [UsuarioAdminController::class, 'destroy'])->name('usuarios.destroy');

    // 10. Seguridad, Roles y Políticas Institucionales
    Route::get('/seguridad', [SeguridadAdminController::class, 'index'])->name('seguridad.index');
    Route::post('/seguridad/politicas', [SeguridadAdminController::class, 'updatePoliticas'])->name('seguridad.politicas.update');

    // 11. Ajustes del Sistema y Parámetros Comerciales
    Route::get('/ajustes', [AjusteAdminController::class, 'index'])->name('ajustes.index');
    Route::post('/ajustes', [AjusteAdminController::class, 'update'])->name('ajustes.update');
    Route::post('/ajustes/limpiar-cache', [AjusteAdminController::class, 'limpiarCacheTienda'])->name('ajustes.limpiar.cache');
    Route::get('/ajustes/catalogo-precios', [AjusteAdminController::class, 'descargarCatalogoPrecios'])->name('ajustes.catalogo.precios');
    Route::get('/ajustes/respaldo-sql', [AjusteAdminController::class, 'descargarRespaldoSql'])->name('ajustes.respaldo.sql');

    // 12. Mi Perfil del Administrador
    Route::get('/perfil', [AjusteAdminController::class, 'perfil'])->name('perfil.index');
    Route::put('/perfil', [AjusteAdminController::class, 'updatePerfil'])->name('perfil.update');
});
