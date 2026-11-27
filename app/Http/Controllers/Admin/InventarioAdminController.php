<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventarioAdminController extends Controller
{
    /**
     * Consultar existencias actuales, alertas de stock bajo e historial de movimientos (Kardex).
     */
    public function index(Request $request): View
    {
        $productos = Producto::with('categoria')->orderBy('id_producto')->get();

        // Productos con stock bajo o agotado (stock <= stock_minimo)
        $productosStockBajo = $productos->filter(function (Producto $prod) {
            $minimo = $prod->stock_minimo ?? 10;
            return $prod->stock <= $minimo;
        });

        // Historial de entradas y salidas de inventario con filtros opcionales
        $movimientosQuery = MovimientoInventario::with('producto')
            ->orderByDesc('fecha_movimiento')
            ->orderByDesc('id_movimiento');

        if ($request->filled('id_producto')) {
            $movimientosQuery->where('id_producto', $request->id_producto);
        }

        if ($request->filled('tipo_movimiento')) {
            $movimientosQuery->where('tipo_movimiento', $request->tipo_movimiento);
        }

        $movimientos = $movimientosQuery->paginate(15)->withQueryString();

        $resumenInventario = [
            'total_productos'    => $productos->count(),
            'unidades_totales'   => $productos->sum('stock'),
            'valor_inventario'   => $productos->sum(fn ($p) => $p->stock * $p->precio),
            'alertas_stock_bajo' => $productosStockBajo->count(),
        ];

        return view('admin.inventario.index', compact(
            'productos',
            'productosStockBajo',
            'movimientos',
            'resumenInventario'
        ));
    }

    /**
     * Registrar una entrada, salida o ajuste de inventario verificando que se refleje en el stock.
     */
    public function registrarMovimiento(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'id_producto'     => ['required', 'integer', 'exists:producto,id_producto'],
            'tipo_movimiento' => ['required', 'in:ENTRADA,SALIDA,AJUSTE'],
            'cantidad'        => ['required', 'integer', 'min:1'],
            'observacion'     => ['required', 'string', 'min:3', 'max:255'],
        ], [
            'id_producto.required'     => 'Debes seleccionar un producto.',
            'tipo_movimiento.required' => 'Selecciona el tipo de movimiento (ENTRADA, SALIDA o AJUSTE).',
            'cantidad.min'             => 'La cantidad debe ser mayor o igual a 1.',
            'observacion.required'     => 'Ingresa el motivo u observación del movimiento.',
        ]);

        $producto = Producto::findOrFail($datos['id_producto']);

        if ($datos['tipo_movimiento'] === 'SALIDA' && $producto->stock < $datos['cantidad']) {
            return back()->with('error', "No es posible registrar la SALIDA de {$datos['cantidad']} unidades para '{$producto->nombre_producto}'. Stock disponible actual: {$producto->stock}.")->withInput();
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

            $observacionCompleta = "{$datos['observacion']} (Stock previo: {$stockAnterior} -> Stock actual: {$producto->stock})";

            if ($conteoDespues === $conteoAntes) {
                MovimientoInventario::create([
                    'id_producto'      => $producto->id_producto,
                    'tipo_movimiento'  => $datos['tipo_movimiento'],
                    'cantidad'         => $datos['cantidad'],
                    'observacion'      => $observacionCompleta,
                    'fecha_movimiento' => now(),
                ]);
            } else {
                $ultimo = MovimientoInventario::where('id_producto', $producto->id_producto)
                    ->orderByDesc('id_movimiento')
                    ->first();
                if ($ultimo) {
                    $ultimo->update(['observacion' => $observacionCompleta]);
                }
            }
        });

        return redirect()->route('admin.inventario.index')
            ->with('success', "Movimiento de {$datos['tipo_movimiento']} ({$datos['cantidad']} uds.) registrado en '{$producto->nombre_producto}'. Stock actualizado a {$producto->fresh()->stock} unidades.");
    }

    /**
     * Módulo específico de Alertas de Stock Bajo y Reabastecimiento Crítico.
     */
    public function alertasStock(): View
    {
        $productosCriticos = Producto::with('categoria')
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->get();

        $todosProductos = Producto::with('categoria')
            ->orderBy('stock')
            ->get();

        $conteoCriticos = $productosCriticos->count();
        $conteoTotal = $todosProductos->count();

        return view('admin.inventario.alertas', compact(
            'productosCriticos',
            'todosProductos',
            'conteoCriticos',
            'conteoTotal'
        ));
    }
}
