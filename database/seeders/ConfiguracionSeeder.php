<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracion;
use App\Models\BitacoraSeguridad;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Parámetros comerciales BIO-GEST
        $configuraciones = [
            [
                'clave'       => 'costo_envio_bogota',
                'valor'       => '10000',
                'categoria'   => 'comercial',
                'descripcion' => 'Costo de envío urbano para Bogotá D.C. en pesos colombianos (COP)',
            ],
            [
                'clave'       => 'costo_envio_nacional',
                'valor'       => '18000',
                'categoria'   => 'comercial',
                'descripcion' => 'Costo de envío para cobertura nacional en pesos colombianos (COP)',
            ],
            [
                'clave'       => 'monto_envio_gratis',
                'valor'       => '122900',
                'categoria'   => 'comercial',
                'descripcion' => 'Monto mínimo de compra para envío 100% gratuito',
            ],
            [
                'clave'       => 'porcentaje_iva',
                'valor'       => '19',
                'categoria'   => 'comercial',
                'descripcion' => 'Porcentaje legal del IVA aplicable en Colombia',
            ],
            [
                'clave'       => 'whatsapp_soporte',
                'valor'       => '+57 310 000 0000',
                'categoria'   => 'soporte',
                'descripcion' => 'Línea directa de atención al cliente y pedidos por WhatsApp',
            ],
            [
                'clave'       => 'alerta_stock_bajo',
                'valor'       => '1',
                'categoria'   => 'notificaciones',
                'descripcion' => 'Notificaciones activas cuando el inventario llegue al nivel mínimo',
            ],
            [
                'clave'       => 'alerta_nuevo_pedido',
                'valor'       => '1',
                'categoria'   => 'notificaciones',
                'descripcion' => 'Notificaciones sonoras y visuales al registrarse un nuevo pedido',
            ],

            // 2. Políticas editables de Seguridad y Privacidad (Req 5)
            [
                'clave'       => 'politica_contrasenas_sesiones',
                'valor'       => "• Longitud mínima de contraseña: 6 a 8 caracteres incluyendo mayúsculas y caracteres numéricos.\n• Expiración automática de sesiones inactivas tras 120 minutos.\n• Cifrado obligatorio de credenciales mediante Bcrypt / Argon2.\n• Bloqueo preventivo de sesión ante múltiples intentos erróneos.",
                'categoria'   => 'seguridad',
                'descripcion' => 'Política institucional de contraseñas y sesiones activas',
            ],
            [
                'clave'       => 'politica_datos_personales',
                'valor'       => "• Cumplimiento estricto de la Ley 1581 de 2012 (Habeas Data Colombia).\n• Los datos de compradores (dirección, teléfono, correo) son de uso exclusivo para logística de entrega y facturación.\n• Queda prohibida la edición o alteración arbitraria de información de clientes por parte de terceros.\n• Canales habilitados para solicitud de rectificación o supresión de datos.",
                'categoria'   => 'seguridad',
                'descripcion' => 'Protección de datos personales y privacidad del cliente',
            ],
            [
                'clave'       => 'politica_garantia_reembolso',
                'valor'       => "• Garantía del 100% de llegada viva en todos los envíos nacionales de Tenebrio molitor.\n• Para hacer efectiva la reposición, el cliente debe remitir video continuo de apertura de la caja dentro de las 2 horas siguientes a la recepción.\n• Se realiza despacho de reemplazo prioritario sin costo adicional de flete.",
                'categoria'   => 'seguridad',
                'descripcion' => 'Términos de garantía de llegada viva y reembolsos',
            ],
            [
                'clave'       => 'politica_control_acceso',
                'valor'       => "• Modelo estricto de dos roles: Administrador (id_rol=1) y Cliente (id_rol=2).\n• Administrador: acceso total a gestión de catálogo, inventario, pedidos, analítica, ajustes y soporte.\n• Cliente: autoservicio en tienda pública, carrito, pago seguro y seguimiento de sus pedidos.\n• Separación de privilegios sin roles intermedios no autorizados.",
                'categoria'   => 'seguridad',
                'descripcion' => 'Control de acceso por roles y principio de mínimo privilegio',
            ],
        ];

        foreach ($configuraciones as $item) {
            Configuracion::updateOrCreate(
                ['clave' => $item['clave']],
                [
                    'valor'       => $item['valor'],
                    'categoria'   => $item['categoria'],
                    'descripcion' => $item['descripcion'],
                ]
            );
        }

        // 3. Bitácora de seguridad inicial
        $admin = Usuario::where('correo', 'admin@tenebriosgold.com')->first();
        if ($admin && BitacoraSeguridad::count() === 0) {
            BitacoraSeguridad::create([
                'id_usuario'  => $admin->id_usuario,
                'accion'      => 'Inicio de sesión exitoso',
                'descripcion' => 'Autenticación mediante credenciales maestras en panel admin',
                'ip_origen'   => '127.0.0.1',
                'fecha'       => now()->subHours(2),
            ]);

            BitacoraSeguridad::create([
                'id_usuario'  => $admin->id_usuario,
                'accion'      => 'Ajuste de parámetros del sistema',
                'descripcion' => 'Actualización de costos de envío y umbral de gratuidad',
                'ip_origen'   => '127.0.0.1',
                'fecha'       => now()->subHour(),
            ]);

            BitacoraSeguridad::create([
                'id_usuario'  => $admin->id_usuario,
                'accion'      => 'Verificación de políticas de seguridad',
                'descripcion' => 'Auditoría de cumplimiento de roles HU-02 / RF-002',
                'ip_origen'   => '127.0.0.1',
                'fecha'       => now()->subMinutes(30),
            ]);
        }

        // 4. Mensajes de contacto y chat de ejemplo si están vacíos
        if (DB::table('mensaje_contacto')->count() === 0) {
            DB::table('mensaje_contacto')->insert([
                [
                    'nombre'           => 'Juan Camilo Pérez',
                    'correo'           => 'juan.perez@zoovet.com',
                    'asunto'           => 'Cotización de 10.000 larvas para criadero de aves',
                    'mensaje'          => 'Buenas tardes, requerimos suministro continuo de 10.000 larvas quincenales para nuestro criadero en Cundinamarca. ¿Manejan descuento institucional?',
                    'estado'           => 'Pendiente',
                    'respuesta_admin'  => null,
                    'fecha_atencion'   => null,
                    'fecha_envio'      => now()->subDays(1),
                ],
                [
                    'nombre'           => 'Dra. Andrea Morales',
                    'correo'           => 'andrea.morales@zooclinic.co',
                    'asunto'           => 'Consulta sobre conservación de pie de cría',
                    'mensaje'          => 'Hola, adquirimos un kit de pie de cría y quisiéramos confirmar si el sustrato de salvado ya viene desparasitado térmicamente.',
                    'estado'           => 'Atendido',
                    'respuesta_admin'  => 'Estimada Dra. Andrea, efectivamente nuestro salvado de trigo pasa por tratamiento térmico previo para garantizar cero ácaros.',
                    'fecha_atencion'   => now()->subHours(5),
                    'fecha_envio'      => now()->subDays(2),
                ],
            ]);
        }

        $cliente = Usuario::where('correo', 'cliente@tenebriosgold.com')->first();
        if ($cliente && DB::table('chat_mensaje')->count() === 0) {
            DB::table('chat_mensaje')->insert([
                [
                    'id_usuario'  => $cliente->id_usuario,
                    'mensaje'     => 'Hola, ¿tienen stock disponible del paquete de 1.000 larvas vivas?',
                    'es_admin'    => false,
                    'id_admin'    => null,
                    'fecha_envio' => now()->subHours(3),
                ],
                [
                    'id_usuario'  => $cliente->id_usuario,
                    'mensaje'     => '¡Hola! Sí, tenemos stock fresco para despacho inmediato hoy mismo.',
                    'es_admin'    => true,
                    'id_admin'    => $admin?->id_usuario,
                    'fecha_envio' => now()->subHours(2)->subMinutes(50),
                ],
            ]);
        }
    }
}
