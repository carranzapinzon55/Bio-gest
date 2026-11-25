<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

            // Cabecera BOM UTF-8
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
     * Exportar reporte a formato Excel (.xlsx) real con estilos negro/dorado y formato COP.
     */
    public function exportarExcel(Request $request): StreamedResponse
    {
        $d = $this->obtenerDatosReporte($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte BIO-GEST');

        // Paleta oficial
        $colorNegro = '111111';
        $colorDorado = 'E6A100';
        $colorGrisClaro = 'F8FAFC';
        $colorBorde = 'CBD5E1';

        // 1. Título General
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'TENEBRIOS GOLD · REPORTE EJECUTIVO Y ESTADÍSTICAS BIO-GEST');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB($colorDorado);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorNegro);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Subtítulo
        $sheet->mergeCells('A2:I2');
        $sub = "Período: {$d['fechaInicio']} al {$d['fechaFin']} | Filtro Estado: " . ($d['estadoFiltro'] ?: 'Todos') . " | Generado: " . now()->format('d/m/Y h:i a');
        $sheet->setCellValue('A2', $sub);
        $sheet->getStyle('A2')->getFont()->setSize(9)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('222222');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Tarjetas KPI
        $sheet->setCellValue('A4', 'RESUMEN DE INDICADORES CLAVE (KPIS)');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB($colorNegro);

        $kpis = [
            ['Ventas Totales (COP)', $d['ventasTotales'], '"$"#,##0'],
            ['Total de Pedidos', $d['totalPedidos'], '#,##0'],
            ['Ticket Promedio (COP)', $d['ticketPromedio'], '"$"#,##0'],
            ['Unidades Vendidas', $d['unidadesVendidas'], '#,##0'],
        ];

        $filaKpi = 5;
        foreach ($kpis as [$label, $val, $fmt]) {
            $sheet->setCellValue("A{$filaKpi}", $label);
            $sheet->setCellValue("B{$filaKpi}", $val);
            $sheet->getStyle("A{$filaKpi}")->getFont()->setBold(true);
            $sheet->getStyle("B{$filaKpi}")->getNumberFormat()->setFormatCode($fmt);
            $sheet->getStyle("A{$filaKpi}:B{$filaKpi}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorGrisClaro);
            $sheet->getStyle("A{$filaKpi}:B{$filaKpi}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($colorBorde);
            $filaKpi++;
        }

        // 3. SECCIÓN 1: SALUD Y ROTACIÓN DE INVENTARIO
        $filaInv = $filaKpi + 2;
        $sheet->mergeCells("A{$filaInv}:I{$filaInv}");
        $sheet->setCellValue("A{$filaInv}", '1. SALUD Y ROTACIÓN DE INVENTARIO');
        $sheet->getStyle("A{$filaInv}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB($colorDorado);
        $sheet->getStyle("A{$filaInv}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorNegro);
        $sheet->getRowDimension($filaInv)->setRowHeight(24);

        $filaInvHeader = $filaInv + 1;
        $headersInv = [
            'ID Producto',
            'Nombre del Producto',
            'Categoría',
            'Precio Actual (COP)',
            'Stock Físico',
            'Stock Mínimo',
            'Unidades Vendidas',
            'Total Ingresos (COP)',
            'Rotación'
        ];

        $col = 'A';
        foreach ($headersInv as $headerText) {
            $sheet->setCellValue("{$col}{$filaInvHeader}", $headerText);
            $col++;
        }

        $sheet->getStyle("A{$filaInvHeader}:I{$filaInvHeader}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$filaInvHeader}:I{$filaInvHeader}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('222222');
        $sheet->getStyle("A{$filaInvHeader}:I{$filaInvHeader}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $filaDataInv = $filaInvHeader + 1;
        foreach ($d['todosProductos'] as $prod) {
            $venta    = $d['ventasPorProducto']->get($prod->id_producto);
            $unidades = $venta ? (int) $venta->total_unidades : 0;
            $ingresos = $venta ? (float) $venta->total_ingresos : 0;
            $rotacion = $unidades >= 5 ? 'Alta' : ($unidades >= 1 ? 'Media' : 'Baja');

            $sheet->setCellValue("A{$filaDataInv}", 'PROD-' . str_pad((string) $prod->id_producto, 3, '0', STR_PAD_LEFT));
            $sheet->setCellValue("B{$filaDataInv}", $prod->nombre_producto);
            $sheet->setCellValue("C{$filaDataInv}", $prod->categoria?->nombre_categoria ?? 'General');
            $sheet->setCellValue("D{$filaDataInv}", (float) $prod->precio);
            $sheet->setCellValue("E{$filaDataInv}", (int) $prod->stock);
            $sheet->setCellValue("F{$filaDataInv}", (int) ($prod->stock_minimo ?? 0));
            $sheet->setCellValue("G{$filaDataInv}", $unidades);
            $sheet->setCellValue("H{$filaDataInv}", $ingresos);
            $sheet->setCellValue("I{$filaDataInv}", $rotacion);

            $sheet->getStyle("D{$filaDataInv}")->getNumberFormat()->setFormatCode('"$"#,##0');
            $sheet->getStyle("H{$filaDataInv}")->getNumberFormat()->setFormatCode('"$"#,##0');

            if ($filaDataInv % 2 === 0) {
                $sheet->getStyle("A{$filaDataInv}:I{$filaDataInv}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorGrisClaro);
            }
            $sheet->getStyle("A{$filaDataInv}:I{$filaDataInv}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($colorBorde);

            $filaDataInv++;
        }

        // 4. SECCIÓN 2: DETALLE DE PEDIDOS
        $filaPed = $filaDataInv + 2;
        $sheet->mergeCells("A{$filaPed}:K{$filaPed}");
        $sheet->setCellValue("A{$filaPed}", '2. DETALLE DE VENTAS Y PEDIDOS EN EL PERÍODO');
        $sheet->getStyle("A{$filaPed}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB($colorDorado);
        $sheet->getStyle("A{$filaPed}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorNegro);
        $sheet->getRowDimension($filaPed)->setRowHeight(24);

        $filaPedHeader = $filaPed + 1;
        $headersPed = [
            'Número Pedido',
            'Fecha y Hora',
            'Cliente',
            'Correo Electrónico',
            'Ciudad Entrega',
            'Subtotal (COP)',
            'Costo Envío (COP)',
            'Total (COP)',
            'Método Pago',
            'Estado Pago',
            'Estado Pedido'
        ];

        $col = 'A';
        foreach ($headersPed as $headerText) {
            $sheet->setCellValue("{$col}{$filaPedHeader}", $headerText);
            $col++;
        }

        $sheet->getStyle("A{$filaPedHeader}:K{$filaPedHeader}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$filaPedHeader}:K{$filaPedHeader}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('222222');
        $sheet->getStyle("A{$filaPedHeader}:K{$filaPedHeader}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $filaDataPed = $filaPedHeader + 1;
        foreach ($d['pedidos'] as $p) {
            $sheet->setCellValue("A{$filaDataPed}", $p->numero_pedido ?: ('PED-' . (1000 + $p->id_pedido)));
            $sheet->setCellValue("B{$filaDataPed}", optional($p->fecha_pedido)->format('Y-m-d H:i:s'));
            $sheet->setCellValue("C{$filaDataPed}", optional($p->usuario)->nombre_completo ?? 'Cliente');
            $sheet->setCellValue("D{$filaDataPed}", optional($p->usuario)->correo ?? 'N/A');
            $sheet->setCellValue("E{$filaDataPed}", $p->ciudad_entrega ?? 'Bogotá D.C.');
            $sheet->setCellValue("F{$filaDataPed}", (float) $p->subtotal);
            $sheet->setCellValue("G{$filaDataPed}", (float) $p->costo_envio);
            $sheet->setCellValue("H{$filaDataPed}", (float) $p->total);
            $sheet->setCellValue("I{$filaDataPed}", $p->metodo_pago ?? 'Contraentrega');
            $sheet->setCellValue("J{$filaDataPed}", $p->estado_pago ?? 'Aprobado');
            $sheet->setCellValue("K{$filaDataPed}", $p->estado_pedido ?? 'Completado');

            $sheet->getStyle("F{$filaDataPed}")->getNumberFormat()->setFormatCode('"$"#,##0');
            $sheet->getStyle("G{$filaDataPed}")->getNumberFormat()->setFormatCode('"$"#,##0');
            $sheet->getStyle("H{$filaDataPed}")->getNumberFormat()->setFormatCode('"$"#,##0');

            if ($filaDataPed % 2 === 0) {
                $sheet->getStyle("A{$filaDataPed}:K{$filaDataPed}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorGrisClaro);
            }
            $sheet->getStyle("A{$filaDataPed}:K{$filaDataPed}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($colorBorde);

            $filaDataPed++;
        }

        // Auto-ajustar ancho de todas las columnas (A a K)
        foreach (range('A', 'K') as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $fileName = "reporte_ejecutivo_biogest_{$d['fechaInicio']}_al_{$d['fechaFin']}.xlsx";
        $headers = [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control'       => 'max-age=0',
        ];

        return response()->stream(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, $headers);
    }

    /**
     * Exportar reporte ejecutivo en PDF oficial con DomPDF.
     */
    public function exportarPdf(Request $request): Response
    {
        $d = $this->obtenerDatosReporte($request);

        // Logo en base64 para renderizado offline garantizado en DomPDF
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

        $pdf = Pdf::loadView('admin.estadisticas.pdf_report', $data)
            ->setPaper('a4', 'portrait')
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        $fileName = "reporte_ejecutivo_biogest_{$d['fechaInicio']}_al_{$d['fechaFin']}.pdf";

        return $pdf->download($fileName);
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
