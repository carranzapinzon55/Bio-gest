<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class BitacoraSeguridad extends Model
{
    protected $table = 'bitacora_seguridad';
    protected $primaryKey = 'id_bitacora';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'accion',
        'descripcion',
        'ip_origen',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public static function registrar(?int $idUsuario, string $accion, ?string $descripcion = null, ?string $ip = null): ?self
    {
        try {
            if (!Schema::hasTable('bitacora_seguridad')) {
                Schema::create('bitacora_seguridad', function ($table) {
                    $table->increments('id_bitacora');
                    $table->unsignedInteger('id_usuario')->nullable();
                    $table->string('accion', 150);
                    $table->text('descripcion')->nullable();
                    $table->string('ip_origen', 45)->nullable();
                    $table->timestamp('fecha')->useCurrent();
                });
            }

            return static::create([
                'id_usuario'  => $idUsuario,
                'accion'      => $accion,
                'descripcion' => $descripcion,
                'ip_origen'   => $ip ?? request()->ip(),
                'fecha'       => now(),
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
