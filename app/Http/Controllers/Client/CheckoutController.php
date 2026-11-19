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
    public function index(): View|RedirectResponse
    {
        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('catalogo.index')
                ->with('error', 'Tu carrito está vacío.');
        }

        $direcciones = auth()->user()->direcciones;
        return view('client.finalizar_compra', compact('carrito', 'direcciones'));
    }

    public function procesar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'id_direccion'      => ['nullable', 'exists:direccion_usuario,id_direccion'],
            'direccion_entrega' => ['required', 'string', 'max:255'],
            'ciudad_entrega'    => ['required', 'string', 'max:100'],
            'telefono_contacto' => ['required', 'string', 'max:20'],
            'metodo_pago'       => ['required', 'in:Contraentrega,Transferencia,PSE'],
        ]);

        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('catalogo.index')->with('error', 'El carrito está vacío.');
        }

        return DB::transaction(function () use ($datos, $carrito) {
            $subtotal = collect($carrito)->sum(fn ($item) => $item['precio'] * $item['cantidad']);
            $costoEnvio = $subtotal >= 122900 ? 0.00 : 12000.00;
            $total = $subtotal + $costoEnvio;

            $pedido = Pedido::create([
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
            ]);

            $pedido->update([
                'numero_pedido' => 'PED-' . (1000 + $pedido->id_pedido),
            ]);

            foreach ($carrito as $item) {
                $producto = Producto::lockForUpdate()->findOrFail($item['id_producto']);

                if ($producto->stock < $item['cantidad']) {
                    throw new \RuntimeException("Stock insuficiente para {$producto->nombre_producto}.");
                }

                PedidoDetalle::create([
                    'id_pedido'       => $pedido->id_pedido,
                    'id_producto'     => $producto->id_producto,
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal'        => $producto->precio * $item['cantidad'],
                ]);

                $producto->decrement('stock', $item['cantidad']);

                MovimientoInventario::create([
                    'id_producto'     => $producto->id_producto,
                    'tipo_movimiento' => 'SALIDA',
                    'cantidad'        => $item['cantidad'],
                    'observacion'     => "Venta en pedido {$pedido->numero_pedido}",
                ]);
            }

            session()->forget('carrito');

            return redirect()->route('cliente.pedidos.index')
                ->with('success', "¡Pedido {$pedido->numero_pedido} confirmado con éxito!");
        });
    }
}
