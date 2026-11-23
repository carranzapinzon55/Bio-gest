<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PedidoApiController extends Controller
{
    /**
     * Listar pedidos (Protegido: auth:sanctum).
     * Admin ve todos los pedidos; Cliente ve solo los propios.
     * GET /api/pedidos
     */
    public function index(Request $request)
    {
        $usuario = $request->user();
        $query = Pedido::with(['usuario', 'direccion', 'detalles.producto']);

        if (!$usuario->esAdmin()) {
            $query->where('id_usuario', $usuario->id_usuario);
        }

        if ($request->filled('estado_pedido')) {
            $query->where('estado_pedido', $request->estado_pedido);
        }

        $pedidos = $query->orderBy('fecha_pedido', 'desc')->get();

        return response()->json([
            'success' => true,
            'total'   => $pedidos->count(),
            'data'    => $pedidos,
        ], 200);
    }

    /**
     * Obtener detalle de un pedido (Protegido: auth:sanctum).
     * GET /api/pedidos/{id}
     */
    public function show(Request $request, $id)
    {
        $usuario = $request->user();
        $pedido = Pedido::with(['usuario', 'direccion', 'detalles.producto'])->find($id);

        if (!$pedido) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado.'
            ], 404);
        }

        // Validar que el cliente solo consulte sus propios pedidos
        if (!$usuario->esAdmin() && $pedido->id_usuario !== $usuario->id_usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. No tienes permisos para ver este pedido.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $pedido,
        ], 200);
    }

    /**
     * Crear pedido con transacción, validación estricta y descuento de stock.
     * POST /api/pedidos
     */
    public function store(Request $request)
    {
        $usuario = $request->user();

        $validator = Validator::make($request->all(), [
            'id_direccion'       => 'nullable|exists:direccion_usuario,id_direccion',
            'metodo_pago'        => 'nullable|string|max:50',
            'direccion_entrega'  => 'nullable|string|max:255',
            'ciudad_entrega'     => 'nullable|string|max:100',
            'telefono_contacto'  => 'nullable|string|max:20',
            'costo_envio'        => 'nullable|numeric|gte:0',
            'items'              => 'required|array|min:1',
            'items.*.id_producto'=> 'required|exists:producto,id_producto',
            'items.*.cantidad'   => 'required|integer|gt:0', // Restricción CHECK: cantidad > 0
        ], [
            'items.required'            => 'Debe incluir al menos un producto en el pedido.',
            'items.*.id_producto.exists'=> 'Uno de los productos seleccionados no existe.',
            'items.*.cantidad.gt'       => 'La cantidad solicitada debe ser estrictamente mayor a 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $nuevoPedido = DB::transaction(function () use ($request, $usuario) {
                $subtotal = 0;
                $itemsAProcesar = [];

                // 1. Validar disponibilidad y bloquear registros para actualización concurrente
                foreach ($request->items as $itemData) {
                    $producto = Producto::where('id_producto', $itemData['id_producto'])
                        ->lockForUpdate()
                        ->first();

                    if (!$producto) {
                        throw new \Exception("El producto ID {$itemData['id_producto']} no fue encontrado.");
                    }

                    if ($producto->estado !== 'Activo') {
                        throw new \Exception("El producto '{$producto->nombre_producto}' no está activo para venta.");
                    }

                    if ($producto->stock < $itemData['cantidad']) {
                        throw new \Exception("Stock insuficiente para el producto '{$producto->nombre_producto}'. Stock disponible: {$producto->stock}, cantidad solicitada: {$itemData['cantidad']}.");
                    }

                    $itemSubtotal = $producto->precio * $itemData['cantidad'];
                    $subtotal += $itemSubtotal;

                    $itemsAProcesar[] = [
                        'producto' => $producto,
                        'cantidad' => $itemData['cantidad'],
                        'precio'   => $producto->precio,
                        'subtotal' => $itemSubtotal,
                    ];
                }

                // 2. Calcular costo de envío y total
                // Política BIO-GEST: Envío gratis a partir de $122.900 (Paquete Recomendado en adelante)
                $costoEnvio = $request->filled('costo_envio')
                    ? (float)$request->costo_envio
                    : ($subtotal >= 122900 ? 0.00 : 10000.00);

                $total = $subtotal + $costoEnvio;

                // Generar número de pedido único legible
                $numeroPedido = 'PED-' . date('Ymd') . '-' . strtoupper(Str::random(4));

                // 3. Crear el pedido
                $pedido = Pedido::create([
                    'numero_pedido'     => $numeroPedido,
                    'id_usuario'        => $usuario->id_usuario,
                    'id_direccion'      => $request->id_direccion,
                    'subtotal'          => $subtotal,
                    'costo_envio'       => $costoEnvio,
                    'total'             => $total,
                    'metodo_pago'       => $request->metodo_pago ?? 'Contraentrega',
                    'estado_pago'       => 'Pendiente',
                    'estado_pedido'     => 'Pendiente',
                    'direccion_entrega' => $request->direccion_entrega ?? optional($usuario->direcciones()->first())->direccion,
                    'ciudad_entrega'    => $request->ciudad_entrega ?? optional($usuario->direcciones()->first())->ciudad,
                    'telefono_contacto' => $request->telefono_contacto ?? $usuario->telefono,
                    'fecha_pedido'      => now(),
                ]);

                // 4. Crear detalles y descontar stock de cada producto
                // (El trigger trg_auditoria_stock_producto registra automáticamente el movimiento SALIDA)
                foreach ($itemsAProcesar as $procesar) {
                    PedidoDetalle::create([
                        'id_pedido'       => $pedido->id_pedido,
                        'id_producto'     => $procesar['producto']->id_producto,
                        'cantidad'        => $procesar['cantidad'],
                        'precio_unitario' => $procesar['precio'],
                        'subtotal'        => $procesar['subtotal'],
                    ]);

                    // Descontar inventario
                    $procesar['producto']->stock -= $procesar['cantidad'];
                    $procesar['producto']->save();
                }

                return $pedido;
            });

            $nuevoPedido->load(['detalles.producto', 'usuario', 'direccion']);

            return response()->json([
                'success' => true,
                'message' => 'Pedido generado exitosamente y stock descontado.',
                'data'    => $nuevoPedido,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Actualizar estado de pedido (Protegido: Administrador).
     * PUT /api/pedidos/{id}
     */
    public function update(Request $request, $id)
    {
        $usuario = $request->user();

        if (!$usuario->esAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Solo un administrador puede actualizar el estado de los pedidos.'
            ], 403);
        }

        $pedido = Pedido::with('detalles.producto')->find($id);

        if (!$pedido) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado_pedido' => 'required|in:Pendiente,Procesando,Despachado,Listo para retiro,Entregado,Completado,Cancelado',
            'estado_pago'   => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $estadoAnterior = $pedido->estado_pedido;
        $nuevoEstado = $request->estado_pedido;

        DB::transaction(function () use ($pedido, $estadoAnterior, $nuevoEstado, $request) {
            // Si cambia a Cancelado desde un estado activo, restaurar el inventario
            if ($nuevoEstado === 'Cancelado' && $estadoAnterior !== 'Cancelado') {
                foreach ($pedido->detalles as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    if ($producto) {
                        $producto->stock += $detalle->cantidad;
                        $producto->save();
                    }
                }
            }

            // Si se reactiva un pedido cancelado, validar y volver a descontar
            if ($estadoAnterior === 'Cancelado' && $nuevoEstado !== 'Cancelado') {
                foreach ($pedido->detalles as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    if ($producto) {
                        if ($producto->stock < $detalle->cantidad) {
                            throw new \Exception("No hay stock suficiente para reactivar el pedido. Producto: {$producto->nombre_producto}");
                        }
                        $producto->stock -= $detalle->cantidad;
                        $producto->save();
                    }
                }
            }

            $pedido->estado_pedido = $nuevoEstado;
            if ($request->filled('estado_pago')) {
                $pedido->estado_pago = $request->estado_pago;
            }
            $pedido->save();
        });

        $pedido->refresh()->load(['detalles.producto', 'usuario', 'direccion']);

        return response()->json([
            'success' => true,
            'message' => "Estado del pedido actualizado a '{$nuevoEstado}' correctamente.",
            'data'    => $pedido,
        ], 200);
    }

    /**
     * Cancelar pedido y restaurar stock.
     * DELETE /api/pedidos/{id}
     */
    public function destroy(Request $request, $id)
    {
        $usuario = $request->user();
        $pedido = Pedido::with('detalles.producto')->find($id);

        if (!$pedido) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado.'
            ], 404);
        }

        // Cliente solo puede cancelar sus propios pedidos y si están en estado 'Pendiente'
        if (!$usuario->esAdmin()) {
            if ($pedido->id_usuario !== $usuario->id_usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. No tienes permisos para cancelar este pedido.'
                ], 403);
            }

            if ($pedido->estado_pedido !== 'Pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes cancelar pedidos que se encuentren en estado Pendiente.'
                ], 422);
            }
        }

        if ($pedido->estado_pedido === 'Cancelado') {
            return response()->json([
                'success' => false,
                'message' => 'El pedido ya se encuentra cancelado.'
            ], 400);
        }

        DB::transaction(function () use ($pedido) {
            // Restaurar el stock de cada producto involucrado
            foreach ($pedido->detalles as $detalle) {
                $producto = Producto::find($detalle->id_producto);
                if ($producto) {
                    $producto->stock += $detalle->cantidad;
                    $producto->save();
                }
            }

            $pedido->estado_pedido = 'Cancelado';
            $pedido->save();
        });

        $pedido->refresh()->load(['detalles.producto', 'usuario', 'direccion']);

        return response()->json([
            'success' => true,
            'message' => 'Pedido cancelado y stock de productos restaurado exitosamente.',
            'data'    => $pedido,
        ], 200);
    }
}
