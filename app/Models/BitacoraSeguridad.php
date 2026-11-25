<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public static function registrar(?int $idUsuario, string $accion, ?string $descripcion = null, ?string $ip = null): self
    {
        return static::create([
            'id_usuario'  => $idUsuario,
            'accion'      => $accion,
            'descripcion' => $descripcion,
            'ip_origen'   => $ip ?? request()->ip(),
            'fecha'       => now(),
        ]);
    }
}
