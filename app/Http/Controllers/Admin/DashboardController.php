<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.panel');
    }

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
                'color' => '#536E58',
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
