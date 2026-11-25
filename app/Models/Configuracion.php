<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracion';
    protected $primaryKey = 'id_configuracion';

    protected $fillable = [
        'clave',
        'valor',
        'categoria',
        'descripcion',
    ];

    /**
     * Obtiene el valor de una configuración por su clave.
     */
    public static function get(string $clave, mixed $default = null): mixed
    {
        $reg = static::where('clave', $clave)->first();
        return $reg ? $reg->valor : $default;
    }

    /**
     * Guarda o actualiza un parámetro de configuración.
     */
    public static function set(string $clave, mixed $valor, string $categoria = 'general', ?string $descripcion = null): self
    {
        return static::updateOrCreate(
            ['clave' => $clave],
            [
                'valor'       => is_array($valor) ? json_encode($valor) : (string) $valor,
                'categoria'   => $categoria,
                'descripcion' => $descripcion,
            ]
        );
    }
}
