<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla: rol
        Schema::create('rol', function (Blueprint $table) {
            $table->increments('id_rol');
            $table->string('nombre_rol', 50);
            $table->string('descripcion', 255)->nullable();
        });

        // 2. Tabla: usuario (Incluye perfil_uso para regla de negocio 1)
        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->unsignedInteger('id_rol')->default(2);
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo', 150)->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('contrasena', 255);
            $table->enum('perfil_uso', [
                'Inicio de criadero',
                'Alimentación continua',
                'Proyecto comercial / Gran escala',
                'Mascota única'
            ])->default('Alimentación continua');
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->rememberToken();

            $table->foreign('id_rol')->references('id_rol')->on('rol');
        });

        // 3. Tabla: direccion_usuario
        Schema::create('direccion_usuario', function (Blueprint $table) {
            $table->increments('id_direccion');
            $table->unsignedInteger('id_usuario');
            $table->string('nombre_ubicacion', 100)->default('Casa');
            $table->string('direccion', 255);
            $table->string('ciudad', 100);
            $table->string('departamento', 100);
            $table->string('codigo_postal', 10)->nullable();
            $table->boolean('es_principal')->default(true);

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
        });

        // 4. Tabla: categoria
        Schema::create('categoria', function (Blueprint $table) {
            $table->increments('id_categoria');
            $table->string('nombre_categoria', 100);
            $table->text('descripcion')->nullable();
        });

        // 5. Tabla: producto (Enriquecida con conservación, uso por especie y política de reembolso)
        Schema::create('producto', function (Blueprint $table) {
            $table->increments('id_producto');
            $table->unsignedInteger('id_categoria');
            $table->string('nombre_producto', 150);
            $table->text('descripcion')->nullable();
            $table->text('beneficios')->nullable();
            $table->text('instrucciones_conservacion')->nullable();
            $table->string('especies_recomendadas', 255)->nullable()->comment('Aves, peces, reptiles, erizos, etc.');
            $table->text('politica_reembolso')->nullable();
            $table->decimal('precio', 10, 2);
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(10);
            $table->string('imagen', 255)->nullable();
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamp('fecha_creacion')->useCurrent();

            $table->foreign('id_categoria')->references('id_categoria')->on('categoria');
        });

        // Restricciones CHECK para producto: precio > 0 y stock >= 0
        DB::statement('ALTER TABLE producto ADD CONSTRAINT chk_producto_precio CHECK (precio > 0)');
        DB::statement('ALTER TABLE producto ADD CONSTRAINT chk_producto_stock CHECK (stock >= 0)');

        // 6. Tabla: movimiento_inventario
        Schema::create('movimiento_inventario', function (Blueprint $table) {
            $table->increments('id_movimiento');
            $table->unsignedInteger('id_producto');
            $table->enum('tipo_movimiento', ['ENTRADA', 'SALIDA', 'AJUSTE']);
            $table->integer('cantidad');
            $table->text('observacion')->nullable();
            $table->timestamp('fecha_movimiento')->useCurrent();

            $table->foreign('id_producto')->references('id_producto')->on('producto');
        });

        // 7. Tabla: carrito
        Schema::create('carrito', function (Blueprint $table) {
            $table->increments('id_carrito');
            $table->unsignedInteger('id_usuario');
            $table->timestamp('fecha_creacion')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
        });

        // 8. Tabla: carrito_detalle
        Schema::create('carrito_detalle', function (Blueprint $table) {
            $table->increments('id_detalle');
            $table->unsignedInteger('id_carrito');
            $table->unsignedInteger('id_producto');
            $table->integer('cantidad')->default(1);

            $table->foreign('id_carrito')->references('id_carrito')->on('carrito')->onDelete('cascade');
            $table->foreign('id_producto')->references('id_producto')->on('producto');
        });

        // 9. Tabla: pedido (Con estados logísticos claros de BIO-GEST)
        Schema::create('pedido', function (Blueprint $table) {
            $table->increments('id_pedido');
            $table->string('numero_pedido', 20)->unique()->nullable();
            $table->unsignedInteger('id_usuario');
            $table->unsignedInteger('id_direccion')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('costo_envio', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->string('metodo_pago', 50)->default('Contraentrega');
            $table->string('estado_pago', 20)->default('Pendiente');
            $table->enum('estado_pedido', [
                'Pendiente',
                'Procesando',
                'Despachado',
                'Listo para retiro',
                'Entregado',
                'Completado',
                'Cancelado'
            ])->default('Pendiente');
            $table->string('direccion_entrega', 255)->nullable();
            $table->string('ciudad_entrega', 100)->nullable();
            $table->string('telefono_contacto', 20)->nullable();
            $table->timestamp('fecha_pedido')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
            $table->foreign('id_direccion')->references('id_direccion')->on('direccion_usuario')->onDelete('set null');
        });

        // 10. Tabla: pedido_detalle
        Schema::create('pedido_detalle', function (Blueprint $table) {
            $table->increments('id_detalle');
            $table->unsignedInteger('id_pedido');
            $table->unsignedInteger('id_producto');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->onDelete('cascade');
            $table->foreign('id_producto')->references('id_producto')->on('producto');
        });

        // Restricción CHECK para pedido_detalle: cantidad > 0
        DB::statement('ALTER TABLE pedido_detalle ADD CONSTRAINT chk_pedido_detalle_cantidad CHECK (cantidad > 0)');

        // 11. Tabla: promocion
        Schema::create('promocion', function (Blueprint $table) {
            $table->increments('id_promocion');
            $table->string('codigo_cupon', 50)->unique();
            $table->decimal('descuento_porcentaje', 5, 2);
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');
        });

        // 12. Tabla: faq
        Schema::create('faq', function (Blueprint $table) {
            $table->increments('id_faq');
            $table->text('pregunta');
            $table->text('respuesta');
            $table->string('categoria', 50)->nullable();
        });

        // 13. Tabla: mensaje_contacto
        Schema::create('mensaje_contacto', function (Blueprint $table) {
            $table->increments('id_mensaje');
            $table->string('nombre', 100);
            $table->string('correo', 150);
            $table->string('asunto', 150)->nullable();
            $table->text('mensaje');
            $table->timestamp('fecha_envio')->useCurrent();
        });

        // 14. Tabla: chat_mensaje
        Schema::create('chat_mensaje', function (Blueprint $table) {
            $table->increments('id_mensaje');
            $table->unsignedInteger('id_usuario');
            $table->text('mensaje');
            $table->timestamp('fecha_envio')->useCurrent();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
        });

        // 15. Tabla: permiso_rol
        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->increments('id_permiso');
            $table->unsignedInteger('id_rol');
            $table->string('nombre_permiso', 100);

            $table->foreign('id_rol')->references('id_rol')->on('rol')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_rol');
        Schema::dropIfExists('chat_mensaje');
        Schema::dropIfExists('mensaje_contacto');
        Schema::dropIfExists('faq');
        Schema::dropIfExists('promocion');
        Schema::dropIfExists('pedido_detalle');
        Schema::dropIfExists('pedido');
        Schema::dropIfExists('carrito_detalle');
        Schema::dropIfExists('carrito');
        Schema::dropIfExists('movimiento_inventario');
        Schema::dropIfExists('producto');
        Schema::dropIfExists('categoria');
        Schema::dropIfExists('direccion_usuario');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('rol');
    }
};
