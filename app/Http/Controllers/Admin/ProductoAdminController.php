<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoAdminController extends Controller
{
    public function index(): View
    {
        $productos  = Producto::with('categoria')->orderBy('id_producto')->get();
        $categorias = Categoria::all();

        return view('admin.productos.index', compact('productos', 'categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'id_categoria'               => ['required', 'exists:categoria,id_categoria'],
            'nombre_producto'            => ['required', 'string', 'max:150'],
            'descripcion'                => ['nullable', 'string'],
            'beneficios'                 => ['nullable', 'string'],
            'instrucciones_conservacion' => ['nullable', 'string'],
            'especies_recomendadas'      => ['nullable', 'string', 'max:255'],
            'politica_reembolso'         => ['nullable', 'string'],
            'precio'                     => ['required', 'numeric', 'min:0'],
            'stock'                      => ['required', 'integer', 'min:0'],
            'estado'                     => ['required', 'in:Activo,Inactivo'],
        ]);

        $producto = Producto::create($datos);

        if ($producto->stock > 0) {
            MovimientoInventario::create([
                'id_producto'     => $producto->id_producto,
                'tipo_movimiento' => 'ENTRADA',
                'cantidad'        => $producto->stock,
                'observacion'     => 'Creación inicial de producto',
            ]);
        }

        return back()->with('success', 'Producto registrado correctamente.');
    }

    public function ajustarStock(Request $request, int $id_producto): RedirectResponse
    {
        $datos = $request->validate([
            'tipo_movimiento' => ['required', 'in:ENTRADA,SALIDA,AJUSTE'],
            'cantidad'        => ['required', 'integer', 'min:1'],
            'observacion'     => ['nullable', 'string', 'max:255'],
        ]);

        $producto = Producto::findOrFail($id_producto);

        if ($datos['tipo_movimiento'] === 'ENTRADA') {
            $producto->increment('stock', $datos['cantidad']);
        } elseif ($datos['tipo_movimiento'] === 'SALIDA') {
            $producto->decrement('stock', min($producto->stock, $datos['cantidad']));
        } else {
            $producto->update(['stock' => $datos['cantidad']]);
        }

        MovimientoInventario::create([
            'id_producto'     => $producto->id_producto,
            'tipo_movimiento' => $datos['tipo_movimiento'],
            'cantidad'        => $datos['cantidad'],
            'observacion'     => $datos['observacion'] ?? 'Ajuste manual en panel administrativo',
        ]);

        return back()->with('success', "Stock de {$producto->nombre_producto} actualizado.");
    }
}
