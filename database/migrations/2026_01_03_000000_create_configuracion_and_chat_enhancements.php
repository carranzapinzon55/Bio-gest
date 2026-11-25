<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla configuracion
        if (!Schema::hasTable('configuracion')) {
            Schema::create('configuracion', function (Blueprint $table) {
                $table->increments('id_configuracion');
                $table->string('clave', 100)->unique();
                $table->longText('valor')->nullable();
                $table->string('categoria', 50)->default('general');
                $table->string('descripcion', 255)->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabla bitacora_seguridad
        if (!Schema::hasTable('bitacora_seguridad')) {
            Schema::create('bitacora_seguridad', function (Blueprint $table) {
                $table->increments('id_bitacora');
                $table->unsignedInteger('id_usuario')->nullable();
                $table->string('accion', 100);
                $table->text('descripcion')->nullable();
                $table->string('ip_origen', 45)->nullable();
                $table->timestamp('fecha')->useCurrent();

                $table->foreign('id_usuario')
                    ->references('id_usuario')
                    ->on('usuario')
                    ->onDelete('set null');
            });
        }

        // 3. Mejoras en mensaje_contacto
        if (Schema::hasTable('mensaje_contacto')) {
            Schema::table('mensaje_contacto', function (Blueprint $table) {
                if (!Schema::hasColumn('mensaje_contacto', 'estado')) {
                    $table->enum('estado', ['Pendiente', 'Atendido'])->default('Pendiente')->after('mensaje');
                }
                if (!Schema::hasColumn('mensaje_contacto', 'respuesta_admin')) {
                    $table->text('respuesta_admin')->nullable()->after('estado');
                }
                if (!Schema::hasColumn('mensaje_contacto', 'fecha_atencion')) {
                    $table->timestamp('fecha_atencion')->nullable()->after('respuesta_admin');
                }
            });
        }

        // 4. Mejoras en chat_mensaje
        if (Schema::hasTable('chat_mensaje')) {
            Schema::table('chat_mensaje', function (Blueprint $table) {
                if (!Schema::hasColumn('chat_mensaje', 'es_admin')) {
                    $table->boolean('es_admin')->default(false)->after('mensaje');
                }
                if (!Schema::hasColumn('chat_mensaje', 'id_admin')) {
                    $table->unsignedInteger('id_admin')->nullable()->after('es_admin');
                    $table->foreign('id_admin')
                        ->references('id_usuario')
                        ->on('usuario')
                        ->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('chat_mensaje')) {
            Schema::table('chat_mensaje', function (Blueprint $table) {
                if (Schema::hasColumn('chat_mensaje', 'id_admin')) {
                    $table->dropForeign(['id_admin']);
                    $table->dropColumn('id_admin');
                }
                if (Schema::hasColumn('chat_mensaje', 'es_admin')) {
                    $table->dropColumn('es_admin');
                }
            });
        }

        if (Schema::hasTable('mensaje_contacto')) {
            Schema::table('mensaje_contacto', function (Blueprint $table) {
                if (Schema::hasColumn('mensaje_contacto', 'fecha_atencion')) {
                    $table->dropColumn('fecha_atencion');
                }
                if (Schema::hasColumn('mensaje_contacto', 'respuesta_admin')) {
                    $table->dropColumn('respuesta_admin');
                }
                if (Schema::hasColumn('mensaje_contacto', 'estado')) {
                    $table->dropColumn('estado');
                }
            });
        }

        Schema::dropIfExists('bitacora_seguridad');
        Schema::dropIfExists('configuracion');
    }
};
