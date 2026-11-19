<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarritoController extends Controller
{
    public function index(): View
    {
        $carrito = session('carrito', []);
        return view('client.carrito', compact('carrito'));
    }

    public function agregar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'id_producto' => ['required', 'integer', 'exists:producto,id_producto'],
            'cantidad'    => ['required', 'integer', 'min:1'],
        ]);

        $producto = Producto::activos()->findOrFail($datos['id_producto']);
        $carrito  = session('carrito', []);

        $id = $producto->id_producto;
        $cantidadNueva = ($carrito[$id]['cantidad'] ?? 0) + $datos['cantidad'];

        if ($cantidadNueva > $producto->stock) {
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

        return redirect()->route('carrito.index')
            ->with('success', "{$producto->nombre_producto} agregado al carrito.");
    }

    public function actualizar(Request $request, int $id_producto): RedirectResponse
    {
        $datos = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $carrito = session('carrito', []);
        if (isset($carrito[$id_producto])) {
            $producto = Producto::findOrFail($id_producto);
            if ($datos['cantidad'] > $producto->stock) {
                return back()->with('error', "Solo hay {$producto->stock} unidades disponibles.");
            }
            $carrito[$id_producto]['cantidad'] = $datos['cantidad'];
            session(['carrito' => $carrito]);
        }

        return back()->with('success', 'Cantidad actualizada.');
    }

    public function eliminar(int $id_producto): RedirectResponse
    {
        $carrito = session('carrito', []);
        unset($carrito[$id_producto]);
        session(['carrito' => $carrito]);

        return back()->with('success', 'Producto eliminado del carrito.');
    }
}
