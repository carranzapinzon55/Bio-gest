<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'pedido';
    protected $primaryKey = 'id_pedido';
    public $timestamps = false;

    protected $fillable = [
        'numero_pedido',
        'id_usuario',
        'id_direccion',
        'subtotal',
        'costo_envio',
        'total',
        'metodo_pago',
        'estado_pago',
        'estado_pedido',
        'direccion_entrega',
        'ciudad_entrega',
        'telefono_contacto',
        'fecha_pedido',
    ];

    protected $casts = [
        'subtotal'     => 'decimal:2',
        'costo_envio'  => 'decimal:2',
        'total'        => 'decimal:2',
        'fecha_pedido' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function direccion(): BelongsTo
    {
        return $this->belongsTo(DireccionUsuario::class, 'id_direccion', 'id_direccion');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(PedidoDetalle::class, 'id_pedido', 'id_pedido');
    }
}
