<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard Principal de TENEBRIOS GOLD conectado a MySQL.
     */
    public function index(): View
    {
        $inicioMes = now()->startOfMonth();
        $finMes    = now()->endOfMonth();

        // 1. Cuatro tarjetas KPI calculadas directamente desde MySQL
        $ingresosMes = (float) Pedido::whereBetween('fecha_pedido', [$inicioMes, $finMes])
            ->where('estado_pedido', '!=', 'Cancelado')
            ->sum('total');

        $pedidosPendientes = Pedido::where('estado_pedido', 'Pendiente')->count();

        $clientesActivos = Usuario::whereHas('rol', function ($q) {
            $q->where('nombre_rol', 'Cliente');
        })->where('estado', 'Activo')->count();

        $alertasStock = Producto::whereColumn('stock', '<=', 'stock_minimo')->count();

        $kpis = [
            'ingresos_mes'       => $ingresosMes,
            'pedidos_pendientes' => $pedidosPendientes,
            'clientes_activos'   => $clientesActivos,
            'alertas_stock'      => $alertasStock,
        ];

        // 2. Gráfica 1: "Rendimiento de Ventas (COP)" por mes (Año 2026)
        $añoActual = now()->year;
        $ventasMesesRaw = Pedido::selectRaw('MONTH(fecha_pedido) as mes, SUM(total) as total')
            ->whereYear('fecha_pedido', $añoActual)
            ->where('estado_pedido', '!=', 'Cancelado')
            ->groupBy(DB::raw('MONTH(fecha_pedido)'))
            ->pluck('total', 'mes')
            ->toArray();

        $mesesNombres = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        $chartVentasLabels = array_values($mesesNombres);
        $chartVentasData   = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartVentasData[] = (float) ($ventasMesesRaw[$m] ?? 0);
        }

        // 3. Gráfica 2: "Estado de Pedidos" (Dona: Pendiente, Confirmado/Enviado, Entregado, Cancelado)
        $chartPedidosEstados = [
            'Pendiente'            => Pedido::where('estado_pedido', 'Pendiente')->count(),
            'Confirmado / Enviado' => Pedido::whereIn('estado_pedido', ['Confirmado', 'Procesando', 'Enviado', 'Despachado'])->count(),
            'Entregado'            => Pedido::whereIn('estado_pedido', ['Entregado', 'Completado'])->count(),
            'Cancelado'            => Pedido::where('estado_pedido', 'Cancelado')->count(),
        ];

        // 4. Tabla Inferior 1: "Pedidos Recientes" (últimos 5 pedidos con enlace al detalle)
        $pedidosRecientes = Pedido::with('usuario')
            ->orderByDesc('fecha_pedido')
            ->orderByDesc('id_pedido')
            ->take(5)
            ->get();

        // 5. Tabla Inferior 2: "Alertas de Stock Bajo" (productos con stock <= stock_minimo y barra de nivel)
        $productosBajoStock = Producto::with('categoria')
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->get();

        // Traer también existencias ordenadas por menor stock para visualización informativa
        $productosNivelStock = $productosBajoStock->isNotEmpty()
            ? $productosBajoStock
            : Producto::with('categoria')->orderBy('stock')->take(5)->get();

        return view('admin.inicio', compact(
            'kpis',
            'chartVentasLabels',
            'chartVentasData',
            'chartPedidosEstados',
            'pedidosRecientes',
            'productosBajoStock',
            'productosNivelStock'
        ));
    }

    /**
     * Módulo de Promociones y Cupones.
     */
    public function promociones(): View
    {
        $promociones = Promocion::orderByDesc('id_promocion')->get();
        return view('admin.promociones.index', compact('promociones'));
    }

    /**
     * Módulo de Chats y Atención al Cliente.
     */
    public function chats(): View
    {
        $mensajes = DB::table('chat_mensaje')
            ->join('usuario', 'chat_mensaje.id_usuario', '=', 'usuario.id_usuario')
            ->select('chat_mensaje.*', 'usuario.nombre', 'usuario.apellido', 'usuario.correo')
            ->orderByDesc('fecha_envio')
            ->limit(50)
            ->get();

        $contactos = DB::table('mensaje_contacto')->orderByDesc('fecha_envio')->limit(50)->get();

        return view('admin.chats.index', compact('mensajes', 'contactos'));
    }

    /**
     * Módulo de Seguridad y Permisos.
     */
    public function seguridad(): View
    {
        $roles = Rol::withCount('usuarios')->get();
        $usuarios = Usuario::with('rol')->orderByDesc('id_usuario')->limit(20)->get();
        return view('admin.seguridad.index', compact('roles', 'usuarios'));
    }

    /**
     * Módulo de Ajustes del Sistema.
     */
    public function ajustes(): View
    {
        return view('admin.ajustes.index');
    }

    /**
     * Endpoint API JSON de compatibilidad para clientes auxiliares.
     */
    public function data(): JsonResponse
    {
        $pedidos = Pedido::with(['usuario', 'detalles.producto.categoria'])
            ->orderByDesc('fecha_pedido')
            ->orderByDesc('id_pedido')
            ->limit(100)
            ->get();

        $productos = Producto::with('categoria')->orderBy('id_producto')->get();
        $movimientos = MovimientoInventario::with('producto')
            ->orderByDesc('fecha_movimiento')
            ->orderByDesc('id_movimiento')
            ->limit(100)
            ->get();

        $pedidosDelMes = Pedido::whereBetween('fecha_pedido', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('estado_pedido', '!=', 'Cancelado');

        $productosPanel = $productos->map(fn (Producto $producto) => [
            'id' => 'PROD-' . str_pad((string) $producto->id_producto, 3, '0', STR_PAD_LEFT),
            'backendId' => (string) $producto->id_producto,
            'name' => $producto->nombre_producto,
            'category' => $producto->categoria?->nombre_categoria ?? 'Sin categoría',
            'categoryId' => (string) $producto->id_categoria,
            'price' => (float) $producto->precio,
            'stock' => (int) $producto->stock,
            'minStock' => (int) ($producto->stock_minimo ?? 0),
            'status' => $producto->estado,
            'img' => asset('assets/img/' . ($producto->imagen ?: 'Tenebrios_img.png')),
            'imagePath' => $producto->imagen,
            'desc' => $producto->descripcion ?? '',
        ])->values();

        return response()->json([
            'categories' => Categoria::orderBy('nombre_categoria')->get()->map(fn (Categoria $categoria) => [
                'id' => (string) $categoria->id_categoria,
                'name' => $categoria->nombre_categoria,
                'color' => '#e6a100',
            ])->values(),
            'products' => $productosPanel,
            'orders' => $pedidos->map(fn (Pedido $pedido) => [
                'id' => (string) $pedido->id_pedido,
                'number' => $pedido->numero_pedido ?: 'PED-' . (1000 + $pedido->id_pedido),
                'customer' => trim(($pedido->usuario?->nombre ?? '') . ' ' . ($pedido->usuario?->apellido ?? '')),
                'email' => $pedido->usuario?->correo ?? '',
                'phone' => $pedido->telefono_contacto ?: ($pedido->usuario?->telefono ?? ''),
                'date' => optional($pedido->fecha_pedido)->format('d/m/Y'),
                'time' => optional($pedido->fecha_pedido)->format('H:i'),
                'status' => $pedido->estado_pedido,
                'payment' => $pedido->metodo_pago,
                'subtotal' => (float) $pedido->subtotal,
                'shipping' => (float) $pedido->costo_envio,
                'total' => (float) $pedido->total,
                'items' => $pedido->detalles->map(fn ($detalle) => [
                    'name' => $detalle->producto?->nombre_producto ?? 'Producto no disponible',
                    'category' => $detalle->producto?->categoria?->nombre_categoria ?? 'Sin categoría',
                    'qty' => (int) $detalle->cantidad,
                    'price' => (float) $detalle->precio_unitario,
                    'img' => asset('assets/img/' . ($detalle->producto?->imagen ?: 'Tenebrios_img.png')),
                ])->values(),
                'extraCount' => $pedido->detalles->count(),
            ])->values(),
            'inventory' => $productos->map(function (Producto $producto) use ($movimientos) {
                $ultimoMovimiento = $movimientos->firstWhere('id_producto', $producto->id_producto);

                return [
                    'id' => (string) $producto->id_producto,
                    'species' => $producto->nombre_producto,
                    'scientific' => $producto->categoria?->nombre_categoria ?? 'Producto',
                    'stock' => (int) $producto->stock,
                    'minStock' => (int) ($producto->stock_minimo ?? 0),
                    'maxCapacity' => max((int) $producto->stock, (int) ($producto->stock_minimo ?? 0) * 3, 1),
                    'change' => null,
                    'updated' => optional($ultimoMovimiento?->fecha_movimiento)->format('d/m/Y H:i') ?? 'Sin movimientos',
                    'img' => asset('assets/img/' . ($producto->imagen ?: 'Tenebrios_img.png')),
                    'status' => $producto->estado,
                ];
            })->values(),
            'inventoryMovements' => $movimientos->map(fn (MovimientoInventario $movimiento) => [
                'date' => optional($movimiento->fecha_movimiento)->format('d/m/Y H:i'),
                'species' => $movimiento->producto->nombre_producto ?? 'Producto eliminado',
                'type' => $movimiento->tipo_movimiento === 'ENTRADA' ? 'Entrada'
                    : ($movimiento->tipo_movimiento === 'SALIDA' ? 'Salida' : 'Ajuste'),
                'qty' => ($movimiento->tipo_movimiento === 'SALIDA' ? '-' : '+')
                    . number_format((int) $movimiento->cantidad, 0, ',', '.'),
                'reason' => $movimiento->observacion,
            ])->values(),
            'summary' => [
                'totalUsers' => Usuario::count(),
                'totalOrders' => Pedido::count(),
                'pendingOrders' => Pedido::where('estado_pedido', 'Pendiente')->count(),
                'confirmedOrders' => Pedido::where('estado_pedido', 'Procesando')->count(),
                'deliveredOrders' => Pedido::whereIn('estado_pedido', ['Entregado', 'Completado'])->count(),
                'monthlyOrders' => (clone $pedidosDelMes)->count(),
                'monthlyRevenue' => (float) (clone $pedidosDelMes)->sum('total'),
            ],
        ]);
    }
}
