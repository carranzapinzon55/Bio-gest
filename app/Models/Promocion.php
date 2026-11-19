<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $table = 'promocion';
    protected $primaryKey = 'id_promocion';
    public $timestamps = false;

    protected $fillable = [
        'codigo_cupon',
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
}
