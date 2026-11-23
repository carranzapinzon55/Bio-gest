<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
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
     * Procesar pedido en transacción con validación y descuento de stock.
     */
    public function procesar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'id_direccion'      => ['nullable', 'exists:direccion_usuario,id_direccion'],
            'direccion_entrega' => ['required', 'string', 'max:255'],
            'ciudad_entrega'    => ['required', 'string', 'max:100'],
            'telefono_contacto' => ['required', 'string', 'max:20'],
            'metodo_pago'       => ['required', 'in:Contraentrega,Transferencia,PSE,Tarjeta'],
        ]);

        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('catalogo.index')->with('error', 'El carrito está vacío.');
        }

        try {
            $pedido = DB::transaction(function () use ($datos, $carrito) {
                $subtotal = collect($carrito)->sum(fn ($item) => $item['precio'] * $item['cantidad']);
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

                $nuevoPedido->update([
                    'numero_pedido' => 'PED-' . (1000 + $nuevoPedido->id_pedido),
                ]);

                foreach ($carrito as $item) {
                    $producto = Producto::lockForUpdate()->findOrFail($item['id_producto']);

                    if ($producto->stock < $item['cantidad']) {
                        throw new \RuntimeException("Stock insuficiente para '{$producto->nombre_producto}'. Stock disponible: {$producto->stock}.");
                    }

                    PedidoDetalle::create([
                        'id_pedido'       => $nuevoPedido->id_pedido,
                        'id_producto'     => $producto->id_producto,
                        'cantidad'        => $item['cantidad'],
                        'precio_unitario' => $producto->precio,
                        'subtotal'        => $producto->precio * $item['cantidad'],
                    ]);

                    // Descontar inventario (el trigger trg_auditoria_stock_producto registra la SALIDA)
                    $producto->decrement('stock', $item['cantidad']);
                }

                session()->forget('carrito');

                return $nuevoPedido;
            });

            return redirect()->route('cliente.pedidos.index')
                ->with('success', "¡Pedido {$pedido->numero_pedido} confirmado y registrado con éxito!");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
