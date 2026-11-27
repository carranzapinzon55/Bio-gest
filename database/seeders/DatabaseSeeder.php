<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
     * Seed the application's database with Tenebrios Gold official products and data.
     */
    public function run(): void
    {
        // 1. Roles (HU-02 / RF-002: Exclusivamente Administrador y Cliente)
        DB::table('permiso_rol')->whereNotIn('id_rol', [1, 2])->delete();
        DB::table('rol')->whereNotIn('id_rol', [1, 2])->delete();

        $rolAdmin = Rol::updateOrCreate(
            ['id_rol' => 1],
            ['nombre_rol' => 'Administrador', 'descripcion' => 'Acceso total a la administración y gestión del sistema']
        );

        $rolCliente = Rol::updateOrCreate(
            ['id_rol' => 2],
            ['nombre_rol' => 'Cliente', 'descripcion' => 'Acceso al catálogo, compras, carrito y seguimiento de pedidos']
        );

        // 2. Usuarios con contraseñas cifradas automáticamente vía cast 'hashed' en Usuario
        $admin = Usuario::firstOrCreate(
            ['correo' => 'admin@tenebriosgold.com'],
            [
                'id_rol'         => $rolAdmin->id_rol,
                'nombre'         => 'Administrador',
                'apellido'       => 'BIO-GEST',
                'telefono'       => '3001234567',
                'contrasena'     => 'Admin123*',
                'perfil_uso'     => 'Proyecto comercial / Gran escala',
                'estado'         => 'Activo',
                'fecha_registro' => now(),
            ]
        );

        $cliente = Usuario::firstOrCreate(
            ['correo' => 'cliente@tenebriosgold.com'],
            [
                'id_rol'         => $rolCliente->id_rol,
                'nombre'         => 'Cliente',
                'apellido'       => 'Tenebrios Gold',
                'telefono'       => '3109876543',
                'contrasena'     => 'Cliente123*',
                'perfil_uso'     => 'Alimentación continua',
                'estado'         => 'Activo',
                'fecha_registro' => now(),
            ]
        );

        // 3. Direcciones
        $dirAdmin = DireccionUsuario::firstOrCreate(
            ['id_usuario' => $admin->id_usuario, 'nombre_ubicacion' => 'Sede Principal Bio-Gest'],
            [
                'direccion'        => 'Calle 26 # 68-90, Suba',
                'ciudad'           => 'Bogotá',
                'departamento'     => 'Cundinamarca',
                'codigo_postal'    => '111111',
                'es_principal'     => true,
            ]
        );

        $dirCliente = DireccionUsuario::firstOrCreate(
            ['id_usuario' => $cliente->id_usuario, 'nombre_ubicacion' => 'Casa'],
            [
                'direccion'        => 'Carrera 15 # 45-20, Chapinero',
                'ciudad'           => 'Bogotá',
                'departamento'     => 'Cundinamarca',
                'codigo_postal'    => '110011',
                'es_principal'     => true,
            ]
        );

        // 4. Categorías Oficiales
        $catPaquetes = Categoria::firstOrCreate(
            ['nombre_categoria' => 'Paquetes de Larvas'],
            ['descripcion' => 'Larvas vivas de Tenebrio Molitor de alta calidad con garantía de llegada viva']
        );

        $catAbono = Categoria::firstOrCreate(
            ['nombre_categoria' => 'Abono Orgánico'],
            ['descripcion' => 'Frass (excremento de tenebrio) ideal como bioestimulante agrícola y enmienda orgánica']
        );

        $catKits = Categoria::firstOrCreate(
            ['nombre_categoria' => 'Kits de Cría'],
            ['descripcion' => 'Insumos, contenedores y accesorios completos para la cría y reproducción doméstica']
        );

        // 5. Los 5 Paquetes Oficiales de Tenebrios Gold (Tenebrio molitor)
        $p1 = Producto::updateOrCreate(
            ['nombre_producto' => 'Paquete Inicial – 500 larvas'],
            [
                'id_categoria'               => $catPaquetes->id_categoria,
                'descripcion'                => 'Ideal para probar o si tienes una mascota. Las que no uses las guardas en nevera, hibernan hasta 2 meses y medio.',
                'beneficios'                 => 'Alta proteína digestiva, ideal para reptiles y aves pequeñas.',
                'instrucciones_conservacion' => 'Refrigerar entre 8°C y 12°C en sustrato de salvado.',
                'especies_recomendadas'      => 'Aves pequeñas, reptiles, erizos, peces de acuario.',
                'politica_reembolso'         => 'Garantía 100% de llegada viva documentando desempaque.',
                'precio'                     => 34900.00,
                'stock'                      => 50,
                'stock_minimo'               => 10,
                'imagen'                     => 'Tenebrios_img.png',
                'estado'                     => 'Activo',
            ]
        );

        $p2 = Producto::updateOrCreate(
            ['nombre_producto' => 'Paquete Básico – 1.000 larvas'],
            [
                'id_categoria'               => $catPaquetes->id_categoria,
                'descripcion'                => 'Incluye guía en PDF con todo el proceso de cría. Ideal para iniciar un cultivo pequeño y tener siempre disponibles.',
                'beneficios'                 => 'Permite rotación alimentaria y primeras pruebas de reproducción.',
                'instrucciones_conservacion' => 'Mantener a temperatura ambiente (22°C-26°C) o refrigerar.',
                'especies_recomendadas'      => 'Erizos, petauros, aves ornamentales, anfibios.',
                'politica_reembolso'         => 'Garantía de reposición o reembolso dentro de las 24 horas.',
                'precio'                     => 59900.00,
                'stock'                      => 45,
                'stock_minimo'               => 10,
                'imagen'                     => 'Tenebrios_img.png',
                'estado'                     => 'Activo',
            ]
        );

        $p3 = Producto::updateOrCreate(
            ['nombre_producto' => 'Paquete Recomendado – 2.000 larvas'],
            [
                'id_categoria'               => $catPaquetes->id_categoria,
                'descripcion'                => 'Incluye ENVÍO GRATIS y curso completo en video paso a paso. Estableces un cultivo sólido y producción continua desde el inicio.',
                'beneficios'                 => 'Excelente balance costo-beneficio, pie de cría autosostenible.',
                'instrucciones_conservacion' => 'Separar en bandejas plásticas ventiladas con salvado de trigo.',
                'especies_recomendadas'      => 'Criaderos de aves, pogonas, geckos leopardo, granjas avícolas.',
                'politica_reembolso'         => 'Reposición garantizada con registro fotográfico.',
                'precio'                     => 122900.00,
                'stock'                      => 40,
                'stock_minimo'               => 10,
                'imagen'                     => 'Tenebrios_img.png',
                'estado'                     => 'Activo',
            ]
        );

        $p4 = Producto::updateOrCreate(
            ['nombre_producto' => 'Paquete Profesional – 5.000 larvas'],
            [
                'id_categoria'               => $catPaquetes->id_categoria,
                'descripcion'                => 'Incluye ENVÍO GRATIS, curso en video y guía para gran escala. Ideal si tu proyecto es grande o quieres emprender.',
                'beneficios'                 => 'Volumen ideal para proyectos comerciales y bioconversión de residuos.',
                'instrucciones_conservacion' => 'Sistema vertical de bandejas con control de humedad.',
                'especies_recomendadas'      => 'Criaderos intensivos, fincas avícolas, zoológicos y centros de rescate.',
                'politica_reembolso'         => 'Garantía integral de llegada viva y soporte técnico.',
                'precio'                     => 289900.00,
                'stock'                      => 25,
                'stock_minimo'               => 5,
                'imagen'                     => 'Tenebrios_img.png',
                'estado'                     => 'Activo',
            ]
        );

        $p5 = Producto::updateOrCreate(
            ['nombre_producto' => 'Paquete Industrial – 10.000 larvas'],
            [
                'id_categoria'               => $catPaquetes->id_categoria,
                'descripcion'                => 'Incluye curso completo y asesoría personalizada (pago anticipado). Para producción comercial o suplementar grandes volúmenes.',
                'beneficios'                 => 'Máximo rendimiento económico por unidad, soporte zootécnico directo.',
                'instrucciones_conservacion' => 'Granja comercial o sala de cría climatizada.',
                'especies_recomendadas'      => 'Industria acuícola, avicultura comercial, plantas de procesamiento.',
                'politica_reembolso'         => 'Acompañamiento zootécnico y garantía de despacho.',
                'precio'                     => 499900.00,
                'stock'                      => 15,
                'stock_minimo'               => 5,
                'imagen'                     => 'Tenebrios_img.png',
                'estado'                     => 'Activo',
            ]
        );

        // Abono Frass y Kits de Cría Oficiales
        $p6 = Producto::updateOrCreate(
            ['nombre_producto' => 'Abono Orgánico Frass (1 Kg)'],
            [
                'id_categoria'               => $catAbono->id_categoria,
                'descripcion'                => 'Bioestimulante y enmienda orgánica derivada de las deyecciones del Tenebrio molitor. Rico en quitina, nitrógeno asimilable y microorganismos benéficos.',
                'beneficios'                 => 'Potencia el sistema inmunológico vegetal y enriquece la microflora del suelo.',
                'instrucciones_conservacion' => 'Almacenar en lugar seco, fresco y protegido de la radiación solar directa.',
                'especies_recomendadas'      => 'Plantas ornamentales, huertos urbanos, cannabis medicinal y frutales.',
                'politica_reembolso'         => 'Garantía de calidad 100% libre de patógenos y humedad excesiva.',
                'precio'                     => 25000.00,
                'stock'                      => 80,
                'stock_minimo'               => 15,
                'imagen'                     => 'oferta_1.jfif',
                'estado'                     => 'Activo',
            ]
        );

        $p7 = Producto::updateOrCreate(
            ['nombre_producto' => 'Kit de Cría Doméstica Completo'],
            [
                'id_categoria'               => $catKits->id_categoria,
                'descripcion'                => 'Contenedor especializado con tamiz selector, sustrato enriquecido de salvado, fuente de hidratación y guía didáctica ilustrada.',
                'beneficios'                 => 'Facilita la separación limpia de escarabajos, pupas y larvas sin generar olores.',
                'instrucciones_conservacion' => 'Ubicar en espacio ventilado entre 23°C y 27°C sin exposición solar directa.',
                'especies_recomendadas'      => 'Familias, aficionados a la cría de insectos y pequeños propietarios de mascotas.',
                'politica_reembolso'         => 'Garantía de reposición de componentes plásticos defectuosos.',
                'precio'                     => 85000.00,
                'stock'                      => 30,
                'stock_minimo'               => 8,
                'imagen'                     => 'Combos_img.png',
                'estado'                     => 'Activo',
            ]
        );

        // Movimientos iniciales de inventario si no existen
        $productosOficiales = [$p1, $p2, $p3, $p4, $p5, $p6, $p7];
        foreach ($productosOficiales as $prod) {
            $existeMov = MovimientoInventario::where('id_producto', $prod->id_producto)->exists();
            if (!$existeMov && $prod->stock > 0) {
                MovimientoInventario::create([
                    'id_producto'      => $prod->id_producto,
                    'tipo_movimiento'  => 'ENTRADA',
                    'cantidad'         => $prod->stock,
                    'observacion'      => 'Inventario inicial de producción BIO-GEST · Tenebrios Gold',
                    'fecha_movimiento' => now(),
                ]);
            }
        }

        // 6. Pedidos de prueba
        $pedido1 = Pedido::firstOrCreate(
            ['numero_pedido' => 'PED-1001'],
            [
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
            ]
        );

        PedidoDetalle::firstOrCreate(
            ['id_pedido' => $pedido1->id_pedido, 'id_producto' => $p3->id_producto],
            [
                'cantidad'        => 1,
                'precio_unitario' => 122900.00,
                'subtotal'        => 122900.00,
            ]
        );

        $pedido2 = Pedido::firstOrCreate(
            ['numero_pedido' => 'PED-1002'],
            [
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
            ]
        );

        PedidoDetalle::firstOrCreate(
            ['id_pedido' => $pedido2->id_pedido, 'id_producto' => $p1->id_producto],
            [
                'cantidad'        => 1,
                'precio_unitario' => 34900.00,
                'subtotal'        => 34900.00,
            ]
        );

        PedidoDetalle::firstOrCreate(
            ['id_pedido' => $pedido2->id_pedido, 'id_producto' => $p2->id_producto],
            [
                'cantidad'        => 1,
                'precio_unitario' => 59900.00,
                'subtotal'        => 59900.00,
            ]
        );

        // 7. Permisos de Rol
        PermisoRol::firstOrCreate(['id_rol' => $rolAdmin->id_rol, 'nombre_permiso' => 'GESTIONAR_PRODUCTOS']);
        PermisoRol::firstOrCreate(['id_rol' => $rolAdmin->id_rol, 'nombre_permiso' => 'GESTIONAR_USUARIOS']);
        PermisoRol::firstOrCreate(['id_rol' => $rolAdmin->id_rol, 'nombre_permiso' => 'VER_REPORTES']);
        PermisoRol::firstOrCreate(['id_rol' => $rolCliente->id_rol, 'nombre_permiso' => 'COMPRAR_PRODUCTOS']);
        PermisoRol::firstOrCreate(['id_rol' => $rolCliente->id_rol, 'nombre_permiso' => 'VER_HISTORIAL_PEDIDOS']);

        // 8. Promoción oficial
        Promocion::firstOrCreate(
            ['codigo_cupon' => 'TENEBRIO10'],
            [
                'descuento_porcentaje'=> 10.00,
                'fecha_inicio'        => now(),
                'fecha_fin'           => now()->addDays(30),
                'estado'              => 'Activo',
            ]
        );

        // 9. Preguntas Frecuentes
        Faq::firstOrCreate(
            ['pregunta' => '¿Tienen garantía de llegada viva?'],
            [
                'respuesta' => 'Sí, todos nuestros paquetes se envían acondicionados térmicamente con garantía total de llegada viva a todo el país.',
                'categoria' => 'Envíos'
            ]
        );

        // 10. Configuración comercial y políticas de seguridad
        $this->call(ConfiguracionSeeder::class);
    }
}
