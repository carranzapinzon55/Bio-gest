<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\DireccionUsuario;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\PromocionUsuario;
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
        if (!auth()->check()) {
            return redirect()->guest(route('login'))->with('info', 'Por favor, inicia sesión para finalizar tu compra.');
        }

        // Restricción para Administradores: no pueden realizar compras para la propia empresa
        if ((int) auth()->user()->id_rol === 1) {
            return redirect()->route('admin.inicio')
                ->with('error', 'Las cuentas con rol Administrador no pueden realizar compras para la propia empresa.');
        }

        $carrito = session('carrito', []);
        if (empty($carrito)) {
            return redirect()->route('catalogo.index')
                ->with('error', 'Tu carrito de compras está vacío.');
        }

        $usuario = auth()->user();
        $direcciones = $usuario->direcciones;
        $subtotal = collect($carrito)->sum(fn ($item) => $item['precio'] * $item['cantidad']);

        // Promociones activas vigentes que el usuario NO haya usado aún
        $promocionesActivas = Promocion::where('estado', 'Activo')
            ->where('fecha_inicio', '<=', now()->addDay())
            ->where('fecha_fin', '>=', now()->subDay())
            ->orderByDesc('descuento_porcentaje')
            ->get()
            ->filter(fn ($promo) => !$promo->haSidoUsadaPor($usuario->id_usuario));

        // Procesar o verificar cupón de descuento en sesión / por query param
        if ($request->filled('cupon')) {
            $codigoInput = strtoupper(trim($request->input('cupon')));
            $promo = Promocion::where('codigo_cupon', $codigoInput)
                ->where('estado', 'Activo')
                ->where('fecha_fin', '>=', now()->subDay())
                ->first();

            if (!$promo) {
                session()->forget('cupon_aplicado');
                return redirect()->route('checkout.index')->with('error', 'El cupón ingresado no existe o ha expirado.');
            }

            if ($promo->haSidoUsadaPor($usuario->id_usuario)) {
                session()->forget('cupon_aplicado');
                return redirect()->route('checkout.index')->with('error', "Ya has utilizado la promoción '{$promo->nombre_comercial}'. Las promociones son de uso único por cliente.");
            }

            session(['cupon_aplicado' => [
                'id_promocion' => $promo->id_promocion,
                'codigo'       => $promo->codigo_cupon,
                'nombre'       => $promo->nombre_comercial,
                'porcentaje'   => (float) $promo->descuento_porcentaje,
            ]]);
        } elseif ($request->has('quitar_cupon')) {
            session()->forget('cupon_aplicado');
            return redirect()->route('checkout.index')->with('info', 'Cupón removido del pedido.');
        }

        // Validar cupón preexistente en sesión por si el usuario ya lo redimió en otra pestaña
        $cuponAplicado = session('cupon_aplicado');
        if ($cuponAplicado && !empty($cuponAplicado['id_promocion'])) {
            $promoExistente = Promocion::find($cuponAplicado['id_promocion']);
            if (!$promoExistente || $promoExistente->haSidoUsadaPor($usuario->id_usuario)) {
                session()->forget('cupon_aplicado');
                $cuponAplicado = null;
            } else {
                // Asegurar que el nombre comercial esté siempre presente y fresco
                $cuponAplicado['nombre'] = $promoExistente->nombre_comercial;
                session(['cupon_aplicado' => $cuponAplicado]);
            }
        }

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
        if (!auth()->check()) {
            return redirect()->guest(route('login'))->with('info', 'Por favor, inicia sesión para finalizar tu compra.');
        }

        // Restricción para Administradores: no pueden realizar compras para la propia empresa
        if ((int) auth()->user()->id_rol === 1) {
            return redirect()->route('admin.inicio')
                ->with('error', 'Las cuentas con rol Administrador no pueden realizar compras para la propia empresa.');
        }

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

        $usuario = auth()->user();

        try {
            $pedido = DB::transaction(function () use ($datos, $carrito, $usuario) {
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

                // Evaluar cupón de descuento y validar regla de USO ÚNICO POR USUARIO
                $promoValida = null;
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
                        if ($promo->haSidoUsadaPor($usuario->id_usuario)) {
                            throw new \RuntimeException("La promoción '{$promo->nombre_comercial}' ya fue utilizada previamente en tu cuenta. Cada cupón es de uso único por cliente.");
                        }
                        $promoValida = $promo;
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

                // GUARDADO AUTOMÁTICO DE DIRECCIÓN EN CHECKOUT
                $idDireccion = $datos['id_direccion'] ?? null;
                $direccionTexto = trim($datos['direccion_entrega']);
                $ciudadTexto = trim($datos['ciudad_entrega']);

                // Buscar si el cliente ya tiene registrada exactamente esta dirección y ciudad
                $direccionExistente = DireccionUsuario::where('id_usuario', $usuario->id_usuario)
                    ->whereRaw('LOWER(TRIM(direccion)) = ?', [mb_strtolower($direccionTexto)])
                    ->whereRaw('LOWER(TRIM(ciudad)) = ?', [mb_strtolower($ciudadTexto)])
                    ->first();

                if ($direccionExistente) {
                    $idDireccion = $direccionExistente->id_direccion;
                } else {
                    // Si no existe, crearla automáticamente. Si es su primera dirección, marcarla como principal
                    $totalDireccionesPrevias = DireccionUsuario::where('id_usuario', $usuario->id_usuario)->count();
                    $esPrimera = ($totalDireccionesPrevias === 0);

                    $nuevaDireccion = DireccionUsuario::create([
                        'id_usuario'       => $usuario->id_usuario,
                        'nombre_ubicacion' => $esPrimera ? 'Principal' : 'Entrega',
                        'direccion'        => $direccionTexto,
                        'ciudad'           => $ciudadTexto,
                        'departamento'     => $esBogota ? 'Cundinamarca / D.C.' : 'Colombia',
                        'codigo_postal'    => null,
                        'es_principal'     => $esPrimera,
                    ]);

                    $idDireccion = $nuevaDireccion->id_direccion;
                }

                // Crear Pedido registrando el nombre comercial de la promoción
                $nuevoPedido = Pedido::create([
                    'id_usuario'        => $usuario->id_usuario,
                    'id_direccion'      => $idDireccion,
                    'id_promocion'      => $promoValida?->id_promocion,
                    'nombre_promocion'  => $promoValida?->nombre_comercial,
                    'subtotal'          => $subtotal,
                    'descuento'         => $descuentoMonto,
                    'costo_envio'       => $costoEnvio,
                    'total'             => $total,
                    'metodo_pago'       => $datos['metodo_pago'],
                    'estado_pago'       => 'Pendiente',
                    'estado_pedido'     => 'Pendiente',
                    'direccion_entrega' => $direccionTexto,
                    'ciudad_entrega'    => $ciudadTexto,
                    'telefono_contacto' => $datos['telefono_contacto'],
                    'fecha_pedido'      => now(),
                ]);

                $numeroPedido = 'PED-' . (1000 + $nuevoPedido->id_pedido);
                $nuevoPedido->update([
                    'numero_pedido' => $numeroPedido,
                ]);

                // Registrar redención en promocion_usuario para blindar el uso único
                if ($promoValida) {
                    PromocionUsuario::create([
                        'id_promocion' => $promoValida->id_promocion,
                        'id_usuario'   => $usuario->id_usuario,
                        'id_pedido'    => $nuevoPedido->id_pedido,
                        'fecha_uso'    => now(),
                    ]);
                }

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
                if ($usuario->carrito) {
                    $usuario->carrito->detalles()->delete();
                }

                return $nuevoPedido;
            });

            return redirect()->route('cliente.pedidos.index')
                ->with('success', "¡Pedido #{$pedido->numero_pedido} confirmado y registrado con éxito! Tu dirección de entrega fue guardada y el inventario ha sido actualizado.")
                ->with('pedido_creado', $pedido->numero_pedido);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
