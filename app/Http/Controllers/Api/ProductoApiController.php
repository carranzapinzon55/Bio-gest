<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductoApiController extends Controller
{
    /**
     * Listar productos (Público).
     * GET /api/productos
     */
    public function index(Request $request)
    {
        $query = Producto::with('categoria');

        // Filtrar por estado (por defecto 'Activo' si no se especifica o si no es admin)
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        } else {
            $query->where('estado', 'Activo');
        }

        // Filtrar por categoría
        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        // Búsqueda por texto en nombre o descripción
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_producto', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        $productos = $query->orderBy('id_producto', 'asc')->get();

        // Enriquecer con la función de estado de stock fn_estado_stock
        $productosTransformados = $productos->map(function ($producto) {
            $estadoStock = DB::selectOne('SELECT fn_estado_stock(?) AS estado_stock', [$producto->id_producto]);
            $productoArray = $producto->toArray();
            $productoArray['estado_stock'] = $estadoStock ? $estadoStock->estado_stock : ($producto->stock > 0 ? 'DISPONIBLE' : 'AGOTADO');
            return $productoArray;
        });

        return response()->json([
            'success' => true,
            'total'   => $productosTransformados->count(),
            'data'    => $productosTransformados,
        ], 200);
    }

    /**
     * Obtener detalle de un producto (Público).
     * GET /api/productos/{id}
     */
    public function show($id)
    {
        $producto = Producto::with('categoria')->find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado.'
            ], 404);
        }

        $estadoStock = DB::selectOne('SELECT fn_estado_stock(?) AS estado_stock', [$producto->id_producto]);
        $productoArray = $producto->toArray();
        $productoArray['estado_stock'] = $estadoStock ? $estadoStock->estado_stock : ($producto->stock > 0 ? 'DISPONIBLE' : 'AGOTADO');

        return response()->json([
            'success' => true,
            'data'    => $productoArray,
        ], 200);
    }

    /**
     * Crear nuevo producto (Protegido: Administrador).
     * POST /api/productos
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_categoria'               => 'required|exists:categoria,id_categoria',
            'nombre_producto'            => 'required|string|max:150',
            'descripcion'                => 'nullable|string',
            'beneficios'                 => 'nullable|string',
            'instrucciones_conservacion' => 'nullable|string',
            'especies_recomendadas'      => 'nullable|string|max:255',
            'politica_reembolso'         => 'nullable|string',
            'precio'                     => 'required|numeric|gt:0', // Restricción CHECK: precio > 0
            'stock'                      => 'required|integer|gte:0', // Restricción CHECK: stock >= 0
            'stock_minimo'               => 'nullable|integer|gte:0',
            'imagen'                     => 'nullable|string|max:255',
            'estado'                     => 'nullable|in:Activo,Inactivo',
        ], [
            'precio.gt'  => 'El precio debe ser estrictamente mayor a 0.',
            'stock.gte' => 'El stock debe ser mayor o igual a 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['estado'] = $data['estado'] ?? 'Activo';
        $data['stock_minimo'] = $data['stock_minimo'] ?? 10;

        $producto = Producto::create($data);

        // Registro de movimiento inicial si tiene stock
        if ($producto->stock > 0) {
            MovimientoInventario::create([
                'id_producto'      => $producto->id_producto,
                'tipo_movimiento'  => 'ENTRADA',
                'cantidad'         => $producto->stock,
                'observacion'      => 'Inventario inicial al crear producto',
                'fecha_movimiento' => now(),
            ]);
        }

        $producto->load('categoria');

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente.',
            'data'    => $producto,
        ], 201);
    }

    /**
     * Actualizar producto existente (Protegido: Administrador).
     * PUT /api/productos/{id}
     * El trigger trg_auditoria_stock_producto auditará cualquier cambio de stock en movimiento_inventario.
     */
    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_categoria'               => 'sometimes|required|exists:categoria,id_categoria',
            'nombre_producto'            => 'sometimes|required|string|max:150',
            'descripcion'                => 'nullable|string',
            'beneficios'                 => 'nullable|string',
            'instrucciones_conservacion' => 'nullable|string',
            'especies_recomendadas'      => 'nullable|string|max:255',
            'politica_reembolso'         => 'nullable|string',
            'precio'                     => 'sometimes|required|numeric|gt:0', // Restricción CHECK: precio > 0
            'stock'                      => 'sometimes|required|integer|gte:0', // Restricción CHECK: stock >= 0
            'stock_minimo'               => 'nullable|integer|gte:0',
            'imagen'                     => 'nullable|string|max:255',
            'estado'                     => 'sometimes|required|in:Activo,Inactivo',
        ], [
            'precio.gt'  => 'El precio debe ser estrictamente mayor a 0.',
            'stock.gte' => 'El stock debe ser mayor o igual a 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $producto->update($validator->validated());
        $producto->load('categoria');

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente.',
            'data'    => $producto,
        ], 200);
    }

    /**
     * Desactivación lógica de un producto (Protegido: Administrador).
     * DELETE /api/productos/{id}
     */
    public function destroy($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado.'
            ], 404);
        }

        $producto->estado = 'Inactivo';
        $producto->save();

        return response()->json([
            'success' => true,
            'message' => "Producto '{$producto->nombre_producto}' desactivado exitosamente (baja lógica).",
            'data'    => [
                'id_producto' => $producto->id_producto,
                'estado'      => $producto->estado,
            ]
        ], 200);
    }
}
