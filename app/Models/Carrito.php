<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrito extends Model
{
    protected $table = 'carrito';
    protected $primaryKey = 'id_carrito';
    public $timestamps = false;

    protected $fillable = ['id_usuario', 'fecha_creacion'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CarritoDetalle::class, 'id_carrito', 'id_carrito');
    }
}
