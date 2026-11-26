<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromocionUsuario extends Model
{
    protected $table = 'promocion_usuario';
    protected $primaryKey = 'id_pivote';
    public $timestamps = false;

    protected $fillable = [
        'id_promocion',
        'id_usuario',
        'id_pedido',
        'fecha_uso',
    ];

    protected $casts = [
        'fecha_uso' => 'datetime',
    ];

    public function promocion(): BelongsTo
    {
        return $this->belongsTo(Promocion::class, 'id_promocion');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }
}
