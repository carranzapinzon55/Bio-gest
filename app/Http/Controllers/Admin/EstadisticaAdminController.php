<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstadisticaAdminController extends Controller
{
    /**
     * Módulo Unificado de Estadísticas y Reportes Comerciales BIO-GEST (Tenebrios Gold).
     */
    public function index(Request $request): View
    {
        $request->validate([
            'fecha_inicio'  => ['nullable', 'date'],
            'fecha_fin'     => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'estado_pedido' => ['nullable', 'string'],
        ], [
            'fecha_fin.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ]);

        // Filtro de fechas por defecto al mes/año actual (2026, nunca 2025)
        $fechaInicio  = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin     = $request->input('fecha_fin', now()->format('Y-m-d'));
        $estadoFiltro = $request->input('estado_pedido');

        // 1. Pedidos filtrados en el rango desde MySQL
        $pedidosQuery = Pedido::with(['usuario', 'detalles.producto'])
            ->whereDate('fecha_pedido', '>=', $fechaInicio)
            ->whereDate('fecha_pedido', '<=', $fechaFin)
            ->orderByDesc('fecha_pedido')
            ->orderByDesc('id_pedido');

        if (!empty($estadoFiltro)) {
            if ($estadoFiltro === 'Confirmado/Enviado') {
                $pedidosQuery->whereIn('estado_pedido', ['Confirmado', 'Procesando', 'Enviado', 'Despachado']);
            } elseif ($estadoFiltro === 'Entregado') {
                $pedidosQuery->whereIn('estado_pedido', ['Entregado', 'Completado']);
            } else {
                $pedidosQuery->where('estado_pedido', $estadoFiltro);
            }
        }

        $pedidos = $pedidosQuery->get();

        // Pedidos válidos para cálculos económicos (excluyendo cancelados salvo filtro explícito)
        $pedidosValidos = empty($estadoFiltro)
            ? $pedidos->where('estado_pedido', '!=', 'Cancelado')
            : ($estadoFiltro === 'Cancelado' ? $pedidos : $pedidos->where('estado_pedido', '!=', 'Cancelado'));

        // 2. Cálculo dinámico de métricas clave desde MySQL
        $ventasTotales    = (float) $pedidosValidos->sum('total');
        $totalPedidos     = $pedidosValidos->count();
        $ticketPromedio   = $totalPedidos > 0 ? ($ventasTotales / $totalPedidos) : 0;
        $unidadesVendidas = (int) $pedidosValidos->flatMap->detalles->sum('cantidad');

        // 3. Salud y Rotación de Inventario (ventas agrupadas por producto en el período)
        $ventasPorProductoQuery = PedidoDetalle::select(
                'pedido_detalle.id_producto',
                DB::raw('SUM(pedido_detalle.cantidad) as total_unidades'),
                DB::raw('SUM(pedido_detalle.subtotal) as total_ingresos'),
                DB::raw('COUNT(DISTINCT pedido.id_pedido) as total_pedidos')
            )
            ->join('pedido', 'pedido_detalle.id_pedido', '=', 'pedido.id_pedido')
            ->whereDate('pedido.fecha_pedido', '>=', $fechaInicio)
            ->whereDate('pedido.fecha_pedido', '<=', $fechaFin);

        if (!empty($estadoFiltro)) {
            if ($estadoFiltro === 'Confirmado/Enviado') {
                $ventasPorProductoQuery->whereIn('pedido.estado_pedido', ['Confirmado', 'Procesando', 'Enviado', 'Despachado']);
            } elseif ($estadoFiltro === 'Entregado') {
                $ventasPorProductoQuery->whereIn('pedido.estado_pedido', ['Entregado', 'Completado']);
            } else {
                $ventasPorProductoQuery->where('pedido.estado_pedido', $estadoFiltro);
            }
        } else {
            $ventasPorProductoQuery->where('pedido.estado_pedido', '!=', 'Cancelado');
        }

        $ventasPorProducto = $ventasPorProductoQuery
            ->groupBy('pedido_detalle.id_producto')
            ->get()
            ->keyBy('id_producto');

        $todosProductos = Producto::with('categoria')->orderBy('nombre_producto')->get();

        $tablaSaludInventario = $todosProductos->map(function ($prod) use ($ventasPorProducto) {
            $venta      = $ventasPorProducto->get($prod->id_producto);
            $unidades   = $venta ? (int) $venta->total_unidades : 0;
            $ingresos   = $venta ? (float) $venta->total_ingresos : 0;
            $numPedidos = $venta ? (int) $venta->total_pedidos : 0;

            // Clasificación de rotación
            if ($unidades >= 5) {
                $rotacion   = 'Alta';
                $badgeClass = 'badge-rotacion-alta';
            } elseif ($unidades >= 1) {
                $rotacion   = 'Media';
                $badgeClass = 'badge-rotacion-media';
            } else {
                $rotacion   = 'Baja';
                $badgeClass = 'badge-rotacion-baja';
            }

            return [
                'id_producto'       => $prod->id_producto,
                'nombre_producto'   => $prod->nombre_producto,
                'categoria'         => $prod->categoria?->nombre_categoria ?? 'Sin categoría',
                'precio'            => (float) $prod->precio,
                'stock'             => (int) $prod->stock,
                'stock_minimo'      => (int) ($prod->stock_minimo ?? 0),
                'unidades_vendidas' => $unidades,
                'total_ingresos'    => $ingresos,
                'total_pedidos'     => $numPedidos,
                'rotacion'          => $rotacion,
                'badge_class'       => $badgeClass,
            ];
        });

        // Conteo de Productos con Baja Rotación
        $productosBajaRotacion = $tablaSaludInventario->where('rotacion', 'Baja')->count();

        // 4. Gráfica Chart.js: "Ingresos vs. Pedidos por Mes" (Año 2026)
        $añoActual = now()->year;
        $mensualRaw = Pedido::selectRaw('MONTH(fecha_pedido) as mes, SUM(total) as ingresos, COUNT(id_pedido) as pedidos')
            ->whereYear('fecha_pedido', $añoActual)
            ->where('estado_pedido', '!=', 'Cancelado')
            ->groupBy(DB::raw('MONTH(fecha_pedido)'))
            ->get()
            ->keyBy('mes');

        $mesesNombres = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        $chartMesesLabels  = array_values($mesesNombres);
        $chartIngresosData = [];
        $chartPedidosData  = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartIngresosData[] = (float) ($mensualRaw[$m]->ingresos ?? 0);
            $chartPedidosData[]  = (int) ($mensualRaw[$m]->pedidos ?? 0);
        }

        // 5. Gráfica Chart.js: "Top Productos Más Vendidos"
        $topProductos = $tablaSaludInventario->sortByDesc('unidades_vendidas')->take(5)->values();
        $chartTopLabels    = $topProductos->pluck('nombre_producto')->toArray();
        $chartTopUnidades  = $topProductos->pluck('unidades_vendidas')->toArray();

        return view('admin.estadisticas', compact(
            'fechaInicio',
            'fechaFin',
            'estadoFiltro',
            'ventasTotales',
            'ticketPromedio',
            'unidadesVendidas',
            'productosBajaRotacion',
            'totalPedidos',
            'pedidos',
            'tablaSaludInventario',
            'chartMesesLabels',
            'chartIngresosData',
            'chartPedidosData',
            'chartTopLabels',
            'chartTopUnidades'
        ));
    }

    /**
     * Exportar reporte filtrado a formato CSV compatible con Microsoft Excel (BOM UTF-8, delimitador ';').
     */
    public function exportarCsv(Request $request): StreamedResponse
    {
        $fechaInicio  = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin     = $request->input('fecha_fin', now()->format('Y-m-d'));
        $estadoFiltro = $request->input('estado_pedido');

        $pedidosQuery = Pedido::with(['usuario', 'detalles.producto'])
            ->whereDate('fecha_pedido', '>=', $fechaInicio)
            ->whereDate('fecha_pedido', '<=', $fechaFin)
            ->orderByDesc('fecha_pedido')
            ->orderByDesc('id_pedido');

        if (!empty($estadoFiltro)) {
            if ($estadoFiltro === 'Confirmado/Enviado') {
                $pedidosQuery->whereIn('estado_pedido', ['Confirmado', 'Procesando', 'Enviado', 'Despachado']);
            } elseif ($estadoFiltro === 'Entregado') {
                $pedidosQuery->whereIn('estado_pedido', ['Entregado', 'Completado']);
            } else {
                $pedidosQuery->where('estado_pedido', $estadoFiltro);
            }
        }

        $pedidos = $pedidosQuery->get();

        $pedidosValidos = empty($estadoFiltro)
            ? $pedidos->where('estado_pedido', '!=', 'Cancelado')
            : ($estadoFiltro === 'Cancelado' ? $pedidos : $pedidos->where('estado_pedido', '!=', 'Cancelado'));

        $ventasTotales    = (float) $pedidosValidos->sum('total');
        $totalPedidos     = $pedidosValidos->count();
        $ticketPromedio   = $totalPedidos > 0 ? ($ventasTotales / $totalPedidos) : 0;
        $unidadesVendidas = (int) $pedidosValidos->flatMap->detalles->sum('cantidad');

        // Ventas por producto en el rango
        $ventasPorProductoQuery = PedidoDetalle::select(
                'pedido_detalle.id_producto',
                DB::raw('SUM(pedido_detalle.cantidad) as total_unidades'),
                DB::raw('SUM(pedido_detalle.subtotal) as total_ingresos')
            )
            ->join('pedido', 'pedido_detalle.id_pedido', '=', 'pedido.id_pedido')
            ->whereDate('pedido.fecha_pedido', '>=', $fechaInicio)
            ->whereDate('pedido.fecha_pedido', '<=', $fechaFin);

        if (!empty($estadoFiltro)) {
            if ($estadoFiltro === 'Confirmado/Enviado') {
                $ventasPorProductoQuery->whereIn('pedido.estado_pedido', ['Confirmado', 'Procesando', 'Enviado', 'Despachado']);
            } elseif ($estadoFiltro === 'Entregado') {
                $ventasPorProductoQuery->whereIn('pedido.estado_pedido', ['Entregado', 'Completado']);
            } else {
                $ventasPorProductoQuery->where('pedido.estado_pedido', $estadoFiltro);
            }
        } else {
            $ventasPorProductoQuery->where('pedido.estado_pedido', '!=', 'Cancelado');
        }

        $ventasPorProducto = $ventasPorProductoQuery
            ->groupBy('pedido_detalle.id_producto')
            ->get()
            ->keyBy('id_producto');

        $todosProductos = Producto::with('categoria')->orderBy('nombre_producto')->get();

        $fileName = "reporte_estadisticas_biogest_{$fechaInicio}_al_{$fechaFin}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($pedidos, $todosProductos, $ventasPorProducto, $fechaInicio, $fechaFin, $estadoFiltro, $ventasTotales, $ticketPromedio, $totalPedidos, $unidadesVendidas) {
            $handle = fopen('php://output', 'w');

            // Cabecera BOM UTF-8 para reconocimiento de caracteres en Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Información general
            fputcsv($handle, ['BIO-GEST | TENEBRIOS GOLD - REPORTE UNIFICADO DE ESTADÍSTICAS Y VENTAS'], ';', '"', "\\");
            fputcsv($handle, ['Rango de Fechas:', "Desde {$fechaInicio} hasta {$fechaFin}", 'Filtro de Estado:', $estadoFiltro ?: 'Todos'], ';', '"', "\\");
            fputcsv($handle, ['Generado el:', now()->format('Y-m-d H:i:s')], ';', '"', "\\");
            fputcsv($handle, [], ';', '"', "\\");

            // Métricas Clave
            fputcsv($handle, ['RESUMEN DE MÉTRICAS CLAVE (KPIS)'], ';', '"', "\\");
            fputcsv($handle, ['Métrica', 'Valor'], ';', '"', "\\");
            fputcsv($handle, ['Ventas Totales (COP)', number_format($ventasTotales, 2, ',', '')], ';', '"', "\\");
            fputcsv($handle, ['Total de Pedidos', $totalPedidos], ';', '"', "\\");
            fputcsv($handle, ['Ticket Promedio (COP)', number_format($ticketPromedio, 2, ',', '')], ';', '"', "\\");
            fputcsv($handle, ['Unidades Vendidas', $unidadesVendidas], ';', '"', "\\");
            fputcsv($handle, [], ';', '"', "\\");

            // SECCIÓN 1: Salud y Rotación de Inventario
            fputcsv($handle, ['1. SALUD Y ROTACIÓN DE INVENTARIO'], ';', '"', "\\");
            fputcsv($handle, [
                'ID Producto',
                'Nombre del Producto',
                'Categoría',
                'Precio Actual (COP)',
                'Stock Actual',
                'Stock Mínimo',
                'Unidades Vendidas',
                'Total Ingresos (COP)',
                'Nivel de Rotación'
            ], ';', '"', "\\");

            foreach ($todosProductos as $prod) {
                $venta    = $ventasPorProducto->get($prod->id_producto);
                $unidades = $venta ? (int) $venta->total_unidades : 0;
                $ingresos = $venta ? (float) $venta->total_ingresos : 0;
                $rotacion = $unidades >= 5 ? 'Alta' : ($unidades >= 1 ? 'Media' : 'Baja');

                fputcsv($handle, [
                    'PROD-' . str_pad((string) $prod->id_producto, 3, '0', STR_PAD_LEFT),
                    $prod->nombre_producto,
                    $prod->categoria?->nombre_categoria ?? 'Sin categoría',
                    number_format((float) $prod->precio, 2, ',', ''),
                    $prod->stock,
                    $prod->stock_minimo ?? 0,
                    $unidades,
                    number_format($ingresos, 2, ',', ''),
                    $rotacion
                ], ';', '"', "\\");
            }

            fputcsv($handle, [], ';', '"', "\\");

            // SECCIÓN 2: Detalle de Pedidos
            fputcsv($handle, ['2. DETALLE DE VENTAS Y PEDIDOS EN EL PERÍODO'], ';', '"', "\\");
            fputcsv($handle, [
                'Número de Pedido',
                'Fecha y Hora',
                'Cliente',
                'Correo Cliente',
                'Ciudad Entrega',
                'Subtotal (COP)',
                'Costo Envío (COP)',
                'Total (COP)',
                'Método de Pago',
                'Estado de Pago',
                'Estado del Pedido'
            ], ';', '"', "\\");

            foreach ($pedidos as $pedido) {
                fputcsv($handle, [
                    $pedido->numero_pedido ?: ('PED-' . (1000 + $pedido->id_pedido)),
                    optional($pedido->fecha_pedido)->format('Y-m-d H:i:s'),
                    optional($pedido->usuario)->nombre_completo ?? 'Cliente',
                    optional($pedido->usuario)->correo ?? 'N/A',
                    $pedido->ciudad_entrega ?? 'Bogotá',
                    number_format((float) $pedido->subtotal, 2, ',', ''),
                    number_format((float) $pedido->costo_envio, 2, ',', ''),
                    number_format((float) $pedido->total, 2, ',', ''),
                    $pedido->metodo_pago,
                    $pedido->estado_pago,
                    $pedido->estado_pedido,
                ], ';', '"', "\\");
            }

            fclose($handle);
        }, 200, $headers);
    }
}
