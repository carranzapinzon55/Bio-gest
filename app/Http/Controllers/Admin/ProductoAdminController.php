<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductoAdminController extends Controller
{
    /**
     * Listado administrativo de productos con filtro opcional.
     */
    public function index(Request $request): View
    {
        $query = Producto::with('categoria')->orderBy('id_producto');

        if ($request->filled('q')) {
            $busqueda = $request->q;
            $query->where('nombre_producto', 'like', "%{$busqueda}%");
        }

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $productos  = $query->get();
        $categorias = Categoria::orderBy('nombre_categoria')->get();

        return view('admin.productos.index', compact('productos', 'categorias'));
    }

    /**
     * Formulario para crear un nuevo producto.
     */
    public function create(): View
    {
        $categorias = Categoria::orderBy('nombre_categoria')->get();
        return view('admin.productos.create', compact('categorias'));
    }

    /**
     * Guardar un nuevo producto en la base de datos con validaciones estrictas.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $datos = $request->validate([
            'id_categoria'               => ['required', 'integer', 'exists:categoria,id_categoria'],
            'nombre_producto'            => ['required', 'string', 'min:3', 'max:150'],
            'descripcion'                => ['nullable', 'string', 'max:2000'],
            'beneficios'                 => ['nullable', 'string', 'max:2000'],
            'instrucciones_conservacion' => ['nullable', 'string', 'max:1000'],
            'especies_recomendadas'      => ['nullable', 'string', 'max:255'],
            'politica_reembolso'         => ['nullable', 'string', 'max:1000'],
            'precio'                     => ['required', 'numeric', 'gt:0'],
            'stock'                      => ['required', 'integer', 'gte:0'],
            'stock_minimo'               => ['nullable', 'integer', 'gte:0'],
            'imagen'                     => ['nullable'],
            'estado'                     => ['required', 'in:Activo,Inactivo'],
        ], [
            'id_categoria.required'    => 'La categoría del producto es obligatoria.',
            'id_categoria.exists'      => 'La categoría seleccionada no es válida.',
            'nombre_producto.required' => 'El nombre del producto es obligatorio.',
            'precio.required'          => 'El precio es obligatorio.',
            'precio.gt'                => 'El precio debe ser estrictamente mayor a 0.',
            'stock.required'           => 'El stock inicial es obligatorio.',
            'stock.gte'                => 'El stock no puede ser negativo.',
            'estado.in'                => 'El estado debe ser Activo o Inactivo.',
        ]);

        $datos['stock_minimo'] = $datos['stock_minimo'] ?? 10;

        if ($request->hasFile('imagen')) {
            $request->validate([
                'imagen' => ['file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            ], [
                'imagen.image' => 'El archivo seleccionado debe ser una imagen válida.',
                'imagen.mimes' => 'Formatos permitidos: PNG, JPG, JPEG o WEBP.',
                'imagen.max'   => 'El tamaño máximo de la imagen es 5MB.',
            ]);
            $datos['imagen'] = $this->storeProductImage($request->file('imagen'));
        } elseif ($request->hasFile('imagen_archivo')) {
            $datos['imagen'] = $this->storeProductImage($request->file('imagen_archivo'));
        } elseif (empty($datos['imagen']) || !is_string($datos['imagen'])) {
            $datos['imagen'] = 'Tenebrios_img.png';
        }

        $producto = DB::transaction(function () use ($datos) {
            $nuevoProducto = Producto::create($datos);

            if ($nuevoProducto->stock > 0) {
                MovimientoInventario::create([
                    'id_producto'      => $nuevoProducto->id_producto,
                    'tipo_movimiento'  => 'ENTRADA',
                    'cantidad'         => $nuevoProducto->stock,
                    'observacion'      => 'Inventario inicial al registrar producto en panel administrativo',
                    'fecha_movimiento' => now(),
                ]);
            }

            return $nuevoProducto;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto registrado exitosamente.',
                'data' => $producto->load('categoria'),
            ], 201);
        }

        return redirect()->route('admin.productos.index')
            ->with('success', "Producto '{$producto->nombre_producto}' registrado exitosamente.");
    }

    /**
     * Formulario de edición de producto.
     */
    public function edit(int $producto): View
    {
        $productoModel = Producto::with('categoria')->findOrFail($producto);
        $categorias    = Categoria::orderBy('nombre_categoria')->get();

        return view('admin.productos.edit', [
            'producto'   => $productoModel,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Actualizar producto en la base de datos registrando movimiento de inventario si cambia el stock.
     */
    public function update(Request $request, int $producto): RedirectResponse|JsonResponse
    {
        $productoModel = Producto::findOrFail($producto);

        $datos = $request->validate([
            'id_categoria'               => ['required', 'integer', 'exists:categoria,id_categoria'],
            'nombre_producto'            => ['required', 'string', 'min:3', 'max:150'],
            'descripcion'                => ['nullable', 'string', 'max:2000'],
            'beneficios'                 => ['nullable', 'string', 'max:2000'],
            'instrucciones_conservacion' => ['nullable', 'string', 'max:1000'],
            'especies_recomendadas'      => ['nullable', 'string', 'max:255'],
            'politica_reembolso'         => ['nullable', 'string', 'max:1000'],
            'precio'                     => ['required', 'numeric', 'gt:0'],
            'stock'                      => ['required', 'integer', 'gte:0'],
            'stock_minimo'               => ['nullable', 'integer', 'gte:0'],
            'imagen'                     => ['nullable'],
            'estado'                     => ['required', 'in:Activo,Inactivo'],
        ], [
            'precio.gt'  => 'El precio debe ser estrictamente mayor a 0.',
            'stock.gte'  => 'El stock debe ser mayor o igual a 0.',
        ]);

        $datos['stock_minimo'] = $datos['stock_minimo'] ?? $productoModel->stock_minimo ?? 10;

        if ($request->hasFile('imagen')) {
            $request->validate([
                'imagen' => ['file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            ], [
                'imagen.image' => 'El archivo seleccionado debe ser una imagen válida.',
                'imagen.mimes' => 'Formatos permitidos: PNG, JPG, JPEG o WEBP.',
                'imagen.max'   => 'El tamaño máximo de la imagen es 5MB.',
            ]);
            $datos['imagen'] = $this->storeProductImage($request->file('imagen'));
        } elseif ($request->hasFile('imagen_archivo')) {
            $datos['imagen'] = $this->storeProductImage($request->file('imagen_archivo'));
        } else {
            // Mantener imagen anterior si no se subió una nueva
            unset($datos['imagen']);
        }

        DB::transaction(function () use ($productoModel, $datos) {
            $stockAnterior = (int) $productoModel->stock;
            $stockNuevo    = (int) $datos['stock'];
            $conteoAntes   = MovimientoInventario::where('id_producto', $productoModel->id_producto)->count();

            $productoModel->update($datos);

            // Si el stock cambió y la BD no tiene activo el trigger automático, registrar el movimiento
            if ($stockNuevo !== $stockAnterior) {
                $conteoDespues = MovimientoInventario::where('id_producto', $productoModel->id_producto)->count();
                if ($conteoDespues === $conteoAntes) {
                    $tipo = $stockNuevo > $stockAnterior ? 'ENTRADA' : 'SALIDA';
                    $diferencia = abs($stockNuevo - $stockAnterior);
                    MovimientoInventario::create([
                        'id_producto'      => $productoModel->id_producto,
                        'tipo_movimiento'  => $tipo,
                        'cantidad'         => $diferencia,
                        'observacion'      => "Actualización de ficha de producto (Stock anterior: {$stockAnterior}, Nuevo: {$stockNuevo})",
                        'fecha_movimiento' => now(),
                    ]);
                }
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado correctamente.',
                'data' => $productoModel->fresh()->load('categoria'),
            ]);
        }

        return redirect()->route('admin.productos.index')
            ->with('success', "Producto '{$productoModel->nombre_producto}' actualizado correctamente.");
    }

    /**
     * Eliminar o desactivar/activar lógicamente un producto.
     */
    public function destroy(int $producto): RedirectResponse
    {
        $productoModel = Producto::findOrFail($producto);
        $nuevoEstado = $productoModel->estado === 'Activo' ? 'Inactivo' : 'Activo';
        $productoModel->update(['estado' => $nuevoEstado]);

        $accion = $nuevoEstado === 'Inactivo' ? 'desactivado' : 'reactivado';

        return redirect()->route('admin.productos.index')
            ->with('success', "El producto '{$productoModel->nombre_producto}' ha sido {$accion} correctamente.");
    }

    /**
     * Ajustar stock rápidamente desde listado de productos o inventario.
     */
    public function ajustarStock(Request $request, int $id_producto): RedirectResponse
    {
        $datos = $request->validate([
            'tipo_movimiento' => ['required', 'in:ENTRADA,SALIDA,AJUSTE'],
            'cantidad'        => ['required', 'integer', 'min:1'],
            'observacion'     => ['nullable', 'string', 'max:255'],
        ], [
            'cantidad.min' => 'La cantidad del movimiento debe ser al menos 1.',
        ]);

        $producto = Producto::findOrFail($id_producto);

        if ($datos['tipo_movimiento'] === 'SALIDA' && $producto->stock < $datos['cantidad']) {
            return back()->with('error', "Stock insuficiente para retirar {$datos['cantidad']} unidades de '{$producto->nombre_producto}'. Stock disponible: {$producto->stock}.");
        }

        DB::transaction(function () use ($producto, $datos) {
            $stockAnterior = (int) $producto->stock;
            $conteoAntes   = MovimientoInventario::where('id_producto', $producto->id_producto)->count();

            if ($datos['tipo_movimiento'] === 'ENTRADA') {
                $producto->increment('stock', $datos['cantidad']);
            } elseif ($datos['tipo_movimiento'] === 'SALIDA') {
                $producto->decrement('stock', $datos['cantidad']);
            } else {
                $producto->update(['stock' => $datos['cantidad']]);
            }

            $producto->refresh();
            $conteoDespues = MovimientoInventario::where('id_producto', $producto->id_producto)->count();

            if ($conteoDespues === $conteoAntes) {
                MovimientoInventario::create([
                    'id_producto'      => $producto->id_producto,
                    'tipo_movimiento'  => $datos['tipo_movimiento'],
                    'cantidad'         => $datos['cantidad'],
                    'observacion'      => $datos['observacion'] ?? "Movimiento manual ({$datos['tipo_movimiento']}) desde Panel Admin. Stock previo: {$stockAnterior}, nuevo: {$producto->stock}",
                    'fecha_movimiento' => now(),
                ]);
            } elseif (!empty($datos['observacion'])) {
                $ultimoMovimiento = MovimientoInventario::where('id_producto', $producto->id_producto)
                    ->orderByDesc('id_movimiento')
                    ->first();
                if ($ultimoMovimiento) {
                    $ultimoMovimiento->update(['observacion' => $datos['observacion']]);
                }
            }
        });

        return back()->with('success', "Movimiento de {$datos['tipo_movimiento']} aplicado. Nuevo stock de '{$producto->nombre_producto}': {$producto->fresh()->stock} unidades.");
    }

    private function storeProductImage(UploadedFile $image): string
    {
        $directory = public_path('assets/img/productos');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('No fue posible preparar el directorio de imágenes de productos.');
        }

        $filename = Str::uuid() . '.' . $image->extension();
        $image->move($directory, $filename);

        return 'productos/' . $filename;
    }
}
