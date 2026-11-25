<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Pantalla de checkout para finalizar compra.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('catalogo.index')
                ->with('error', 'Tu carrito de compras está vacío.');
        }

        $direcciones = auth()->user()->direcciones;
        $subtotal = collect($carrito)->sum(fn ($item) => $item['precio'] * $item['cantidad']);

        // Promociones activas vigentes
        $promocionesActivas = Promocion::where('estado', 'Activo')
            ->where('fecha_inicio', '<=', now()->addDay())
            ->where('fecha_fin', '>=', now()->subDay())
            ->orderByDesc('descuento_porcentaje')
            ->get();

        // Verificar si hay un cupón aplicado en sesión o por parámetro
        if ($request->filled('cupon')) {
            $codigoInput = strtoupper(trim($request->input('cupon')));
            $promo = Promocion::where('codigo_cupon', $codigoInput)
                ->where('estado', 'Activo')
                ->where('fecha_fin', '>=', now()->subDay())
                ->first();
            if ($promo) {
                session(['cupon_aplicado' => [
                    'codigo' => $promo->codigo_cupon,
                    'porcentaje' => (float) $promo->descuento_porcentaje,
                ]]);
            } else {
                session()->forget('cupon_aplicado');
            }
        } elseif ($request->has('quitar_cupon')) {
            session()->forget('cupon_aplicado');
        }

        $cuponAplicado = session('cupon_aplicado');
        $descuentoMonto = 0.00;
        if ($cuponAplicado && !empty($cuponAplicado['porcentaje'])) {
            $descuentoMonto = round($subtotal * ($cuponAplicado['porcentaje'] / 100), 2);
        }

        $subtotalConDescuento = max(0, $subtotal - $descuentoMonto);

        $montoEnvioGratis = (float) Configuracion::get('monto_envio_gratis', 122900);
        $costoBogota = (float) Configuracion::get('costo_envio_bogota', 10000);
        $costoNacional = (float) Configuracion::get('costo_envio_nacional', 18000);

        $ciudadPrincipal = optional($direcciones->first())->ciudad ?? 'Bogotá';
        $esBogota = (stripos($ciudadPrincipal, 'bogot') !== false);
        $tarifaBase = $esBogota ? $costoBogota : $costoNacional;

        $costoEnvio = $subtotalConDescuento >= $montoEnvioGratis ? 0.00 : $tarifaBase;
        $total = $subtotalConDescuento + $costoEnvio;

        return view('client.finalizar_compra', compact(
            'carrito',
            'direcciones',
            'subtotal',
            'descuentoMonto',
            'cuponAplicado',
            'promocionesActivas',
            'costoEnvio',
            'total'
        ));
    }

    /**
     * Procesar pedido del cliente en transacción atómica.
     */
    public function procesar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'id_direccion'      => ['nullable', 'exists:direccion_usuario,id_direccion'],
            'direccion_entrega' => ['required', 'string', 'min:5', 'max:255', 'regex:/[a-zA-ZÁÉÍÓÚÜÑáéíóúüñ]/u'],
            'ciudad_entrega'    => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\.\'-]+$/u'],
            'telefono_contacto' => ['required', 'string', 'regex:/^[0-9]{7,10}$/'],
            'metodo_pago'       => ['required', 'in:Contraentrega,Transferencia,Transferencia Bancaria,PSE,Tarjeta'],
            'codigo_cupon'      => ['nullable', 'string', 'max:50'],
        ], [
            'direccion_entrega.required' => 'La dirección de entrega es obligatoria.',
            'direccion_entrega.min'      => 'Ingresa una dirección de entrega válida y completa.',
            'direccion_entrega.regex'    => 'La dirección de entrega debe incluir texto válido (ej. Calle, Carrera), no solo números.',
            'ciudad_entrega.required'    => 'La ciudad de entrega es obligatoria.',
            'ciudad_entrega.regex'       => 'La ciudad solo puede contener letras y espacios (no se permiten números).',
            'telefono_contacto.required' => 'El teléfono de contacto es obligatorio.',
            'telefono_contacto.regex'    => 'El teléfono de contacto debe contener únicamente números (entre 7 y 10 dígitos).',
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

                // Evaluar cupón de descuento si viene en el formulario o en sesión
                $porcentajeDescuento = 0.0;
                $codigoCupon = !empty($datos['codigo_cupon'])
                    ? strtoupper(trim($datos['codigo_cupon']))
                    : (session('cupon_aplicado.codigo') ?? null);

                if ($codigoCupon) {
                    $promo = Promocion::where('codigo_cupon', $codigoCupon)
                        ->where('estado', 'Activo')
                        ->where('fecha_fin', '>=', now()->subDay())
                        ->first();
                    if ($promo) {
                        $porcentajeDescuento = (float) $promo->descuento_porcentaje;
                    }
                }

                $descuentoMonto = $porcentajeDescuento > 0
                    ? round($subtotal * ($porcentajeDescuento / 100), 2)
                    : 0.00;
                $subtotalConDescuento = max(0, $subtotal - $descuentoMonto);

                $montoEnvioGratis = (float) Configuracion::get('monto_envio_gratis', 122900);
                $costoBogota = (float) Configuracion::get('costo_envio_bogota', 10000);
                $costoNacional = (float) Configuracion::get('costo_envio_nacional', 18000);

                $esBogota = (stripos($datos['ciudad_entrega'], 'bogot') !== false);
                $tarifaEnvio = $esBogota ? $costoBogota : $costoNacional;

                $costoEnvio = $subtotalConDescuento >= $montoEnvioGratis ? 0.00 : $tarifaEnvio;
                $total = $subtotalConDescuento + $costoEnvio;

                $nuevoPedido = Pedido::create([
                    'id_usuario'        => auth()->id(),
                    'id_direccion'      => $datos['id_direccion'] ?? null,
                    'subtotal'          => $subtotalConDescuento,
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

                    $producto->decrement('stock', $cantidadCompra);
                    $producto->refresh();

                    $conteoMovimientosDespues = MovimientoInventario::where('id_producto', $producto->id_producto)->count();
                    $observacionVenta = "Salida por venta en tienda - Pedido {$numeroPedido} (Stock previo: {$stockAnterior}, nuevo: {$producto->stock})";

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

                session()->forget(['carrito', 'cupon_aplicado']);
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
