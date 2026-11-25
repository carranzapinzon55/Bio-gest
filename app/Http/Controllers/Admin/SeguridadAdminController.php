<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BitacoraSeguridad;
use App\Models\Configuracion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeguridadAdminController extends Controller
{
    /**
     * Muestra el panel de Seguridad, Roles y Políticas editables.
     */
    public function index(): View
    {
        // 1. Roles fijos (Administrador y Cliente) con conteo real de usuarios
        $totalAdmin = Usuario::where('id_rol', 1)->count();
        $totalClientes = Usuario::where('id_rol', 2)->count();

        $rolesData = [
            [
                'id_rol'       => 1,
                'nombre'       => 'Administrador',
                'badge'        => 'Control Total',
                'badge_color'  => '#111111',
                'badge_text'   => '#e6a100',
                'usuarios_count' => $totalAdmin,
                'descripcion'  => 'Acceso maestro a la gestión de productos, inventario, pedidos, analítica, usuarios y ajustes comerciales de la empresa.',
                'capacidades'  => [
                    'Gestión integral de catálogo y stock',
                    'Aprobación y despacho de pedidos comerciales',
                    'Creación de nuevas cuentas administrativas',
                    'Activación y bloqueo preventivo de usuarios',
                    'Configuración de tarifas de envío e impuestos',
                    'Auditoría y consulta de bitácora del sistema',
                ],
            ],
            [
                'id_rol'       => 2,
                'nombre'       => 'Cliente',
                'badge'        => 'Autoservicio',
                'badge_color'  => '#f3f4f6',
                'badge_text'   => '#374151',
                'usuarios_count' => $totalClientes,
                'descripcion'  => 'Compradores registrados en la plataforma para adquisición de alimento vivo y pie de cría.',
                'capacidades'  => [
                    'Registro autónomo desde la tienda virtual',
                    'Exploración de productos y calculadora nutricional',
                    'Finalización de compra y pagos en línea',
                    'Seguimiento en tiempo real de sus pedidos',
                    'Gestión de libreta de direcciones personales',
                    'Canales de soporte y atención por chatbot',
                ],
            ],
        ];

        // 2. Políticas de Seguridad, Privacidad y Uso cargadas de la tabla configuracion
        $politicas = [
            'politica_contrasenas_sesiones' => Configuracion::get(
                'politica_contrasenas_sesiones',
                "• Longitud mínima de contraseña: 6 a 8 caracteres incluyendo mayúsculas y caracteres numéricos.\n• Expiración automática de sesiones inactivas tras 120 minutos.\n• Cifrado obligatorio de credenciales mediante Bcrypt / Argon2.\n• Bloqueo preventivo de sesión ante múltiples intentos erróneos."
            ),
            'politica_datos_personales' => Configuracion::get(
                'politica_datos_personales',
                "• Cumplimiento estricto de la Ley 1581 de 2012 (Habeas Data Colombia).\n• Los datos de compradores (dirección, teléfono, correo) son de uso exclusivo para logística de entrega y facturación.\n• Queda prohibida la edición o alteración arbitraria de información de clientes por parte de terceros.\n• Canales habilitados para solicitud de rectificación o supresión de datos."
            ),
            'politica_garantia_reembolso' => Configuracion::get(
                'politica_garantia_reembolso',
                "• Garantía del 100% de llegada viva en todos los envíos nacionales de Tenebrio molitor.\n• Para hacer efectiva la reposición, el cliente debe remitir video continuo de apertura de la caja dentro de las 2 horas siguientes a la recepción.\n• Se realiza despacho de reemplazo prioritario sin costo adicional de flete."
            ),
            'politica_control_acceso' => Configuracion::get(
                'politica_control_acceso',
                "• Modelo estricto de dos roles: Administrador (id_rol=1) y Cliente (id_rol=2).\n• Administrador: acceso total a gestión de catálogo, inventario, pedidos, analítica, ajustes y soporte.\n• Cliente: autoservicio en tienda pública, carrito, pago seguro y seguimiento de sus pedidos.\n• Separación de privilegios sin roles intermedios no autorizados."
            ),
        ];

        // 3. Bitácora reciente de accesos y auditoría de cuentas
        $bitacora = BitacoraSeguridad::with('usuario')
            ->orderByDesc('fecha')
            ->take(15)
            ->get();

        return view('admin.seguridad.index', compact('rolesData', 'politicas', 'bitacora'));
    }

    /**
     * Actualiza las políticas en la tabla `configuracion`.
     */
    public function updatePoliticas(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'politica_contrasenas_sesiones' => ['required', 'string', 'max:5000'],
            'politica_datos_personales'     => ['required', 'string', 'max:5000'],
            'politica_garantia_reembolso'   => ['required', 'string', 'max:5000'],
            'politica_control_acceso'       => ['required', 'string', 'max:5000'],
        ], [
            'politica_contrasenas_sesiones.required' => 'La política de contraseñas y sesiones es obligatoria.',
            'politica_datos_personales.required'     => 'La política de datos personales es obligatoria.',
            'politica_garantia_reembolso.required'   => 'La política de garantía y reembolsos es obligatoria.',
            'politica_control_acceso.required'       => 'El control de acceso por rol es obligatorio.',
        ]);

        Configuracion::set(
            'politica_contrasenas_sesiones',
            $datos['politica_contrasenas_sesiones'],
            'seguridad',
            'Política institucional de contraseñas y sesiones activas'
        );

        Configuracion::set(
            'politica_datos_personales',
            $datos['politica_datos_personales'],
            'seguridad',
            'Protección de datos personales y privacidad del cliente'
        );

        Configuracion::set(
            'politica_garantia_reembolso',
            $datos['politica_garantia_reembolso'],
            'seguridad',
            'Términos de garantía de llegada viva y reembolsos'
        );

        Configuracion::set(
            'politica_control_acceso',
            $datos['politica_control_acceso'],
            'seguridad',
            'Control de acceso por roles y principio de mínimo privilegio'
        );

        BitacoraSeguridad::registrar(
            auth()->id(),
            'Actualización de Políticas',
            'El administrador actualizó los términos y políticas institucionales de BIO-GEST.'
        );

        return redirect()->route('admin.seguridad.index')
            ->with('success', 'Las políticas de seguridad, privacidad y uso se actualizaron exitosamente en MySQL.');
    }
}
