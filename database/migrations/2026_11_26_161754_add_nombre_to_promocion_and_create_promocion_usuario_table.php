<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('promocion')) {
            Schema::table('promocion', function (Blueprint $table) {
                if (!Schema::hasColumn('promocion', 'nombre')) {
                    $table->string('nombre', 150)->nullable()->after('codigo_cupon');
                }
            });
        }

        if (!Schema::hasTable('promocion_usuario')) {
            Schema::create('promocion_usuario', function (Blueprint $table) {
                $table->increments('id_pivote');
                $table->unsignedInteger('id_promocion');
                $table->unsignedInteger('id_usuario');
                $table->unsignedInteger('id_pedido')->nullable();
                $table->timestamp('fecha_uso')->useCurrent();

                $table->foreign('id_promocion')
                    ->references('id_promocion')
                    ->on('promocion')
                    ->onDelete('cascade');

                $table->foreign('id_usuario')
                    ->references('id_usuario')
                    ->on('usuario')
                    ->onDelete('cascade');

                $table->foreign('id_pedido')
                    ->references('id_pedido')
                    ->on('pedido')
                    ->onDelete('set null');

                $table->unique(['id_promocion', 'id_usuario'], 'uk_promocion_usuario');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocion_usuario');

        if (Schema::hasTable('promocion') && Schema::hasColumn('promocion', 'nombre')) {
            Schema::table('promocion', function (Blueprint $table) {
                $table->dropColumn('nombre');
            });
        }
    }
};
