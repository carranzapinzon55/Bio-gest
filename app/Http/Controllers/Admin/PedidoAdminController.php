<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PedidoAdminController extends Controller
{
    /**
     * Listado administrativo de pedidos con filtros por estado y búsqueda.
     */
    public function index(Request $request): View
    {
        $query = Pedido::with(['usuario', 'detalles.producto', 'direccion'])
            ->orderByDesc('fecha_pedido')
            ->orderByDesc('id_pedido');

        if ($request->filled('estado')) {
            $query->where('estado_pedido', $request->estado);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('numero_pedido', 'like', "%{$q}%")
                    ->orWhereHas('usuario', function ($u) use ($q) {
                        $u->where('nombre', 'like', "%{$q}%")
                          ->orWhere('apellido', 'like', "%{$q}%")
                          ->orWhere('correo', 'like', "%{$q}%");
                    });
            });
        }

        $pedidos = $query->paginate(15)->withQueryString();

        return view('admin.pedidos.index', compact('pedidos'));
    }

    /**
     * Detalle completo del pedido en el panel de administración.
     */
    public function show(int $id_pedido): View
    {
        $pedido = Pedido::with(['usuario', 'detalles.producto', 'direccion'])
            ->findOrFail($id_pedido);

        return view('admin.pedidos.show', compact('pedido'));
    }

    /**
     * Actualizar estado del pedido (soporta 'Enviado' -> 'Despachado' y restaura stock si se cancela).
     */
    public function actualizarEstado(Request $request, int $id_pedido): RedirectResponse
    {
        $datos = $request->validate([
            'estado_pedido' => [
                'required',
                'in:Pendiente,Procesando,Enviado,Despachado,Listo para retiro,Entregado,Completado,Cancelado'
            ],
            'estado_pago' => [
                'nullable',
                'string',
                'in:Pendiente,Aprobado,Rechazado,Reembolsado'
            ],
        ]);

        // Compatibilidad con el ENUM de la base de datos ('Despachado' representa 'Enviado')
        $nuevoEstadoInput = $datos['estado_pedido'];
        $nuevoEstadoDb    = $nuevoEstadoInput === 'Enviado' ? 'Despachado' : $nuevoEstadoInput;

        $pedido = Pedido::with('detalles.producto')->findOrFail($id_pedido);
        $estadoAnterior = $pedido->estado_pedido;

        try {
            DB::transaction(function () use ($pedido, $estadoAnterior, $nuevoEstadoDb, $datos) {
                // Si cambia a 'Cancelado' desde un estado activo, reingresar stock al inventario
                if ($nuevoEstadoDb === 'Cancelado' && $estadoAnterior !== 'Cancelado') {
                    foreach ($pedido->detalles as $detalle) {
                        $producto = Producto::lockForUpdate()->find($detalle->id_producto);
                        if ($producto) {
                            $stockPrevio = $producto->stock;
                            $conteoAntes = MovimientoInventario::where('id_producto', $producto->id_producto)->count();

                            $producto->increment('stock', $detalle->cantidad);
                            $producto->refresh();

                            $conteoDespues = MovimientoInventario::where('id_producto', $producto->id_producto)->count();
                            $obs = "Reingreso automático por cancelación del pedido {$pedido->numero_pedido} (Stock: {$stockPrevio} -> {$producto->stock})";

                            if ($conteoDespues === $conteoAntes) {
                                MovimientoInventario::create([
                                    'id_producto'      => $producto->id_producto,
                                    'tipo_movimiento'  => 'ENTRADA',
                                    'cantidad'         => $detalle->cantidad,
                                    'observacion'      => $obs,
                                    'fecha_movimiento' => now(),
                                ]);
                            } else {
                                MovimientoInventario::where('id_producto', $producto->id_producto)
                                    ->orderByDesc('id_movimiento')
                                    ->first()
                                    ?->update(['observacion' => $obs]);
                            }
                        }
                    }
                }

                // Si se reactiva un pedido que estaba cancelado, descontar nuevamente el stock
                if ($estadoAnterior === 'Cancelado' && $nuevoEstadoDb !== 'Cancelado') {
                    foreach ($pedido->detalles as $detalle) {
                        $producto = Producto::lockForUpdate()->find($detalle->id_producto);
                        if ($producto) {
                            if ($producto->stock < $detalle->cantidad) {
                                throw new \RuntimeException("Stock insuficiente para reactivar el pedido. Producto: {$producto->nombre_producto} (Disponible: {$producto->stock}, Requerido: {$detalle->cantidad}).");
                            }
                            $stockPrevio = $producto->stock;
                            $conteoAntes = MovimientoInventario::where('id_producto', $producto->id_producto)->count();

                            $producto->decrement('stock', $detalle->cantidad);
                            $producto->refresh();

                            $conteoDespues = MovimientoInventario::where('id_producto', $producto->id_producto)->count();
                            $obs = "Salida por reactivación del pedido {$pedido->numero_pedido} (Stock: {$stockPrevio} -> {$producto->stock})";

                            if ($conteoDespues === $conteoAntes) {
                                MovimientoInventario::create([
                                    'id_producto'      => $producto->id_producto,
                                    'tipo_movimiento'  => 'SALIDA',
                                    'cantidad'         => $detalle->cantidad,
                                    'observacion'      => $obs,
                                    'fecha_movimiento' => now(),
                                ]);
                            } else {
                                MovimientoInventario::where('id_producto', $producto->id_producto)
                                    ->orderByDesc('id_movimiento')
                                    ->first()
                                    ?->update(['observacion' => $obs]);
                            }
                        }
                    }
                }

                $updateData = ['estado_pedido' => $nuevoEstadoDb];
                if (!empty($datos['estado_pago'])) {
                    $updateData['estado_pago'] = $datos['estado_pago'];
                }

                $pedido->update($updateData);
            });

            $etiquetaEstado = $nuevoEstadoInput === 'Enviado' ? 'Enviado (Despachado)' : $nuevoEstadoDb;

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Estado del pedido actualizado a '{$etiquetaEstado}' correctamente.",
                    'data' => $pedido->fresh()->load(['usuario', 'detalles.producto', 'direccion']),
                ]);
            }

            return back()->with('success', "Confirmación: El estado del pedido {$pedido->numero_pedido} fue actualizado exitosamente a '{$etiquetaEstado}'.");
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }
}
