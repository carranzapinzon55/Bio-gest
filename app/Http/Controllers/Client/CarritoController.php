<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarritoController extends Controller
{
    /**
     * Vista del Carrito de Compras con promociones, cupón comercial y cálculo dinámico de envío.
     */
    public function index(Request $request): View
    {
        $carrito = session('carrito', []);

        // Permitir aplicar o quitar cupón vía GET (parámetros de URL)
        if ($request->filled('cupon')) {
            $this->procesarAplicarCupon(strtoupper(trim($request->input('cupon'))));
        } elseif ($request->has('quitar_cupon')) {
            session()->forget('cupon_aplicado');
        }

        $subtotal = collect($carrito)->sum(fn ($item) => $item['precio'] * $item['cantidad']);

        // Verificar cupón en sesión y validar regla de un solo uso por usuario
        $cuponAplicado = session('cupon_aplicado');
        $descuentoMonto = 0.00;

        if ($cuponAplicado) {
            $promo = Promocion::where('codigo_cupon', $cuponAplicado['codigo'] ?? '')
                ->where('estado', 'Activo')
                ->where('fecha_fin', '>=', now()->subDay())
                ->first();

            if (!$promo) {
                session()->forget('cupon_aplicado');
                $cuponAplicado = null;
            } elseif (auth()->check() && $promo->haSidoUsadaPor(auth()->id())) {
                session()->forget('cupon_aplicado');
                $cuponAplicado = null;
                session()->flash('error', "La promoción '{$promo->nombre_comercial}' ya fue utilizada por tu cuenta. Solo es válida una única vez.");
            } else {
                // Actualizar nombre comercial y porcentaje
                $cuponAplicado['nombre'] = $promo->nombre_comercial;
                $cuponAplicado['porcentaje'] = (float) $promo->descuento_porcentaje;
                session(['cupon_aplicado' => $cuponAplicado]);

                $descuentoMonto = round($subtotal * ($cuponAplicado['porcentaje'] / 100), 2);
            }
        }

        $subtotalConDescuento = max(0, $subtotal - $descuentoMonto);

        // Envío dinámico desde configuracion
        $montoEnvioGratis = (float) Configuracion::get('monto_envio_gratis', 122900);
        $costoBogota = (float) Configuracion::get('costo_envio_bogota', 10000);
        $costoEnvio = ($subtotalConDescuento >= $montoEnvioGratis || empty($carrito)) ? 0.00 : $costoBogota;
        $total = $subtotalConDescuento + $costoEnvio;

        // Promociones activas disponibles para el usuario
        $promocionesDisponibles = Promocion::where('estado', 'Activo')
            ->where('fecha_inicio', '<=', now()->addDay())
            ->where('fecha_fin', '>=', now()->subDay())
            ->orderByDesc('descuento_porcentaje')
            ->get()
            ->filter(function ($promo) {
                return !auth()->check() || !$promo->haSidoUsadaPor(auth()->id());
            })
            ->values();

        return view('client.carrito', compact(
            'carrito',
            'subtotal',
            'descuentoMonto',
            'subtotalConDescuento',
            'cuponAplicado',
            'costoEnvio',
            'montoEnvioGratis',
            'total',
            'promocionesDisponibles'
        ));
    }

    /**
     * Agrega un producto al carrito verificando stock y restringiendo compras a Administradores.
     */
    public function agregar(Request $request): RedirectResponse|JsonResponse
    {
        if (auth()->check() && (int) auth()->user()->id_rol === 1) {
            $msg = 'Las cuentas con rol Administrador no pueden realizar compras para la propia empresa.';
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => false,
                    'message'  => $msg,
                    'redirect' => route('admin.inicio'),
                ], 403);
            }
            return redirect()->route('admin.inicio')->with('error', $msg);
        }

        $datos = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:producto,id_producto'],
            'cantidad'    => ['required', 'integer', 'min:1'],
        ]);

        $producto = Producto::activos()->findOrFail($datos['id_producto']);
        $carrito  = session('carrito', []);

        $id = $producto->id_producto;
        $cantidadNueva = ($carrito[$id]['cantidad'] ?? 0) + $datos['cantidad'];

        if ($cantidadNueva > $producto->stock) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock disponible insuficiente ({$producto->stock} unidades)."
                ], 422);
            }
            return back()->with('error', "Stock disponible insuficiente ({$producto->stock} unidades).");
        }

        $carrito[$id] = [
            'id_producto'     => $producto->id_producto,
            'nombre_producto' => $producto->nombre_producto,
            'precio'          => (float) $producto->precio,
            'imagen'          => $producto->imagen,
            'cantidad'        => $cantidadNueva,
        ];

        session(['carrito' => $carrito]);

        $totalCantidad = collect($carrito)->sum('cantidad');

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "¡{$producto->nombre_producto} se agregó al carrito!",
                'cart_count' => $totalCantidad,
            ]);
        }

        return redirect()->route('carrito.index')
            ->with('success', "{$producto->nombre_producto} agregado al carrito.");
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     */
    public function actualizar(Request $request, int $id_producto): RedirectResponse
    {
        $datos = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $carrito = session('carrito', []);
        if (isset($carrito[$id_producto])) {
            $producto = Producto::findOrFail($id_producto);
            if ($datos['cantidad'] > $producto->stock) {
                return back()->with('error', "Solo hay {$producto->stock} unidades disponibles de '{$producto->nombre_producto}'.");
            }
            $carrito[$id_producto]['cantidad'] = $datos['cantidad'];
            session(['carrito' => $carrito]);
        }

        return back()->with('success', 'Cantidad actualizada en el carrito.');
    }

    /**
     * Elimina un producto del carrito.
     */
    public function eliminar(int $id_producto): RedirectResponse
    {
        $carrito = session('carrito', []);
        unset($carrito[$id_producto]);
        session(['carrito' => $carrito]);

        if (empty($carrito)) {
            session()->forget('cupon_aplicado');
        }

        return back()->with('success', 'Producto retirado del carrito.');
    }

    /**
     * Aplica un cupón validando que sea de UN SOLO USO por usuario.
     */
    public function aplicarCupon(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'codigo_cupon' => ['required', 'string', 'max:50'],
        ], [
            'codigo_cupon.required' => 'Ingresa el código del cupón.',
        ]);

        $codigo = strtoupper(trim($datos['codigo_cupon']));
        $resultado = $this->procesarAplicarCupon($codigo);

        if ($resultado['error']) {
            return back()->with('error', $resultado['mensaje']);
        }

        return back()->with('success', $resultado['mensaje']);
    }

    /**
     * Quita el cupón actualmente aplicado en sesión.
     */
    public function quitarCupon(): RedirectResponse
    {
        session()->forget('cupon_aplicado');
        return back()->with('success', 'Cupón de descuento retirado.');
    }

    /**
     * Lógica compartida para procesar y validar un cupón.
     */
    private function procesarAplicarCupon(string $codigo): array
    {
        $promo = Promocion::where('codigo_cupon', $codigo)
            ->where('estado', 'Activo')
            ->first();

        if (!$promo) {
            session()->forget('cupon_aplicado');
            return ['error' => true, 'mensaje' => "El cupón '{$codigo}' no existe o se encuentra inactivo."];
        }

        if ($promo->fecha_fin && $promo->fecha_fin->isPast()) {
            session()->forget('cupon_aplicado');
            return ['error' => true, 'mensaje' => "La promoción '{$promo->nombre_comercial}' ha vencido."];
        }

        if (auth()->check() && $promo->haSidoUsadaPor(auth()->id())) {
            session()->forget('cupon_aplicado');
            return [
                'error' => true,
                'mensaje' => "Ya has utilizado la promoción '{$promo->nombre_comercial}' en un pedido anterior. Las promociones son de uso único por cliente."
            ];
        }

        session(['cupon_aplicado' => [
            'id_promocion' => $promo->id_promocion,
            'codigo'       => $promo->codigo_cupon,
            'nombre'       => $promo->nombre_comercial,
            'porcentaje'   => (float) $promo->descuento_porcentaje,
        ]]);

        return [
            'error'   => false,
            'mensaje' => "¡Promoción '{$promo->nombre_comercial}' aplicada con éxito! Descuento del " . (int)$promo->descuento_porcentaje . "%."
        ];
    }
}
