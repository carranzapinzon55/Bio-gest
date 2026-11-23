<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    /**
     * Vista administrativa del reporte de ventas con filtro por rango de fechas,
     * estados de pedidos y totales agrupados por producto.
     */
    public function ventas(Request $request): View
    {
        $request->validate([
            'fecha_inicio'  => ['nullable', 'date'],
            'fecha_fin'     => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'estado_pedido' => ['nullable', 'string'],
        ], [
            'fecha_fin.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ]);

        $fechaInicio  = $request->input('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin     = $request->input('fecha_fin', now()->format('Y-m-d'));
        $estadoFiltro = $request->input('estado_pedido');

        // 1. Consulta de pedidos (ventas registradas) en el rango de fechas
        $pedidosQuery = Pedido::with(['usuario', 'detalles.producto'])
            ->whereDate('fecha_pedido', '>=', $fechaInicio)
            ->whereDate('fecha_pedido', '<=', $fechaFin)
            ->orderByDesc('fecha_pedido')
            ->orderByDesc('id_pedido');

        if (!empty($estadoFiltro)) {
            $estadoDb = $estadoFiltro === 'Enviado' ? 'Despachado' : $estadoFiltro;
            $pedidosQuery->where('estado_pedido', $estadoDb);
        }

        $pedidos = $pedidosQuery->get();

        // 2. Totales agrupados por estado de pedido dentro del rango de fechas
        $resumenEstados = Pedido::select('estado_pedido', DB::raw('COUNT(*) as total_pedidos'), DB::raw('SUM(total) as monto_total'))
            ->whereDate('fecha_pedido', '>=', $fechaInicio)
            ->whereDate('fecha_pedido', '<=', $fechaFin)
            ->groupBy('estado_pedido')
            ->get();

        // 3. Totales agrupados / resumidos por producto
        $ventasPorProductoQuery = PedidoDetalle::select(
                'producto.id_producto',
                'producto.nombre_producto',
                'producto.precio as precio_actual',
                'producto.stock as stock_actual',
                DB::raw('SUM(pedido_detalle.cantidad) as cantidad_vendida'),
                DB::raw('COUNT(DISTINCT pedido.id_pedido) as numero_pedidos'),
                DB::raw('SUM(pedido_detalle.subtotal) as total_recaudado')
            )
            ->join('pedido', 'pedido_detalle.id_pedido', '=', 'pedido.id_pedido')
            ->join('producto', 'pedido_detalle.id_producto', '=', 'producto.id_producto')
            ->whereDate('pedido.fecha_pedido', '>=', $fechaInicio)
            ->whereDate('pedido.fecha_pedido', '<=', $fechaFin);

        if (!empty($estadoFiltro)) {
            $estadoDb = $estadoFiltro === 'Enviado' ? 'Despachado' : $estadoFiltro;
            $ventasPorProductoQuery->where('pedido.estado_pedido', $estadoDb);
        } else {
            $ventasPorProductoQuery->where('pedido.estado_pedido', '!=', 'Cancelado');
        }

        $ventasPorProducto = $ventasPorProductoQuery
            ->groupBy('producto.id_producto', 'producto.nombre_producto', 'producto.precio', 'producto.stock')
            ->orderByDesc('total_recaudado')
            ->get();

        // 4. KPIs generales del rango filtrado
        $pedidosActivos = $pedidos->where('estado_pedido', '!=', 'Cancelado');
        $kpis = [
            'total_pedidos'      => $pedidos->count(),
            'ingresos_netos'     => $pedidosActivos->sum('total'),
            'unidades_vendidas'  => $ventasPorProducto->sum('cantidad_vendida'),
            'pedidos_cancelados' => $pedidos->where('estado_pedido', 'Cancelado')->count(),
        ];

        return view('admin.reportes.ventas', compact(
            'pedidos',
            'resumenEstados',
            'ventasPorProducto',
            'kpis',
            'fechaInicio',
            'fechaFin',
            'estadoFiltro'
        ));
    }

    /**
     * Exportar el reporte de ventas filtrado a formato CSV compatible con Microsoft Excel.
     * Incluye cabecera BOM UTF-8 (\xEF\xBB\xBF) y delimitador punto y coma (;).
     */
    public function exportarVentasCsv(Request $request): StreamedResponse
    {
        $fechaInicio  = $request->input('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin     = $request->input('fecha_fin', now()->format('Y-m-d'));
        $estadoFiltro = $request->input('estado_pedido');

        $pedidosQuery = Pedido::with(['usuario', 'detalles.producto'])
            ->whereDate('fecha_pedido', '>=', $fechaInicio)
            ->whereDate('fecha_pedido', '<=', $fechaFin)
            ->orderByDesc('fecha_pedido')
            ->orderByDesc('id_pedido');

        if (!empty($estadoFiltro)) {
            $estadoDb = $estadoFiltro === 'Enviado' ? 'Despachado' : $estadoFiltro;
            $pedidosQuery->where('estado_pedido', $estadoDb);
        }

        $pedidos = $pedidosQuery->get();

        $ventasPorProductoQuery = PedidoDetalle::select(
                'producto.id_producto',
                'producto.nombre_producto',
                DB::raw('SUM(pedido_detalle.cantidad) as cantidad_vendida'),
                DB::raw('COUNT(DISTINCT pedido.id_pedido) as numero_pedidos'),
                DB::raw('SUM(pedido_detalle.subtotal) as total_recaudado')
            )
            ->join('pedido', 'pedido_detalle.id_pedido', '=', 'pedido.id_pedido')
            ->join('producto', 'pedido_detalle.id_producto', '=', 'producto.id_producto')
            ->whereDate('pedido.fecha_pedido', '>=', $fechaInicio)
            ->whereDate('pedido.fecha_pedido', '<=', $fechaFin);

        if (!empty($estadoFiltro)) {
            $estadoDb = $estadoFiltro === 'Enviado' ? 'Despachado' : $estadoFiltro;
            $ventasPorProductoQuery->where('pedido.estado_pedido', $estadoDb);
        } else {
            $ventasPorProductoQuery->where('pedido.estado_pedido', '!=', 'Cancelado');
        }

        $ventasPorProducto = $ventasPorProductoQuery
            ->groupBy('producto.id_producto', 'producto.nombre_producto')
            ->orderByDesc('total_recaudado')
            ->get();

        $nombreArchivo = "reporte_ventas_biogest_{$fechaInicio}_al_{$fechaFin}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($pedidos, $ventasPorProducto, $fechaInicio, $fechaFin, $estadoFiltro) {
            $handle = fopen('php://output', 'w');

            // CRÍTICO: Cabecera BOM UTF-8 para que Microsoft Excel reconozca tildes, "ñ" y caracteres especiales
            fwrite($handle, "\xEF\xBB\xBF");

            // Encabezado del Reporte
            fputcsv($handle, ['BIO-GEST | TENEBRIOS GOLD - REPORTE DE VENTAS'], ';');
            fputcsv($handle, ['Rango de Fechas:', "Desde {$fechaInicio} hasta {$fechaFin}", 'Filtro Estado:', $estadoFiltro ?: 'Todos'], ';');
            fputcsv($handle, [], ';');

            // SECCIÓN 1: Detalle de Ventas Registradas
            fputcsv($handle, ['1. LISTADO DE VENTAS Y PEDIDOS REGISTRADOS'], ';');
            fputcsv($handle, [
                'Número de Pedido',
                'Fecha y Hora',
                'Cliente',
                'Correo Cliente',
                'Ciudad Entrega',
                'Productos y Cantidades',
                'Total Unidades',
                'Subtotal (COP)',
                'Costo Envío (COP)',
                'Valor Total (COP)',
                'Método de Pago',
                'Estado de Pago',
                'Estado del Pedido'
            ], ';');

            foreach ($pedidos as $pedido) {
                $resumenProductos = $pedido->detalles->map(function ($det) {
                    $nombre = optional($det->producto)->nombre_producto ?? 'Producto';
                    return "{$nombre} (x{$det->cantidad})";
                })->implode(' | ');

                $totalUnidades = $pedido->detalles->sum('cantidad');

                fputcsv($handle, [
                    $pedido->numero_pedido ?? ('PED-' . $pedido->id_pedido),
                    optional($pedido->fecha_pedido)->format('Y-m-d H:i:s'),
                    optional($pedido->usuario)->nombre_completo ?? 'Cliente',
                    optional($pedido->usuario)->correo ?? 'N/A',
                    $pedido->ciudad_entrega ?? 'Bogotá',
                    $resumenProductos,
                    $totalUnidades,
                    number_format((float) $pedido->subtotal, 2, ',', ''),
                    number_format((float) $pedido->costo_envio, 2, ',', ''),
                    number_format((float) $pedido->total, 2, ',', ''),
                    $pedido->metodo_pago,
                    $pedido->estado_pago,
                    $pedido->estado_pedido,
                ], ';');
            }

            fputcsv($handle, [], ';');

            // SECCIÓN 2: Totales Agrupados por Producto
            fputcsv($handle, ['2. TOTALES AGRUPADOS Y RESUMIDOS POR PRODUCTO'], ';');
            fputcsv($handle, [
                'ID Producto',
                'Nombre del Producto',
                'Pedidos con este Producto',
                'Cantidad Total Vendida',
                'Ingresos Totales por Producto (COP)'
            ], ';');

            foreach ($ventasPorProducto as $item) {
                fputcsv($handle, [
                    'PROD-' . str_pad((string) $item->id_producto, 3, '0', STR_PAD_LEFT),
                    $item->nombre_producto,
                    $item->numero_pedidos,
                    $item->cantidad_vendida,
                    number_format((float) $item->total_recaudado, 2, ',', ''),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
