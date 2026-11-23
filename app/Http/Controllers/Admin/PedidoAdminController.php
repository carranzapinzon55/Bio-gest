<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PedidoAdminController extends Controller
{
    /**
     * Listado administrativo de pedidos.
     */
    public function index(Request $request): View
    {
        $query = Pedido::with(['usuario', 'detalles.producto', 'direccion'])
            ->orderByDesc('fecha_pedido');

        if ($request->filled('estado')) {
            $query->where('estado_pedido', $request->estado);
        }

        $pedidos = $query->paginate(15);

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
     * Actualizar estado del pedido (con restauración de inventario si se cancela).
     */
    public function actualizarEstado(Request $request, int $id_pedido): RedirectResponse
    {
        $datos = $request->validate([
            'estado_pedido' => [
                'required',
                'in:Pendiente,Procesando,Despachado,Listo para retiro,Entregado,Completado,Cancelado'
            ],
            'estado_pago' => [
                'nullable',
                'string',
                'max:50'
            ],
        ]);

        $pedido = Pedido::with('detalles.producto')->findOrFail($id_pedido);
        $estadoAnterior = $pedido->estado_pedido;
        $nuevoEstado    = $datos['estado_pedido'];

        DB::transaction(function () use ($pedido, $estadoAnterior, $nuevoEstado, $datos) {
            // Si cambia a 'Cancelado' desde un estado activo, reingresar stock
            if ($nuevoEstado === 'Cancelado' && $estadoAnterior !== 'Cancelado') {
                foreach ($pedido->detalles as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    if ($producto) {
                        $producto->increment('stock', $detalle->cantidad);
                    }
                }
            }

            // Si se reactiva un pedido cancelado, descontar nuevamente el stock
            if ($estadoAnterior === 'Cancelado' && $nuevoEstado !== 'Cancelado') {
                foreach ($pedido->detalles as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    if ($producto) {
                        if ($producto->stock < $detalle->cantidad) {
                            throw new \RuntimeException("Stock insuficiente para reactivar pedido. Producto: {$producto->nombre_producto}");
                        }
                        $producto->decrement('stock', $detalle->cantidad);
                    }
                }
            }

            $updateData = ['estado_pedido' => $nuevoEstado];
            if (!empty($datos['estado_pago'])) {
                $updateData['estado_pago'] = $datos['estado_pago'];
            }

            $pedido->update($updateData);
        });

        return back()->with('success', "Estado del pedido {$pedido->numero_pedido} actualizado a '{$nuevoEstado}'.");
    }
}
