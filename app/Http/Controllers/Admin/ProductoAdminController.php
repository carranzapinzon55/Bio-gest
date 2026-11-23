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
    /**
     * Listado administrativo de productos.
     */
    public function index(): View
    {
        $productos  = Producto::with('categoria')->orderBy('id_producto')->get();
        $categorias = Categoria::all();

        return view('admin.productos.index', compact('productos', 'categorias'));
    }

    /**
     * Formulario para crear un nuevo producto.
     */
    public function create(): View
    {
        $categorias = Categoria::all();
        return view('admin.productos.create', compact('categorias'));
    }

    /**
     * Guardar un nuevo producto en la base de datos.
     * Respeta la restricción CHECK: precio > 0 y stock >= 0.
     */
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
            'precio'                     => ['required', 'numeric', 'gt:0'], // CHECK precio > 0
            'stock'                      => ['required', 'integer', 'gte:0'], // CHECK stock >= 0
            'stock_minimo'               => ['nullable', 'integer', 'gte:0'],
            'imagen'                     => ['nullable', 'string', 'max:255'],
            'estado'                     => ['required', 'in:Activo,Inactivo'],
        ], [
            'precio.gt'  => 'El precio debe ser estrictamente mayor a 0.',
            'stock.gte' => 'El stock debe ser mayor o igual a 0.',
        ]);

        $datos['stock_minimo'] = $datos['stock_minimo'] ?? 10;
        $producto = Producto::create($datos);

        // Movimiento inicial si ingresa con existencias
        if ($producto->stock > 0) {
            MovimientoInventario::create([
                'id_producto'     => $producto->id_producto,
                'tipo_movimiento' => 'ENTRADA',
                'cantidad'        => $producto->stock,
                'observacion'     => 'Inventario inicial al crear producto (Panel Admin)',
                'fecha_movimiento'=> now(),
            ]);
        }

        return redirect()->route('admin.productos.index')
            ->with('success', "Producto '{$producto->nombre_producto}' registrado con éxito.");
    }

    /**
     * Formulario de edición de producto.
     */
    public function edit(int $producto): View
    {
        $productoModel = Producto::with('categoria')->findOrFail($producto);
        $categorias    = Categoria::all();

        return view('admin.productos.edit', [
            'producto'   => $productoModel,
            'categorias' => $categorias
        ]);
    }

    /**
     * Actualizar producto en la base de datos.
     * Respeta las restricciones CHECK y el trigger trg_auditoria_stock_producto.
     */
    public function update(Request $request, int $producto): RedirectResponse
    {
        $productoModel = Producto::findOrFail($producto);

        $datos = $request->validate([
            'id_categoria'               => ['required', 'exists:categoria,id_categoria'],
            'nombre_producto'            => ['required', 'string', 'max:150'],
            'descripcion'                => ['nullable', 'string'],
            'beneficios'                 => ['nullable', 'string'],
            'instrucciones_conservacion' => ['nullable', 'string'],
            'especies_recomendadas'      => ['nullable', 'string', 'max:255'],
            'politica_reembolso'         => ['nullable', 'string'],
            'precio'                     => ['required', 'numeric', 'gt:0'], // CHECK precio > 0
            'stock'                      => ['required', 'integer', 'gte:0'], // CHECK stock >= 0
            'stock_minimo'               => ['nullable', 'integer', 'gte:0'],
            'imagen'                     => ['nullable', 'string', 'max:255'],
            'estado'                     => ['required', 'in:Activo,Inactivo'],
        ], [
            'precio.gt'  => 'El precio debe ser estrictamente mayor a 0.',
            'stock.gte' => 'El stock debe ser mayor o igual a 0.',
        ]);

        $productoModel->update($datos);

        return redirect()->route('admin.productos.index')
            ->with('success', "Producto '{$productoModel->nombre_producto}' actualizado correctamente.");
    }

    /**
     * Desactivación lógica de un producto.
     */
    public function destroy(int $producto): RedirectResponse
    {
        $productoModel = Producto::findOrFail($producto);
        $productoModel->update(['estado' => 'Inactivo']);

        return redirect()->route('admin.productos.index')
            ->with('success', "Producto '{$productoModel->nombre_producto}' desactivado (baja lógica).");
    }

    /**
     * Ajustar stock manualmente desde el panel de control.
     */
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
            if ($producto->stock < $datos['cantidad']) {
                return back()->with('error', "No se puede retirar {$datos['cantidad']} unidades. Stock actual: {$producto->stock}.");
            }
            $producto->decrement('stock', $datos['cantidad']);
        } else {
            $producto->update(['stock' => $datos['cantidad']]);
        }

        return back()->with('success', "Stock de {$producto->nombre_producto} actualizado.");
    }
}
