<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DireccionUsuario extends Model
{
    protected $table = 'direccion_usuario';
    protected $primaryKey = 'id_direccion';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'nombre_ubicacion',
        'direccion',
        'ciudad',
        'departamento',
        'codigo_postal',
        'es_principal',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
