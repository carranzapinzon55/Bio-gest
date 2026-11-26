<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
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

        // 4 KPIs reales desde MySQL
        $kpis = [
            'total'                => Pedido::count(),
            'pendientes'           => Pedido::where('estado_pedido', 'Pendiente')->count(),
            'confirmados_enviados' => Pedido::whereIn('estado_pedido', ['Confirmado', 'Procesando', 'Enviado', 'Despachado', 'Listo para retiro'])->count(),
            'entregados'           => Pedido::whereIn('estado_pedido', ['Entregado', 'Completado'])->count(),
        ];

        return view('admin.pedidos.index', compact('pedidos', 'kpis'));
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
     * Calcula el siguiente estado lógico para avanzar el pedido a un solo clic:
     * Pendiente -> Confirmado -> Enviado -> Entregado.
     */
    private function obtenerSiguienteEstado(string $estadoActual): string
    {
        return match ($estadoActual) {
            'Pendiente'  => 'Confirmado',
            'Confirmado', 'Procesando' => 'Enviado',
            'Enviado', 'Despachado', 'Listo para retiro' => 'Entregado',
            default => $estadoActual,
        };
    }

    /**
     * Actualizar estado del pedido (soporta acción rápida, 'Confirmado', 'Enviado' -> 'Despachado' y restaura stock si se cancela).
     */
    public function actualizarEstado(Request $request, int $id_pedido): RedirectResponse|JsonResponse
    {
        $pedido = Pedido::with('detalles.producto')->findOrFail($id_pedido);

        // Soporte para acción rápida a un clic
        $accionRapida = $request->input('accion_rapida');

        if ($accionRapida === 'avanzar') {
            $nuevoEstadoInput = $this->obtenerSiguienteEstado($pedido->estado_pedido);
            $nuevoEstadoPago = ($nuevoEstadoInput === 'Entregado' && $pedido->metodo_pago === 'Contraentrega') ? 'Aprobado' : $pedido->estado_pago;
        } elseif ($accionRapida === 'confirmar_pago') {
            $nuevoEstadoInput = ($pedido->estado_pedido === 'Pendiente') ? 'Confirmado' : $pedido->estado_pedido;
            $nuevoEstadoPago = 'Aprobado';
        } elseif ($accionRapida === 'cancelar') {
            $nuevoEstadoInput = 'Cancelado';
            $nuevoEstadoPago = ($pedido->estado_pago === 'Aprobado') ? 'Reembolsado' : $pedido->estado_pago;
        } else {
            $datos = $request->validate([
                'estado_pedido' => [
                    'required',
                    'in:Pendiente,Confirmado,Procesando,Enviado,Despachado,Listo para retiro,Entregado,Completado,Cancelado'
                ],
                'estado_pago' => [
                    'nullable',
                    'string',
                    'in:Pendiente,Aprobado,Rechazado,Reembolsado'
                ],
            ]);
            $nuevoEstadoInput = $datos['estado_pedido'];
            $nuevoEstadoPago = $datos['estado_pago'] ?? $pedido->estado_pago;
        }

        // Compatibilidad con el ENUM de la base de datos ('Despachado' almacena 'Enviado')
        $nuevoEstadoDb = ($nuevoEstadoInput === 'Enviado') ? 'Despachado' : $nuevoEstadoInput;
        $estadoAnterior = $pedido->estado_pedido;

        try {
            DB::transaction(function () use ($pedido, $estadoAnterior, $nuevoEstadoDb, $nuevoEstadoPago) {
                // Si cambia a 'Cancelado' desde un estado activo, reingresar stock automáticamente al kardex
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
                if (!empty($nuevoEstadoPago)) {
                    $updateData['estado_pago'] = $nuevoEstadoPago;
                }

                $pedido->update($updateData);
            });

            $etiquetaEstado = ($nuevoEstadoInput === 'Enviado' || $nuevoEstadoDb === 'Despachado') ? 'Enviado (Despachado)' : $nuevoEstadoDb;

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Estado del pedido {$pedido->numero_pedido} actualizado a '{$etiquetaEstado}'.",
                    'data'    => $pedido->fresh()->load(['usuario', 'detalles.producto', 'direccion']),
                ]);
            }

            return back()->with('success', "Confirmación: El pedido {$pedido->numero_pedido} avanzó a estado '{$etiquetaEstado}'.");
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }
}
