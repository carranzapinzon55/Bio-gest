<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Configuracion extends Model
{
    protected $table = 'configuracion';
    protected $primaryKey = 'id_configuracion';
    public $timestamps = false;

    protected $fillable = [
        'clave',
        'valor',
        'categoria',
        'descripcion',
    ];

    /**
     * Obtiene el valor de una configuración por su clave de forma segura.
     */
    public static function get(string $clave, mixed $default = null): mixed
    {
        try {
            $reg = static::where('clave', $clave)->first();
            return $reg ? $reg->valor : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Guarda o actualiza un parámetro de configuración creando la tabla si no existiera.
     */
    public static function set(string $clave, mixed $valor, string $categoria = 'general', ?string $descripcion = null): ?self
    {
        try {
            if (!Schema::hasTable('configuracion')) {
                Schema::create('configuracion', function ($table) {
                    $table->increments('id_configuracion');
                    $table->string('clave', 100)->unique();
                    $table->text('valor')->nullable();
                    $table->string('categoria', 50)->default('general');
                    $table->string('descripcion', 255)->nullable();
                });
            }

            return static::updateOrCreate(
                ['clave' => $clave],
                [
                    'valor'       => is_array($valor) ? json_encode($valor) : (string) $valor,
                    'categoria'   => $categoria,
                    'descripcion' => $descripcion,
                ]
            );
        } catch (\Throwable $e) {
            return null;
        }
    }
}
