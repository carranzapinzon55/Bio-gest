<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;

    protected $fillable = [
        'id_categoria',
        'nombre_producto',
        'descripcion',
        'beneficios',
        'instrucciones_conservacion',
        'especies_recomendadas',
        'politica_reembolso',
        'precio',
        'stock',
        'stock_minimo',
        'imagen',
        'estado',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock'  => 'integer',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto', 'id_producto');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }
}
