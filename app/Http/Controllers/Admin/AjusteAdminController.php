<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BitacoraSeguridad;
use App\Models\Configuracion;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AjusteAdminController extends Controller
{
    /**
     * Pantalla de Mi Perfil del Administrador Autenticado.
     */
    public function perfil(): View
    {
        $admin = auth()->user();
        return view('admin.perfil.index', compact('admin'));
    }

    /**
     * Actualiza los datos personales, correo y contraseña del Administrador Autenticado.
     */
    public function updatePerfil(Request $request): RedirectResponse
    {
        $admin = auth()->user();

        $datos = $request->validate([
            'nombre'     => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'apellido'   => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'correo'     => [
                'required',
                'email',
                'max:150',
                Rule::unique('usuario', 'correo')->ignore($admin->id_usuario, 'id_usuario'),
            ],
            'telefono'   => ['nullable', 'string', 'regex:/^[0-9]{7,10}$/'],
            'contrasena' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[A-Za-zÁÉÍÓÚÜÑáéíóúüñ])(?=.*\d).+$/', 'confirmed'],
        ], [
            'nombre.required'      => 'El nombre es obligatorio.',
            'nombre.min'           => 'El nombre debe tener al menos 2 letras.',
            'nombre.regex'         => 'El nombre solo puede contener letras y espacios (sin números).',
            'apellido.required'    => 'El apellido es obligatorio.',
            'apellido.min'         => 'El apellido debe tener al menos 2 letras.',
            'apellido.regex'       => 'El apellido solo puede contener letras y espacios (sin números).',
            'correo.required'      => 'El correo electrónico es obligatorio.',
            'correo.email'         => 'Ingresa un correo electrónico válido.',
            'correo.unique'        => 'Ya existe otra cuenta registrada con este correo electrónico.',
            'telefono.regex'       => 'El teléfono debe contener únicamente números (entre 7 y 10 dígitos).',
            'contrasena.min'       => 'La nueva contraseña debe tener mínimo 8 caracteres.',
            'contrasena.regex'     => 'La nueva contraseña debe incluir al menos una letra y al menos un número.',
            'contrasena.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $admin->nombre   = trim($datos['nombre']);
        $admin->apellido = trim($datos['apellido']);
        $admin->correo   = strtolower(trim($datos['correo']));
        $admin->telefono = !empty($datos['telefono']) ? trim($datos['telefono']) : null;

        if (!empty($datos['contrasena'])) {
            $admin->contrasena = $datos['contrasena'];
        }

        $admin->save();

        BitacoraSeguridad::registrar(
            $admin->id_usuario,
            'Actualización de Perfil Administrador',
            "El administrador {$admin->nombre_completo} ({$admin->correo}) actualizó sus datos de perfil."
        );

        return redirect()->route('admin.perfil.index')
            ->with('success', 'Tus datos de perfil de administrador fueron actualizados correctamente.');
    }

    /**
     * Pantalla de Ajustes del Sistema y Parámetros Comerciales BIO-GEST.
     */
    public function index(): View
    {
        $ajustes = [
            'costo_envio_bogota'   => (float) Configuracion::get('costo_envio_bogota', 10000),
            'costo_envio_nacional' => (float) Configuracion::get('costo_envio_nacional', 18000),
            'monto_envio_gratis'   => (float) Configuracion::get('monto_envio_gratis', 122900),
            'porcentaje_iva'       => (float) Configuracion::get('porcentaje_iva', 19),
            'whatsapp_soporte'     => Configuracion::get('whatsapp_soporte', '+57 310 000 0000'),
            'alerta_stock_bajo'    => (int) Configuracion::get('alerta_stock_bajo', 1),
            'alerta_nuevo_pedido'  => (int) Configuracion::get('alerta_nuevo_pedido', 1),
        ];

        $admin = auth()->user();

        return view('admin.ajustes.index', compact('ajustes', 'admin'));
    }

    /**
     * Guarda los parámetros comerciales y opcionalmente actualiza el perfil del administrador.
     */
    public function update(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'costo_envio_bogota'   => ['required', 'numeric', 'gte:0'],
            'costo_envio_nacional' => ['required', 'numeric', 'gte:0'],
            'monto_envio_gratis'   => ['required', 'numeric', 'gte:0'],
            'porcentaje_iva'       => ['required', 'numeric', 'gte:0', 'lte:100'],
            'whatsapp_soporte'     => ['required', 'string', 'max:30'],
            'alerta_stock_bajo'    => ['nullable', 'boolean'],
            'alerta_nuevo_pedido'  => ['nullable', 'boolean'],

            // Datos de perfil de admin (opcional)
            'telefono_admin'       => ['nullable', 'string', 'max:20'],
            'contrasena_nueva'     => ['nullable', 'string', 'min:6'],
        ], [
            'costo_envio_bogota.required'   => 'El costo de envío a Bogotá es obligatorio.',
            'costo_envio_nacional.required' => 'El costo de envío nacional es obligatorio.',
            'monto_envio_gratis.required'   => 'El monto de envío gratis es obligatorio.',
            'porcentaje_iva.required'       => 'El porcentaje de IVA es obligatorio.',
            'whatsapp_soporte.required'     => 'El número de WhatsApp de soporte es obligatorio.',
        ]);

        Configuracion::set('costo_envio_bogota', $datos['costo_envio_bogota'], 'comercial', 'Costo de envío urbano para Bogotá');
        Configuracion::set('costo_envio_nacional', $datos['costo_envio_nacional'], 'comercial', 'Costo de envío nacional');
        Configuracion::set('monto_envio_gratis', $datos['monto_envio_gratis'], 'comercial', 'Monto de compra para envío gratis');
        Configuracion::set('porcentaje_iva', $datos['porcentaje_iva'], 'comercial', 'Porcentaje de IVA');
        Configuracion::set('whatsapp_soporte', $datos['whatsapp_soporte'], 'soporte', 'Línea de atención WhatsApp');
        Configuracion::set('alerta_stock_bajo', $request->has('alerta_stock_bajo') ? '1' : '0', 'notificaciones', 'Alerta stock bajo');
        Configuracion::set('alerta_nuevo_pedido', $request->has('alerta_nuevo_pedido') ? '1' : '0', 'notificaciones', 'Alerta nuevo pedido');

        // Actualizar datos del admin si aplica
        if (auth()->check()) {
            $user = auth()->user();
            if ($request->filled('telefono_admin')) {
                $user->telefono = $request->input('telefono_admin');
            }
            if ($request->filled('contrasena_nueva')) {
                $user->contrasena = $request->input('contrasena_nueva');
            }
            $user->save();
        }

        BitacoraSeguridad::registrar(
            auth()->id(),
            'Actualización de Parámetros Comerciales',
            'Se modificaron las tarifas de envío, IVA y parámetros de soporte en BIO-GEST.'
        );

        return redirect()->route('admin.ajustes.index')
            ->with('success', 'Ajustes comerciales y parámetros del sistema guardados exitosamente en MySQL.');
    }

    /**
     * Herramienta no técnica: Verificar estado de la tienda y optimizar/limpiar caché del sistema.
     */
    public function limpiarCacheTienda(): RedirectResponse
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            BitacoraSeguridad::registrar(
                auth()->id(),
                'Mantenimiento de Tienda',
                'El administrador ejecutó la verificación de estado y limpieza de caché del sistema.'
            );
            return redirect()->route('admin.ajustes.index')
                ->with('success', '¡Estado de la tienda verificado con éxito! Caché de rutas, vistas y configuración optimizada correctamente.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.ajustes.index')
                ->with('error', 'Error al optimizar la tienda: ' . $e->getMessage());
        }
    }

    /**
     * Herramienta no técnica: Descargar resumen general del catálogo de productos y precios en formato CSV amigable para Excel.
     */
    public function descargarCatalogoPrecios(): StreamedResponse
    {
        $fecha = now()->format('Y-m-d');
        $fileName = "catalogo_precios_tenebrios_gold_{$fecha}.csv";
        $productos = \App\Models\Producto::with('categoria')->orderBy('id_producto')->get();

        BitacoraSeguridad::registrar(
            auth()->id(),
            'Descarga de Catálogo y Precios',
            "Descarga del resumen de catálogo y precios ({$productos->count()} referencias)."
        );

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($productos, $fecha) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['TENEBRIOS GOLD · LISTADO OFICIAL DE PRODUCTOS Y PRECIOS'], ';', '"', "\\");
            fputcsv($handle, ["Generado el: {$fecha} | Total productos: " . $productos->count()], ';', '"', "\\");
            fputcsv($handle, [], ';', '"', "\\");

            fputcsv($handle, [
                'ID Referencia',
                'Nombre del Producto',
                'Categoría',
                'Precio Comercial (COP)',
                'Stock Disponible (Uds)',
                'Stock Mínimo (Alerta)',
                'Estado en Tienda',
                'Descripción'
            ], ';', '"', "\\");

            foreach ($productos as $p) {
                fputcsv($handle, [
                    'PROD-' . str_pad((string)$p->id_producto, 3, '0', STR_PAD_LEFT),
                    $p->nombre_producto,
                    $p->categoria?->nombre_categoria ?? 'General',
                    number_format((float) $p->precio, 0, ',', '.'),
                    $p->stock,
                    $p->stock_minimo ?? 10,
                    $p->estado,
                    $p->descripcion ?? 'Sin descripción',
                ], ';', '"', "\\");
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Genera y descarga una copia de respaldo completa (.sql) de la base de datos MySQL.
     */
    public function descargarRespaldoSql(): StreamedResponse
    {
        $dbName = config('database.connections.mysql.database', 'bio_gest');
        $fileName = 'respaldo_biogest_' . now()->format('Y_m_d_His') . '.sql';

        BitacoraSeguridad::registrar(
            auth()->id(),
            'Descarga de Respaldo Base de Datos',
            "Generación y descarga de archivo de respaldo .sql de '{$dbName}'."
        );

        $headers = [
            'Content-Type'        => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($dbName) {
            $out = fopen('php://output', 'w');

            // Encabezado institucional
            fwrite($out, "-- =====================================================================\n");
            fwrite($out, "-- TENEBRIOS GOLD · BIO-GEST - COPIA DE SEGURIDAD MYSQL OFICIAL\n");
            fwrite($out, "-- Base de datos: {$dbName}\n");
            fwrite($out, "-- Generado el: " . now()->format('Y-m-d H:i:s') . "\n");
            fwrite($out, "-- Generado por usuario ID: " . (auth()->id() ?? '1') . " (" . (auth()->user()->correo ?? 'admin@tenebriosgold.com') . ")\n");
            fwrite($out, "-- =====================================================================\n\n");
            fwrite($out, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($out, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($out, "SET time_zone = \"-05:00\";\n\n");

            // Obtener todas las tablas
            $tablesRows = DB::select('SHOW TABLES');
            $tables = [];
            foreach ($tablesRows as $tr) {
                $trArr = (array) $tr;
                $tables[] = reset($trArr);
            }

            foreach ($tables as $table) {
                fwrite($out, "\n-- -------------------------------------------------------------\n");
                fwrite($out, "-- Estructura de tabla para `{$table}`\n");
                fwrite($out, "-- -------------------------------------------------------------\n");
                fwrite($out, "DROP TABLE IF EXISTS `{$table}`;\n");

                $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                $createArr = (array) $createTable[0];
                $createSql = $createArr['Create Table'] ?? '';
                fwrite($out, $createSql . ";\n\n");

                // Datos de la tabla
                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    fwrite($out, "-- Volcado de datos para `{$table}` (" . $rows->count() . " registros)\n");

                    $chunks = $rows->chunk(50);
                    foreach ($chunks as $chunk) {
                        $insertValues = [];
                        foreach ($chunk as $row) {
                            $rowArray = (array) $row;
                            $escapedValues = array_map(function ($val) {
                                if (is_null($val)) {
                                    return 'NULL';
                                }
                                return "'" . addslashes((string) $val) . "'";
                            }, $rowArray);
                            $insertValues[] = "(" . implode(', ', $escapedValues) . ")";
                        }

                        $firstRowCols = array_keys((array) $chunk->first());
                        $colsSql = "`" . implode('`, `', $firstRowCols) . "`";

                        fwrite($out, "INSERT INTO `{$table}` ({$colsSql}) VALUES\n" . implode(",\n", $insertValues) . ";\n");
                    }
                    fwrite($out, "\n");
                }
            }

            fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
            fwrite($out, "-- Fin del respaldo BIO-GEST\n");
            fclose($out);
        }, 200, $headers);
    }
}
