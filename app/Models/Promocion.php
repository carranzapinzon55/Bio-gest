<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promocion extends Model
{
    protected $table = 'promocion';
    protected $primaryKey = 'id_promocion';
    public $timestamps = false;

    protected $fillable = [
        'codigo_cupon',
        'nombre',
        'descuento_porcentaje',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'descuento_porcentaje' => 'decimal:2',
        'fecha_inicio'         => 'datetime',
        'fecha_fin'            => 'datetime',
    ];

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'promocion_usuario', 'id_promocion', 'id_usuario')
            ->withPivot('id_pedido', 'fecha_uso');
    }

    public function redenciones(): HasMany
    {
        return $this->hasMany(PromocionUsuario::class, 'id_promocion');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_promocion');
    }

    /**
     * Nombre comercial de la promoción (fallback al código si no está asignado).
     */
    public function getNombreComercialAttribute(): string
    {
        if (!empty($this->nombre)) {
            return $this->nombre;
        }

        $pct = (int) $this->descuento_porcentaje;
        return "Descuento {$pct}% ({$this->codigo_cupon})";
    }

    /**
     * Valida si esta promoción ya ha sido utilizada por el usuario indicado.
     * Verifica tanto la tabla pivote promocion_usuario como pedidos confirmados/activos.
     */
    public function haSidoUsadaPor(?int $idUsuario): bool
    {
        if (!$idUsuario) {
            return false;
        }

        // 1. Verificación en la tabla pivote promocion_usuario
        $usadoEnPivote = PromocionUsuario::where('id_promocion', $this->id_promocion)
            ->where('id_usuario', $idUsuario)
            ->exists();

        if ($usadoEnPivote) {
            return true;
        }

        // 2. Verificación en la tabla pedido (excluyendo cancelados)
        $usadoEnPedido = Pedido::where('id_usuario', $idUsuario)
            ->where('id_promocion', $this->id_promocion)
            ->where('estado_pedido', '!=', 'Cancelado')
            ->exists();

        return $usadoEnPedido;
    }
}
