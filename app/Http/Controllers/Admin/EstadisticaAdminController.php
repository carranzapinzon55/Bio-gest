<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            'fecha_inicio'  => ['nullable', 'date', 'before_or_equal:today'],
            'fecha_fin'     => ['nullable', 'date', 'after_or_equal:fecha_inicio', 'before_or_equal:today'],
            'estado_pedido' => ['nullable', 'string'],
        ], [
            'fecha_inicio.before_or_equal' => 'La fecha inicial ("Fecha Desde") no puede ser una fecha futura.',
            'fecha_fin.after_or_equal'     => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            'fecha_fin.before_or_equal'    => 'La fecha final ("Fecha Hasta") no puede ser una fecha futura.',
        ]);

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

        $pedidosValidos = empty($estadoFiltro)
            ? $pedidos->where('estado_pedido', '!=', 'Cancelado')
            : ($estadoFiltro === 'Cancelado' ? $pedidos : $pedidos->where('estado_pedido', '!=', 'Cancelado'));

        // 2. Cálculo dinámico de métricas clave desde MySQL
        $ventasTotales    = (float) $pedidosValidos->sum('total');
        $totalPedidos     = $pedidosValidos->count();
        $ticketPromedio   = $totalPedidos > 0 ? ($ventasTotales / $totalPedidos) : 0;
        $unidadesVendidas = (int) $pedidosValidos->flatMap->detalles->sum('cantidad');

        // 3. Salud y Rotación de Inventario
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

        $productosBajaRotacion = $tablaSaludInventario->where('rotacion', 'Baja')->count();

        // 4. Gráfica Chart.js: "Ingresos vs. Pedidos por Mes"
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
        $chartTopLabels   = $topProductos->pluck('nombre_producto')->toArray();
        $chartTopUnidades = $topProductos->pluck('unidades_vendidas')->toArray();

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
        $d = $this->obtenerDatosReporte($request);

        $fileName = "reporte_estadisticas_biogest_{$d['fechaInicio']}_al_{$d['fechaFin']}.csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($d) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['BIO-GEST | TENEBRIOS GOLD - REPORTE UNIFICADO DE ESTADÍSTICAS Y VENTAS'], ';', '"', "\\");
            fputcsv($handle, ['Rango de Fechas:', "Desde {$d['fechaInicio']} hasta {$d['fechaFin']}", 'Filtro de Estado:', $d['estadoFiltro'] ?: 'Todos'], ';', '"', "\\");
            fputcsv($handle, ['Generado el:', now()->format('Y-m-d H:i:s')], ';', '"', "\\");
            fputcsv($handle, [], ';', '"', "\\");

            fputcsv($handle, ['RESUMEN DE MÉTRICAS CLAVE (KPIS)'], ';', '"', "\\");
            fputcsv($handle, ['Métrica', 'Valor'], ';', '"', "\\");
            fputcsv($handle, ['Ventas Totales (COP)', number_format($d['ventasTotales'], 2, ',', '')], ';', '"', "\\");
            fputcsv($handle, ['Total de Pedidos', $d['totalPedidos']], ';', '"', "\\");
            fputcsv($handle, ['Ticket Promedio (COP)', number_format($d['ticketPromedio'], 2, ',', '')], ';', '"', "\\");
            fputcsv($handle, ['Unidades Vendidas', $d['unidadesVendidas']], ';', '"', "\\");
            fputcsv($handle, [], ';', '"', "\\");

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

            foreach ($d['todosProductos'] as $prod) {
                $venta    = $d['ventasPorProducto']->get($prod->id_producto);
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

            foreach ($d['pedidos'] as $pedido) {
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

    /**
     * Exportar reporte a formato Excel (.xlsx) nativo OpenXML sin depender de ext-zip ni PhpSpreadsheet.
     * Genera un paquete ZIP OpenXML (.xlsx) 100% compatible con Microsoft Excel, LibreOffice y Google Sheets,
     * con estilos corporativos negro (#111111) y dorado (#E6A100).
     */
    public function exportarExcel(Request $request): Response|StreamedResponse
    {
        $d = $this->obtenerDatosReporte($request);

        // Si PhpSpreadsheet y extensiones requeridas (zip y gd) están disponibles, usar PhpSpreadsheet.
        // De lo contrario, usar fallback automático limpio y robusto para compatibilidad multi-PC en cualquier servidor XAMPP.
        if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet') && extension_loaded('zip') && extension_loaded('gd')) {
            try {
                return $this->exportarExcelConPhpSpreadsheet($d);
            } catch (\Throwable $e) {
                // Fallback limpio si falla alguna librería en el entorno
            }
        }

        // Si ZipArchive o ext-zip no están cargados, generar HTML-table .xls nativo 100% compatible con Microsoft Excel
        if (!class_exists('\ZipArchive') && !extension_loaded('zip')) {
            return $this->exportarExcelHtmlFallback($d);
        }

        try {
            $xlsxBinary = $this->buildNativeXlsx($d);
            $fileName   = "reporte_ejecutivo_biogest_{$d['fechaInicio']}_al_{$d['fechaFin']}.xlsx";

            return response($xlsxBinary, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                'Content-Length'      => strlen($xlsxBinary),
                'Cache-Control'       => 'max-age=0',
            ]);
        } catch (\Throwable $e) {
            return $this->exportarExcelHtmlFallback($d);
        }
    }

    /**
     * Fallback limpio a tabla HTML .xls que abre directamente en Excel sin requerir extensiones en XAMPP.
     */
    private function exportarExcelHtmlFallback(array $d): Response
    {
        $fileName = "reporte_ejecutivo_biogest_{$d['fechaInicio']}_al_{$d['fechaFin']}.xls";
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">'
            . '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
            . '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>'
            . '<x:Name>Reporte BIO-GEST</x:Name>'
            . '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->'
            . '<style>'
            . 'table { border-collapse: collapse; font-family: Calibri, Arial, sans-serif; }'
            . 'th { background-color: #111111; color: #ffffff; border: 1px solid #333; padding: 6px 10px; font-weight: bold; }'
            . 'td { border: 1px solid #e5e7eb; padding: 5px 8px; font-size: 11pt; }'
            . '.title { background-color: #111111; color: #e6a100; font-size: 14pt; font-weight: bold; }'
            . '.sub { background-color: #fdf8f0; color: #111111; font-size: 10pt; }'
            . '.kpi-th { background-color: #f3f4f6; color: #111111; text-align: left; }'
            . '.kpi-val { font-weight: bold; color: #047857; }'
            . '</style></head><body>';

        $html .= '<table>';
        $html .= '<tr><td colspan="9" class="title">TENEBRIOS GOLD · REPORTE EJECUTIVO Y ESTADÍSTICAS BIO-GEST</td></tr>';
        $html .= '<tr><td colspan="9" class="sub">Período: ' . $d['fechaInicio'] . ' al ' . $d['fechaFin'] . ' | Filtro: ' . ($d['estadoFiltro'] ?: 'Todos') . ' | Generado: ' . now()->format('d/m/Y h:i a') . '</td></tr>';
        $html .= '<tr><td colspan="9"></td></tr>';

        // KPIs
        $html .= '<tr><td colspan="2" class="title" style="font-size:11pt;">RESUMEN DE INDICADORES CLAVE (KPIS)</td></tr>';
        $html .= '<tr><td class="kpi-th">Ventas Totales (COP)</td><td class="kpi-val">$' . number_format($d['ventasTotales'], 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td class="kpi-th">Total de Pedidos</td><td>' . $d['totalPedidos'] . '</td></tr>';
        $html .= '<tr><td class="kpi-th">Ticket Promedio (COP)</td><td>$' . number_format($d['ticketPromedio'], 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td class="kpi-th">Unidades Vendidas</td><td>' . $d['unidadesVendidas'] . '</td></tr>';
        $html .= '<tr><td colspan="9"></td></tr>';

        // Tabla 1: Inventario
        $html .= '<tr><td colspan="9" class="title" style="font-size:11pt;">1. SALUD Y ROTACIÓN DE INVENTARIO</td></tr>';
        $html .= '<tr><th>Código</th><th>Producto</th><th>Categoría</th><th>Precio Actual</th><th>Stock Físico</th><th>Stock Mínimo</th><th>Vendidas</th><th>Ingresos</th><th>Rotación</th></tr>';
        foreach ($d['todosProductos'] as $prod) {
            $venta    = $d['ventasPorProducto']->get($prod->id_producto);
            $unidades = $venta ? (int) $venta->total_unidades : 0;
            $ingresos = $venta ? (float) $venta->total_ingresos : 0;
            $rotacion = $unidades >= 5 ? 'Alta' : ($unidades >= 1 ? 'Media' : 'Baja');

            $html .= '<tr>'
                . '<td>PROD-' . str_pad((string)$prod->id_producto, 3, '0', STR_PAD_LEFT) . '</td>'
                . '<td>' . htmlspecialchars($prod->nombre_producto) . '</td>'
                . '<td>' . htmlspecialchars($prod->categoria?->nombre_categoria ?? 'General') . '</td>'
                . '<td>$' . number_format($prod->precio, 0, ',', '.') . '</td>'
                . '<td>' . $prod->stock . '</td>'
                . '<td>' . ($prod->stock_minimo ?? 0) . '</td>'
                . '<td>' . $unidades . '</td>'
                . '<td>$' . number_format($ingresos, 0, ',', '.') . '</td>'
                . '<td>' . $rotacion . '</td>'
                . '</tr>';
        }
        $html .= '<tr><td colspan="9"></td></tr>';

        // Tabla 2: Pedidos
        $html .= '<tr><td colspan="9" class="title" style="font-size:11pt;">2. DETALLE DE VENTAS Y PEDIDOS EN EL PERÍODO</td></tr>';
        $html .= '<tr><th>N° Pedido</th><th>Fecha</th><th>Cliente</th><th>Correo</th><th>Ciudad</th><th>Subtotal</th><th>Envío</th><th>Total</th><th>Estado</th></tr>';
        foreach ($d['pedidos'] as $p) {
            $html .= '<tr>'
                . '<td>' . ($p->numero_pedido ?: ('PED-' . (1000 + $p->id_pedido))) . '</td>'
                . '<td>' . optional($p->fecha_pedido)->format('d/m/Y H:i') . '</td>'
                . '<td>' . htmlspecialchars(optional($p->usuario)->nombre_completo ?? 'Cliente') . '</td>'
                . '<td>' . htmlspecialchars(optional($p->usuario)->correo ?? 'N/A') . '</td>'
                . '<td>' . htmlspecialchars($p->ciudad_entrega ?? 'Bogotá') . '</td>'
                . '<td>$' . number_format($p->subtotal, 0, ',', '.') . '</td>'
                . '<td>$' . number_format($p->costo_envio, 0, ',', '.') . '</td>'
                . '<td>$' . number_format($p->total, 0, ',', '.') . '</td>'
                . '<td>' . $p->estado_pedido . '</td>'
                . '</tr>';
        }

        $html .= '</table></body></html>';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Exportar reporte ejecutivo en PDF (.pdf) nativo sin depender de DomPDF externo.
     */
    public function exportarPdf(Request $request): Response
    {
        $d = $this->obtenerDatosReporte($request);

        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $logoPath = public_path('assets/img/LOGO PNG.png');
            if (!file_exists($logoPath)) {
                $logoPath = public_path('assets/img/Logo.jpeg');
            }
            $logoBase64 = null;
            if (file_exists($logoPath)) {
                $mime = mime_content_type($logoPath) ?: 'image/png';
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
            $data = array_merge($d, ['logoBase64' => $logoBase64]);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.estadisticas.pdf_report', $data)
                ->setPaper('a4', 'portrait')
                ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

            $fileName = "reporte_ejecutivo_biogest_{$d['fechaInicio']}_al_{$d['fechaFin']}.pdf";
            return $pdf->download($fileName);
        }

        $pdfBinary = $this->buildNativePdf($d);
        $fileName  = "reporte_ejecutivo_biogest_{$d['fechaInicio']}_al_{$d['fechaFin']}.pdf";

        return response($pdfBinary, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Content-Length'      => strlen($pdfBinary),
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }

    /**
     * =========================================================================
     * GENERADOR NATIVO DE EXCEL OPENXML (.XLSX) EN PHP PURO (SIN EXT-ZIP)
     * =========================================================================
     */
    private function buildNativeXlsx(array $d): string
    {
        $rows = [];

        // Estilos definidos en styles.xml:
        // s=0: Normal
        // s=1: Título principal (Fondo Negro #111111, Texto Dorado #E6A100, Bold 13pt)
        // s=2: Subtítulo / Encabezado tabla (Fondo Gris Oscuro #222222, Texto Blanco, Bold 10pt)
        // s=3: Celda de datos con borde fino
        // s=4: Celda moneda COP ($#,##0) con borde fino
        // s=5: Celda KPI Etiqueta (Fondo Gris Claro, Bold)
        // s=6: Celda KPI Valor Moneda (Fondo Gris Claro, Bold, $#,##0)
        // s=7: Celda KPI Valor Entero (Fondo Gris Claro, Bold, #,##0)

        $rows[] = [
            ['v' => 'TENEBRIOS GOLD · REPORTE EJECUTIVO Y ESTADÍSTICAS BIO-GEST', 't' => 's', 's' => 1],
        ];
        $sub = "Período: {$d['fechaInicio']} al {$d['fechaFin']} | Filtro Estado: " . ($d['estadoFiltro'] ?: 'Todos') . " | Generado: " . now()->format('d/m/Y h:i a');
        $rows[] = [
            ['v' => $sub, 't' => 's', 's' => 2],
        ];
        $rows[] = [];

        // KPIs
        $rows[] = [
            ['v' => 'RESUMEN DE INDICADORES CLAVE (KPIS)', 't' => 's', 's' => 1],
            ['v' => '', 't' => 's', 's' => 1],
        ];
        $rows[] = [
            ['v' => 'Ventas Totales (COP)', 't' => 's', 's' => 5],
            ['v' => (float) $d['ventasTotales'], 't' => 'n', 's' => 6],
        ];
        $rows[] = [
            ['v' => 'Total de Pedidos', 't' => 's', 's' => 5],
            ['v' => (int) $d['totalPedidos'], 't' => 'n', 's' => 7],
        ];
        $rows[] = [
            ['v' => 'Ticket Promedio (COP)', 't' => 's', 's' => 5],
            ['v' => round((float) $d['ticketPromedio'], 2), 't' => 'n', 's' => 6],
        ];
        $rows[] = [
            ['v' => 'Unidades Vendidas', 't' => 's', 's' => 5],
            ['v' => (int) $d['unidadesVendidas'], 't' => 'n', 's' => 7],
        ];
        $rows[] = [];

        // SECCIÓN 1: SALUD Y ROTACIÓN DE INVENTARIO
        $rows[] = [
            ['v' => '1. SALUD Y ROTACIÓN DE INVENTARIO', 't' => 's', 's' => 1],
        ];
        $rows[] = [
            ['v' => 'ID Producto', 't' => 's', 's' => 2],
            ['v' => 'Nombre del Producto', 't' => 's', 's' => 2],
            ['v' => 'Categoría', 't' => 's', 's' => 2],
            ['v' => 'Precio Actual (COP)', 't' => 's', 's' => 2],
            ['v' => 'Stock Físico', 't' => 's', 's' => 2],
            ['v' => 'Stock Mínimo', 't' => 's', 's' => 2],
            ['v' => 'Unidades Vendidas', 't' => 's', 's' => 2],
            ['v' => 'Total Ingresos (COP)', 't' => 's', 's' => 2],
            ['v' => 'Rotación', 't' => 's', 's' => 2],
        ];

        foreach ($d['todosProductos'] as $prod) {
            $venta    = $d['ventasPorProducto']->get($prod->id_producto);
            $unidades = $venta ? (int) $venta->total_unidades : 0;
            $ingresos = $venta ? (float) $venta->total_ingresos : 0;
            $rotacion = $unidades >= 5 ? 'Alta' : ($unidades >= 1 ? 'Media' : 'Baja');

            $rows[] = [
                ['v' => 'PROD-' . str_pad((string) $prod->id_producto, 3, '0', STR_PAD_LEFT), 't' => 's', 's' => 3],
                ['v' => (string) $prod->nombre_producto, 't' => 's', 's' => 3],
                ['v' => (string) ($prod->categoria?->nombre_categoria ?? 'General'), 't' => 's', 's' => 3],
                ['v' => (float) $prod->precio, 't' => 'n', 's' => 4],
                ['v' => (int) $prod->stock, 't' => 'n', 's' => 3],
                ['v' => (int) ($prod->stock_minimo ?? 0), 't' => 'n', 's' => 3],
                ['v' => $unidades, 't' => 'n', 's' => 3],
                ['v' => $ingresos, 't' => 'n', 's' => 4],
                ['v' => $rotacion, 't' => 's', 's' => 3],
            ];
        }

        $rows[] = [];

        // SECCIÓN 2: DETALLE DE PEDIDOS
        $rows[] = [
            ['v' => '2. DETALLE DE VENTAS Y PEDIDOS EN EL PERÍODO', 't' => 's', 's' => 1],
        ];
        $rows[] = [
            ['v' => 'Número Pedido', 't' => 's', 's' => 2],
            ['v' => 'Fecha y Hora', 't' => 's', 's' => 2],
            ['v' => 'Cliente', 't' => 's', 's' => 2],
            ['v' => 'Correo Electrónico', 't' => 's', 's' => 2],
            ['v' => 'Ciudad Entrega', 't' => 's', 's' => 2],
            ['v' => 'Subtotal (COP)', 't' => 's', 's' => 2],
            ['v' => 'Costo Envío (COP)', 't' => 's', 's' => 2],
            ['v' => 'Total (COP)', 't' => 's', 's' => 2],
            ['v' => 'Método Pago', 't' => 's', 's' => 2],
            ['v' => 'Estado Pago', 't' => 's', 's' => 2],
            ['v' => 'Estado Pedido', 't' => 's', 's' => 2],
        ];

        foreach ($d['pedidos'] as $p) {
            $rows[] = [
                ['v' => (string) ($p->numero_pedido ?: ('PED-' . (1000 + $p->id_pedido))), 't' => 's', 's' => 3],
                ['v' => (string) (optional($p->fecha_pedido)->format('Y-m-d H:i:s') ?? '-'), 't' => 's', 's' => 3],
                ['v' => (string) (optional($p->usuario)->nombre_completo ?? 'Cliente'), 't' => 's', 's' => 3],
                ['v' => (string) (optional($p->usuario)->correo ?? 'N/A'), 't' => 's', 's' => 3],
                ['v' => (string) ($p->ciudad_entrega ?? 'Bogotá D.C.'), 't' => 's', 's' => 3],
                ['v' => (float) $p->subtotal, 't' => 'n', 's' => 4],
                ['v' => (float) $p->costo_envio, 't' => 'n', 's' => 4],
                ['v' => (float) $p->total, 't' => 'n', 's' => 4],
                ['v' => (string) ($p->metodo_pago ?? 'Contraentrega'), 't' => 's', 's' => 3],
                ['v' => (string) ($p->estado_pago ?? 'Aprobado'), 't' => 's', 's' => 3],
                ['v' => (string) ($p->estado_pedido ?? 'Completado'), 't' => 's', 's' => 3],
            ];
        }

        // Construir sheet1.xml
        $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        $sheetDataXml = '';
        foreach ($rows as $rIdx => $rowCells) {
            $rowNum = $rIdx + 1;
            if (empty($rowCells)) {
                $sheetDataXml .= "<row r=\"{$rowNum}\"/>";
                continue;
            }
            $sheetDataXml .= "<row r=\"{$rowNum}\">";
            foreach ($rowCells as $cIdx => $cell) {
                $col = $colLetters[$cIdx] ?? 'A';
                $ref = "{$col}{$rowNum}";
                $styleAttr = isset($cell['s']) ? " s=\"{$cell['s']}\"" : '';
                if (($cell['t'] ?? 's') === 'n') {
                    $val = is_numeric($cell['v']) ? $cell['v'] : 0;
                    $sheetDataXml .= "<c r=\"{$ref}\" t=\"n\"{$styleAttr}><v>{$val}</v></c>";
                } else {
                    $escaped = htmlspecialchars((string) $cell['v'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    $sheetDataXml .= "<c r=\"{$ref}\" t=\"inlineStr\"{$styleAttr}><is><t>{$escaped}</t></is></c>";
                }
            }
            $sheetDataXml .= "</row>";
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<cols>'
            . '<col min="1" max="1" width="22" customWidth="1"/>'
            . '<col min="2" max="2" width="36" customWidth="1"/>'
            . '<col min="3" max="4" width="26" customWidth="1"/>'
            . '<col min="5" max="11" width="18" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>' . $sheetDataXml . '</sheetData>'
            . '</worksheet>';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1">'
            . '<numFmt numFmtId="164" formatCode="&quot;$&quot;#,##0"/>'
            . '</numFmts>'
            . '<fonts count="4">'
            . '<font><sz val="10"/><color rgb="FF1E293B"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="12"/><color rgb="FFE6A100"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="10"/><color rgb="FF111111"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="5">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF111111"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF222222"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border>'
            . '<left style="thin"><color rgb="FFCBD5E1"/></left>'
            . '<right style="thin"><color rgb="FFCBD5E1"/></right>'
            . '<top style="thin"><color rgb="FFCBD5E1"/></top>'
            . '<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>'
            . '<diagonal/>'
            . '</border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="8">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
            . '<xf numFmtId="0" fontId="3" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
            . '<xf numFmtId="164" fontId="3" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1"/>'
            . '<xf numFmtId="3" fontId="3" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Reporte BIO-GEST" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        return $this->createZipArchive([
            '[Content_Types].xml'        => $contentTypes,
            '_rels/.rels'                => $rootRels,
            'xl/workbook.xml'            => $workbookXml,
            'xl/_rels/workbook.xml.rels' => $workbookRels,
            'xl/styles.xml'              => $stylesXml,
            'xl/worksheets/sheet1.xml'   => $sheetXml,
        ]);
    }

    /**
     * Empaquetador ZIP en memoria 100% nativo en PHP usando gzdeflate (zlib) y crc32.
     */
    private function createZipArchive(array $files): string
    {
        $localData = '';
        $centralDir = '';
        $offset = 0;
        $count = 0;

        foreach ($files as $name => $content) {
            $name = str_replace('\\', '/', $name);
            $uncompressedSize = strlen($content);
            $crc = crc32($content);
            $compressed = gzdeflate($content, 6);
            $compressedSize = strlen($compressed);
            $nameLen = strlen($name);

            // Local file header
            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0,
                8,
                0,
                0,
                $crc,
                $compressedSize,
                $uncompressedSize,
                $nameLen,
                0
            ) . $name;

            $localEntry = $localHeader . $compressed;
            $localData .= $localEntry;

            // Central directory file header
            $centralDir .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                8,
                0,
                0,
                $crc,
                $compressedSize,
                $uncompressedSize,
                $nameLen,
                0,
                0,
                0,
                0,
                32,
                $offset
            ) . $name;

            $offset += strlen($localEntry);
            $count++;
        }

        $eocd = pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $count,
            $count,
            strlen($centralDir),
            $offset,
            0
        );

        return $localData . $centralDir . $eocd;
    }

    /**
     * =========================================================================
     * GENERADOR NATIVO DE PDF 1.4 EJECUTIVO EN PHP PURO (SIN DOMPDF EXTERNO)
     * =========================================================================
     */
    private function buildNativePdf(array $d): string
    {
        $pages = [];
        $ops = [];

        $startNewPage = function () use (&$ops, &$pages, $d) {
            if (!empty($ops)) {
                $pages[] = implode("\n", $ops);
            }
            $ops = [];

            // Header corporativo oscuro (#111111) con franja dorada (#E6A100)
            $ops[] = "0.067 0.067 0.067 rg 30 765 535 48 re f";
            $ops[] = "0.902 0.631 0.000 rg 30 762 535 3 re f";

            // Título en dorado y subtítulo en blanco
            $ops[] = $this->pdfText(44, 793, 'TENEBRIOS GOLD - BIO-GEST ADMIN', 'F2', 14, [0.902, 0.631, 0.000]);
            $ops[] = $this->pdfText(44, 776, 'Reporte Ejecutivo de Ventas, Pedidos y Rotacion de Inventario', 'F1', 9.5, [1, 1, 1]);

            $fechaGen = 'Generado: ' . now()->format('d/m/Y h:i a');
            $ops[] = $this->pdfText(420, 793, $fechaGen, 'F1', 8.5, [0.85, 0.85, 0.85]);
            $ops[] = $this->pdfText(420, 776, "Periodo: {$d['fechaInicio']} al {$d['fechaFin']}", 'F2', 8.5, [0.902, 0.631, 0.000]);

            // Pie de página
            $ops[] = "0.85 0.88 0.92 RG 0.6 w 30 35 m 565 35 l S";
            $ops[] = $this->pdfText(30, 23, 'BIO-GEST · Tenebrios Gold S.A.S. — Documento Oficial de Auditoria y Estadisticas Comerciales', 'F1', 8, [0.45, 0.50, 0.58]);
        };

        $startNewPage();
        $y = 735;

        // 1. TARJETAS KPI
        $kpis = [
            ['VENTAS TOTALES', '$ ' . number_format((float) $d['ventasTotales'], 0, ',', '.') . ' COP'],
            ['PEDIDOS REGISTRADOS', (string) $d['totalPedidos'] . ' pedido(s)'],
            ['TICKET PROMEDIO', '$ ' . number_format((float) $d['ticketPromedio'], 0, ',', '.') . ' COP'],
            ['UNIDADES VENDIDAS', (string) $d['unidadesVendidas'] . ' uds.'],
        ];

        $cardW = 126;
        $gap = 10;
        foreach ($kpis as $idx => [$label, $val]) {
            $x = 30 + $idx * ($cardW + $gap);
            $ops[] = "0.973 0.980 0.988 rg {$x} " . ($y - 36) . " {$cardW} 42 re f";
            $ops[] = "0.902 0.631 0.000 rg {$x} " . ($y - 36) . " 3 42 re f";
            $ops[] = $this->pdfText($x + 9, $y - 11, $label, 'F2', 7.5, [0.39, 0.45, 0.55]);
            $ops[] = $this->pdfText($x + 9, $y - 27, $val, 'F2', 10.5, [0.067, 0.067, 0.067]);
        }

        $y -= 58;

        // 2. TABLA: SALUD Y ROTACIÓN DE INVENTARIO
        $ops[] = $this->pdfText(30, $y, '1. SALUD Y ROTACION DE INVENTARIO OFICIAL', 'F2', 10.5, [0.067, 0.067, 0.067]);
        $y -= 10;

        // Encabezado de tabla
        $ops[] = "0.067 0.067 0.067 rg 30 " . ($y - 16) . " 535 18 re f";
        $ops[] = $this->pdfText(35, $y - 11, 'CODIGO', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(85, $y - 11, 'PRODUCTO', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(255, $y - 11, 'CATEGORIA', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(340, $y - 11, 'PRECIO', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(400, $y - 11, 'STOCK', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(440, $y - 11, 'VEND.', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(475, $y - 11, 'INGRESOS', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(530, $y - 11, 'ROT.', 'F2', 8, [0.902, 0.631, 0.000]);
        $y -= 16;

        $rowAlt = 0;
        foreach ($d['todosProductos'] as $prod) {
            if ($y < 75) {
                $startNewPage();
                $y = 735;
            }
            $venta    = $d['ventasPorProducto']->get($prod->id_producto);
            $unidades = $venta ? (int) $venta->total_unidades : 0;
            $ingresos = $venta ? (float) $venta->total_ingresos : 0;
            $rotacion = $unidades >= 5 ? 'Alta' : ($unidades >= 1 ? 'Media' : 'Baja');

            if ($rowAlt % 2 === 1) {
                $ops[] = "0.973 0.980 0.988 rg 30 " . ($y - 15) . " 535 16 re f";
            }
            $ops[] = "0.88 0.91 0.94 RG 0.4 w 30 " . ($y - 15) . " 535 16 re S";

            $cod = 'PROD-' . str_pad((string) $prod->id_producto, 3, '0', STR_PAD_LEFT);
            $nom = mb_strimwidth((string) $prod->nombre_producto, 0, 32, '...');
            $cat = mb_strimwidth((string) ($prod->categoria?->nombre_categoria ?? 'General'), 0, 16, '...');
            $pre = '$' . number_format((float) $prod->precio, 0, ',', '.');
            $ing = '$' . number_format($ingresos, 0, ',', '.');

            $ops[] = $this->pdfText(35, $y - 10, $cod, 'F2', 7.8, [0.2, 0.25, 0.33]);
            $ops[] = $this->pdfText(85, $y - 10, $nom, 'F1', 8, [0.07, 0.07, 0.07]);
            $ops[] = $this->pdfText(255, $y - 10, $cat, 'F1', 7.8, [0.35, 0.4, 0.48]);
            $ops[] = $this->pdfText(340, $y - 10, $pre, 'F1', 8, [0.07, 0.07, 0.07]);
            $ops[] = $this->pdfText(405, $y - 10, (string) $prod->stock, 'F2', 8, [0.07, 0.07, 0.07]);
            $ops[] = $this->pdfText(445, $y - 10, (string) $unidades, 'F2', 8, [0.07, 0.07, 0.07]);
            $ops[] = $this->pdfText(475, $y - 10, $ing, 'F2', 8, [0.02, 0.47, 0.34]);
            $ops[] = $this->pdfText(530, $y - 10, $rotacion, 'F2', 7.8, [0.07, 0.07, 0.07]);

            $y -= 16;
            $rowAlt++;
        }

        $y -= 24;
        if ($y < 120) {
            $startNewPage();
            $y = 735;
        }

        // 3. TABLA: DETALLE DE PEDIDOS Y VENTAS DEL PERÍODO
        $ops[] = $this->pdfText(30, $y, '2. DETALLE DE PEDIDOS Y VENTAS DEL PERIODO (' . count($d['pedidos']) . ')', 'F2', 10.5, [0.067, 0.067, 0.067]);
        $y -= 10;

        $ops[] = "0.067 0.067 0.067 rg 30 " . ($y - 16) . " 535 18 re f";
        $ops[] = $this->pdfText(35, $y - 11, 'N° PEDIDO', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(95, $y - 11, 'FECHA / HORA', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(175, $y - 11, 'CLIENTE', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(310, $y - 11, 'CIUDAD', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(380, $y - 11, 'METODO PAGO', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(455, $y - 11, 'TOTAL (COP)', 'F2', 8, [0.902, 0.631, 0.000]);
        $ops[] = $this->pdfText(515, $y - 11, 'ESTADO', 'F2', 8, [0.902, 0.631, 0.000]);
        $y -= 16;

        if (count($d['pedidos']) === 0) {
            $ops[] = "0.88 0.91 0.94 RG 0.4 w 30 " . ($y - 18) . " 535 19 re S";
            $ops[] = $this->pdfText(40, $y - 12, 'No se registraron pedidos dentro del periodo y filtro seleccionado.', 'F1', 8.5, [0.4, 0.45, 0.52]);
            $y -= 19;
        } else {
            $rowAlt = 0;
            foreach ($d['pedidos'] as $pedido) {
                if ($y < 65) {
                    $startNewPage();
                    $y = 735;
                }
                if ($rowAlt % 2 === 1) {
                    $ops[] = "0.973 0.980 0.988 rg 30 " . ($y - 15) . " 535 16 re f";
                }
                $ops[] = "0.88 0.91 0.94 RG 0.4 w 30 " . ($y - 15) . " 535 16 re S";

                $numPed = (string) ($pedido->numero_pedido ?: ('PED-' . (1000 + $pedido->id_pedido)));
                $fecPed = (string) (optional($pedido->fecha_pedido)->format('d/m/Y H:i') ?? '-');
                $cliPed = mb_strimwidth((string) (optional($pedido->usuario)->nombre_completo ?? 'Cliente'), 0, 24, '...');
                $ciuPed = mb_strimwidth((string) ($pedido->ciudad_entrega ?? 'Bogotá'), 0, 13, '...');
                $metPed = mb_strimwidth((string) ($pedido->metodo_pago ?? 'Contraentrega'), 0, 14, '...');
                $totPed = '$ ' . number_format((float) $pedido->total, 0, ',', '.');
                $estPed = (string) ($pedido->estado_pedido ?? 'Pendiente');

                $ops[] = $this->pdfText(35, $y - 10, $numPed, 'F2', 8, [0.07, 0.07, 0.07]);
                $ops[] = $this->pdfText(95, $y - 10, $fecPed, 'F1', 7.8, [0.3, 0.35, 0.42]);
                $ops[] = $this->pdfText(175, $y - 10, $cliPed, 'F1', 8, [0.07, 0.07, 0.07]);
                $ops[] = $this->pdfText(310, $y - 10, $ciuPed, 'F1', 7.8, [0.25, 0.3, 0.38]);
                $ops[] = $this->pdfText(380, $y - 10, $metPed, 'F1', 7.8, [0.25, 0.3, 0.38]);
                $ops[] = $this->pdfText(455, $y - 10, $totPed, 'F2', 8, [0.02, 0.47, 0.34]);
                $ops[] = $this->pdfText(515, $y - 10, $estPed, 'F2', 7.8, [0.07, 0.07, 0.07]);

                $y -= 16;
                $rowAlt++;
            }
        }

        if (!empty($ops)) {
            $pages[] = implode("\n", $ops);
        }

        return $this->compilePdfDocument($pages);
    }

    /**
     * Genera una instrucción de texto PDF con codificación WinAnsi (CP1252) para soportar tildes y ñ.
     */
    private function pdfText(float $x, float $y, string $text, string $font = 'F1', float $size = 10, array $rgb = [0, 0, 0]): string
    {
        $winAnsi = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        if ($winAnsi === false) {
            $winAnsi = $text;
        }
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $winAnsi);
        [$r, $g, $b] = $rgb;
        return sprintf("BT /%s %.2f Tf %.3f %.3f %.3f rg %.2f %.2f Td (%s) Tj ET", $font, $size, $r, $g, $b, $x, $y, $escaped);
    }

    /**
     * Ensambla objetos PDF 1.4 con tabla xref válida.
     */
    private function compilePdfDocument(array $pageStreams): string
    {
        $objects = [];
        // 1: Catalog, 2: Pages, 3: Font Helvetica, 4: Font Helvetica-Bold
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

        $kids = [];
        $nextId = 5;

        foreach ($pageStreams as $stream) {
            $pageId = $nextId++;
            $contentId = $nextId++;
            $kids[] = "{$pageId} 0 R";

            $len = strlen($stream);
            $objects[$contentId] = "<< /Length {$len} >>\nstream\n{$stream}\nendstream";
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] "
                . "/Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> "
                . "/Contents {$contentId} 0 R >>";
        }

        $countPages = count($kids);
        $kidsStr = implode(' ', $kids);
        $objects[2] = "<< /Type /Pages /Kids [{$kidsStr}] /Count {$countPages} >>";

        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0 => 0];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $maxId = max(array_keys($objects));
        $totalObjs = $maxId + 1;

        $pdf .= "xref\n0 {$totalObjs}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxId; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }

        $pdf .= "trailer\n<< /Size {$totalObjs} /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    /**
     * Helper privado para unificar la consulta de datos de reporte según filtros.
     */
    private function obtenerDatosReporte(Request $request): array
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

        return compact(
            'fechaInicio',
            'fechaFin',
            'estadoFiltro',
            'pedidos',
            'ventasTotales',
            'totalPedidos',
            'ticketPromedio',
            'unidadesVendidas',
            'ventasPorProducto',
            'todosProductos'
        );
    }
}
