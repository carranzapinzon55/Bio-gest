<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Pantalla de checkout para finalizar compra.
     */
    public function index(): View|RedirectResponse
    {
        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('catalogo.index')
                ->with('error', 'Tu carrito de compras está vacío.');
        }

        $direcciones = auth()->user()->direcciones;
        $subtotal = collect($carrito)->sum(fn ($item) => $item['precio'] * $item['cantidad']);
        $costoEnvio = $subtotal >= 122900 ? 0.00 : 10000.00;
        $total = $subtotal + $costoEnvio;

        return view('client.finalizar_compra', compact('carrito', 'direcciones', 'subtotal', 'costoEnvio', 'total'));
    }

    /**
     * Procesar pedido del cliente en transacción atómica:
     * a) Registra el pedido y su detalle con estado inicial 'Pendiente'.
     * b) Descuenta automáticamente el inventario disponible y registra el movimiento de SALIDA.
     * c) Disponible de inmediato en el panel del administrador.
     */
    public function procesar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'id_direccion'      => ['nullable', 'exists:direccion_usuario,id_direccion'],
            'direccion_entrega' => ['required', 'string', 'max:255'],
            'ciudad_entrega'    => ['required', 'string', 'max:100'],
            'telefono_contacto' => ['required', 'string', 'max:20'],
            'metodo_pago'       => ['required', 'in:Contraentrega,Transferencia,PSE,Tarjeta'],
        ], [
            'direccion_entrega.required' => 'La dirección de entrega es obligatoria.',
            'ciudad_entrega.required'    => 'La ciudad de entrega es obligatoria.',
            'telefono_contacto.required' => 'El teléfono de contacto es obligatorio.',
            'metodo_pago.required'       => 'Selecciona un método de pago válido.',
        ]);

        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('catalogo.index')->with('error', 'El carrito está vacío.');
        }

        try {
            $pedido = DB::transaction(function () use ($datos, $carrito) {
                $subtotal = 0;
                $productosBloqueados = [];

                // Validar existencias reales con bloqueo pesimista antes de insertar
                foreach ($carrito as $item) {
                    $producto = Producto::lockForUpdate()->findOrFail($item['id_producto']);

                    if ($producto->estado !== 'Activo') {
                        throw new \RuntimeException("El producto '{$producto->nombre_producto}' ya no se encuentra disponible.");
                    }

                    if ($producto->stock < $item['cantidad']) {
                        throw new \RuntimeException("Stock insuficiente para '{$producto->nombre_producto}'. Disponible: {$producto->stock} uds.");
                    }

                    $subtotal += ((float) $producto->precio * (int) $item['cantidad']);
                    $productosBloqueados[$producto->id_producto] = $producto;
                }

                $costoEnvio = $subtotal >= 122900 ? 0.00 : 10000.00;
                $total = $subtotal + $costoEnvio;

                $nuevoPedido = Pedido::create([
                    'id_usuario'        => auth()->id(),
                    'id_direccion'      => $datos['id_direccion'] ?? null,
                    'subtotal'          => $subtotal,
                    'costo_envio'       => $costoEnvio,
                    'total'             => $total,
                    'metodo_pago'       => $datos['metodo_pago'],
                    'estado_pago'       => 'Pendiente',
                    'estado_pedido'     => 'Pendiente',
                    'direccion_entrega' => $datos['direccion_entrega'],
                    'ciudad_entrega'    => $datos['ciudad_entrega'],
                    'telefono_contacto' => $datos['telefono_contacto'],
                    'fecha_pedido'      => now(),
                ]);

                $numeroPedido = 'PED-' . (1000 + $nuevoPedido->id_pedido);
                $nuevoPedido->update([
                    'numero_pedido' => $numeroPedido,
                ]);

                foreach ($carrito as $item) {
                    /** @var Producto $producto */
                    $producto = $productosBloqueados[$item['id_producto']];
                    $cantidadCompra = (int) $item['cantidad'];
                    $stockAnterior  = (int) $producto->stock;

                    PedidoDetalle::create([
                        'id_pedido'       => $nuevoPedido->id_pedido,
                        'id_producto'     => $producto->id_producto,
                        'cantidad'        => $cantidadCompra,
                        'precio_unitario' => $producto->precio,
                        'subtotal'        => $producto->precio * $cantidadCompra,
                    ]);

                    $conteoMovimientosAntes = MovimientoInventario::where('id_producto', $producto->id_producto)->count();

                    // Descontar inventario disponible
                    $producto->decrement('stock', $cantidadCompra);
                    $producto->refresh();

                    $conteoMovimientosDespues = MovimientoInventario::where('id_producto', $producto->id_producto)->count();
                    $observacionVenta = "Salida por venta en tienda - Pedido {$numeroPedido} (Stock previo: {$stockAnterior}, nuevo: {$producto->stock})";

                    // Si el trigger de MySQL no está instalado en el entorno, lo crea Eloquent directamente;
                    // si el trigger ya lo insertó, actualiza la observación con el número de pedido.
                    if ($conteoMovimientosDespues === $conteoMovimientosAntes) {
                        MovimientoInventario::create([
                            'id_producto'      => $producto->id_producto,
                            'tipo_movimiento'  => 'SALIDA',
                            'cantidad'         => $cantidadCompra,
                            'observacion'      => $observacionVenta,
                            'fecha_movimiento' => now(),
                        ]);
                    } else {
                        MovimientoInventario::where('id_producto', $producto->id_producto)
                            ->orderByDesc('id_movimiento')
                            ->first()
                            ?->update(['observacion' => $observacionVenta]);
                    }
                }

                // Limpiar carrito en sesión y en base de datos si existiera
                session()->forget('carrito');
                if (auth()->user()->carrito) {
                    auth()->user()->carrito->detalles()->delete();
                }

                return $nuevoPedido;
            });

            return redirect()->route('cliente.pedidos.index')
                ->with('success', "¡Pedido #{$pedido->numero_pedido} confirmado y registrado con éxito! El inventario ha sido actualizado.")
                ->with('pedido_creado', $pedido->numero_pedido);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
