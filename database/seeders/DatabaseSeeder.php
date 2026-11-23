<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\DireccionUsuario;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Promocion;
use App\Models\Faq;
use App\Models\PermisoRol;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $rolAdmin = Rol::create([
            'nombre_rol'  => 'Administrador',
            'descripcion' => 'Acceso total a la administración y gestión del sistema'
        ]);

        $rolCliente = Rol::create([
            'nombre_rol'  => 'Cliente',
            'descripcion' => 'Acceso al catálogo, compras, carrito y seguimiento de pedidos'
        ]);

        // 2. Usuarios con contraseñas cifradas vía Hash::make
        $admin = Usuario::create([
            'id_rol'         => $rolAdmin->id_rol,
            'nombre'         => 'Administrador',
            'apellido'       => 'BIO-GEST',
            'correo'         => 'admin@tenebriosgold.com',
            'telefono'       => '3001234567',
            'contrasena'     => Hash::make('Admin123*'),
            'perfil_uso'     => 'Proyecto comercial / Gran escala',
            'estado'         => 'Activo',
            'fecha_registro' => now(),
        ]);

        $cliente = Usuario::create([
            'id_rol'         => $rolCliente->id_rol,
            'nombre'         => 'Cliente',
            'apellido'       => 'Tenebrios Gold',
            'correo'         => 'cliente@tenebriosgold.com',
            'telefono'       => '3109876543',
            'contrasena'     => Hash::make('Cliente123*'),
            'perfil_uso'     => 'Alimentación continua',
            'estado'         => 'Activo',
            'fecha_registro' => now(),
        ]);

        // 3. Direcciones
        $dirAdmin = DireccionUsuario::create([
            'id_usuario'       => $admin->id_usuario,
            'nombre_ubicacion' => 'Sede Principal Bio-Gest',
            'direccion'        => 'Calle 26 # 68-90, Suba',
            'ciudad'           => 'Bogotá',
            'departamento'     => 'Cundinamarca',
            'codigo_postal'    => '111111',
            'es_principal'     => true,
        ]);

        $dirCliente = DireccionUsuario::create([
            'id_usuario'       => $cliente->id_usuario,
            'nombre_ubicacion' => 'Casa',
            'direccion'        => 'Carrera 15 # 45-20, Chapinero',
            'ciudad'           => 'Bogotá',
            'departamento'     => 'Cundinamarca',
            'codigo_postal'    => '110011',
            'es_principal'     => true,
        ]);

        // 4. Categorías
        $catPaquetes = Categoria::create([
            'nombre_categoria' => 'Paquetes de Larvas',
            'descripcion'      => 'Larvas vivas de Tenebrio Molitor de alta calidad con garantía de llegada viva'
        ]);

        $catAbono = Categoria::create([
            'nombre_categoria' => 'Abono Orgánico',
            'descripcion'      => 'Frass (excremento de tenebrio) ideal como bioestimulante agrícola y enmienda orgánica'
        ]);

        $catKits = Categoria::create([
            'nombre_categoria' => 'Kits de Cría',
            'descripcion'      => 'Insumos, contenedores y accesorios completos para la cría y reproducción doméstica'
        ]);

        // 5. Los 5 Paquetes de Tenebrios Gold
        $p1 = Producto::create([
            'id_categoria'               => $catPaquetes->id_categoria,
            'nombre_producto'            => 'Paquete Inicial – 500 larvas',
            'descripcion'                => 'Ideal para probar o si tienes una mascota. Las que no uses las guardas en nevera, hibernan hasta 2 meses y medio.',
            'beneficios'                 => 'Alta proteína digestiva, ideal para reptiles y aves pequeñas.',
            'instrucciones_conservacion' => 'Refrigerar entre 8°C y 12°C en sustrato de salvado.',
            'especies_recomendadas'      => 'Aves pequeñas, reptiles, erizos, peces de acuario.',
            'politica_reembolso'         => 'Garantía 100% de llegada viva documentando desempaque.',
            'precio'                     => 34900.00,
            'stock'                      => 50,
            'stock_minimo'               => 10,
            'imagen'                     => 'paquete_500.jpg',
            'estado'                     => 'Activo',
        ]);

        $p2 = Producto::create([
            'id_categoria'               => $catPaquetes->id_categoria,
            'nombre_producto'            => 'Paquete Básico – 1.000 larvas',
            'descripcion'                => 'Incluye guía en PDF con todo el proceso de cría. Ideal para iniciar un cultivo pequeño y tener siempre disponibles.',
            'beneficios'                 => 'Permite rotación alimentaria y primeras pruebas de reproducción.',
            'instrucciones_conservacion' => 'Mantener a temperatura ambiente (22°C-26°C) o refrigerar.',
            'especies_recomendadas'      => 'Erizos, petauros, aves ornamentales, anfibios.',
            'politica_reembolso'         => 'Garantía de reposición o reembolso dentro de las 24 horas.',
            'precio'                     => 59900.00,
            'stock'                      => 45,
            'stock_minimo'               => 10,
            'imagen'                     => 'paquete_1000.jpg',
            'estado'                     => 'Activo',
        ]);

        $p3 = Producto::create([
            'id_categoria'               => $catPaquetes->id_categoria,
            'nombre_producto'            => 'Paquete Recomendado – 2.000 larvas',
            'descripcion'                => 'Incluye ENVÍO GRATIS y curso completo en video paso a paso. Estableces un cultivo sólido y producción continua desde el inicio.',
            'beneficios'                 => 'Excelente balance costo-beneficio, pie de cría autosostenible.',
            'instrucciones_conservacion' => 'Separar en bandejas plásticas ventiladas con salvado de trigo.',
            'especies_recomendadas'      => 'Criaderos de aves, pogonas, geckos leopardo, granjas avícolas.',
            'politica_reembolso'         => 'Reposición garantizada con registro fotográfico.',
            'precio'                     => 122900.00,
            'stock'                      => 40,
            'stock_minimo'               => 10,
            'imagen'                     => 'paquete_2000.jpg',
            'estado'                     => 'Activo',
        ]);

        $p4 = Producto::create([
            'id_categoria'               => $catPaquetes->id_categoria,
            'nombre_producto'            => 'Paquete Profesional – 5.000 larvas',
            'descripcion'                => 'Incluye ENVÍO GRATIS, curso en video y guía para gran escala. Ideal si tu proyecto es grande o quieres emprender.',
            'beneficios'                 => 'Volumen ideal para proyectos comerciales y bioconversión de residuos.',
            'instrucciones_conservacion' => 'Sistema vertical de bandejas con control de humedad.',
            'especies_recomendadas'      => 'Criaderos intensivos, fincas avícolas, zoológicos y centros de rescate.',
            'politica_reembolso'         => 'Garantía integral de llegada viva y soporte técnico.',
            'precio'                     => 289900.00,
            'stock'                      => 25,
            'stock_minimo'               => 5,
            'imagen'                     => 'paquete_5000.jpg',
            'estado'                     => 'Activo',
        ]);

        $p5 = Producto::create([
            'id_categoria'               => $catPaquetes->id_categoria,
            'nombre_producto'            => 'Paquete Industrial – 10.000 larvas',
            'descripcion'                => 'Incluye curso completo y asesoría personalizada (pago anticipado). Para producción comercial o suplementar grandes volúmenes.',
            'beneficios'                 => 'Máximo rendimiento económico por unidad, soporte zootécnico directo.',
            'instrucciones_conservacion' => 'Granja comercial o sala de cría climatizada.',
            'especies_recomendadas'      => 'Industria acuícola, avicultura comercial, plantas de procesamiento.',
            'politica_reembolso'         => 'Acompañamiento zootécnico y garantía de despacho.',
            'precio'                     => 499900.00,
            'stock'                      => 15,
            'stock_minimo'               => 5,
            'imagen'                     => 'paquete_10000.jpg',
            'estado'                     => 'Activo',
        ]);

        // Movimientos de inventario iniciales
        $inventarioInicial = [
            $p1->id_producto => 50,
            $p2->id_producto => 45,
            $p3->id_producto => 40,
            $p4->id_producto => 25,
            $p5->id_producto => 15,
        ];

        foreach ($inventarioInicial as $idProducto => $cantidad) {
            MovimientoInventario::create([
                'id_producto'      => $idProducto,
                'tipo_movimiento'  => 'ENTRADA',
                'cantidad'         => $cantidad,
                'observacion'      => 'Inventario inicial de producción BIO-GEST',
                'fecha_movimiento' => now(),
            ]);
        }

        // 6. Dos pedidos de prueba con detalles
        // Pedido 1: Pedido Completado
        $pedido1 = Pedido::create([
            'numero_pedido'     => 'PED-1001',
            'id_usuario'        => $cliente->id_usuario,
            'id_direccion'      => $dirCliente->id_direccion,
            'subtotal'          => 122900.00,
            'costo_envio'       => 0.00,
            'total'             => 122900.00,
            'metodo_pago'       => 'Contraentrega',
            'estado_pago'       => 'Aprobado',
            'estado_pedido'     => 'Completado',
            'direccion_entrega' => $dirCliente->direccion,
            'ciudad_entrega'    => $dirCliente->ciudad,
            'telefono_contacto' => $cliente->telefono,
            'fecha_pedido'      => now()->subDays(3),
        ]);

        PedidoDetalle::create([
            'id_pedido'       => $pedido1->id_pedido,
            'id_producto'     => $p3->id_producto,
            'cantidad'        => 1,
            'precio_unitario' => 122900.00,
            'subtotal'        => 122900.00,
        ]);

        // Pedido 2: Pedido Pendiente con múltiples detalles
        $pedido2 = Pedido::create([
            'numero_pedido'     => 'PED-1002',
            'id_usuario'        => $cliente->id_usuario,
            'id_direccion'      => $dirCliente->id_direccion,
            'subtotal'          => 94800.00,
            'costo_envio'       => 10000.00,
            'total'             => 104800.00,
            'metodo_pago'       => 'Transferencia Bancaria',
            'estado_pago'       => 'Pendiente',
            'estado_pedido'     => 'Pendiente',
            'direccion_entrega' => $dirCliente->direccion,
            'ciudad_entrega'    => $dirCliente->ciudad,
            'telefono_contacto' => $cliente->telefono,
            'fecha_pedido'      => now(),
        ]);

        PedidoDetalle::create([
            'id_pedido'       => $pedido2->id_pedido,
            'id_producto'     => $p1->id_producto,
            'cantidad'        => 1,
            'precio_unitario' => 34900.00,
            'subtotal'        => 34900.00,
        ]);

        PedidoDetalle::create([
            'id_pedido'       => $pedido2->id_pedido,
            'id_producto'     => $p2->id_producto,
            'cantidad'        => 1,
            'precio_unitario' => 59900.00,
            'subtotal'        => 59900.00,
        ]);

        // 7. Permisos de Rol
        PermisoRol::create(['id_rol' => $rolAdmin->id_rol, 'nombre_permiso' => 'GESTIONAR_PRODUCTOS']);
        PermisoRol::create(['id_rol' => $rolAdmin->id_rol, 'nombre_permiso' => 'GESTIONAR_USUARIOS']);
        PermisoRol::create(['id_rol' => $rolAdmin->id_rol, 'nombre_permiso' => 'VER_REPORTES']);
        PermisoRol::create(['id_rol' => $rolCliente->id_rol, 'nombre_permiso' => 'COMPRAR_PRODUCTOS']);
        PermisoRol::create(['id_rol' => $rolCliente->id_rol, 'nombre_permiso' => 'VER_HISTORIAL_PEDIDOS']);

        // 8. Promociones
        Promocion::create([
            'codigo_cupon'        => 'TENEBRIO10',
            'descuento_porcentaje'=> 10.00,
            'fecha_inicio'        => now(),
            'fecha_fin'           => now()->addDays(30),
            'estado'              => 'Activo',
        ]);

        // 9. Preguntas Frecuentes
        Faq::create([
            'pregunta'  => '¿Tienen garantía de llegada viva?',
            'respuesta' => 'Sí, todos nuestros paquetes se envían acondicionados térmicamente con garantía total de llegada viva a todo el país.',
            'categoria' => 'Envíos'
        ]);

        Faq::create([
            'pregunta'  => '¿Cómo debo almacenar las larvas que no use de inmediato?',
            'respuesta' => 'Puedes guardarlas en la bandeja inferior de la nevera (refrigeración suave a 8-12°C), donde hibernarán sin metamorfosearse hasta por 2 meses y medio.',
            'categoria' => 'Cuidados'
        ]);

        Faq::create([
            'pregunta'  => '¿Los envíos son gratuitos?',
            'respuesta' => 'El envío es GRATIS a partir del Paquete Recomendado de 2,000 larvas en adelante.',
            'categoria' => 'Envíos'
        ]);
    }
}
