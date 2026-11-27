<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajeContacto extends Model
{
    protected $table = 'mensaje_contacto';
    protected $primaryKey = 'id_mensaje';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'correo',
        'asunto',
        'mensaje',
        'estado',
        'respuesta_admin',
        'fecha_atencion',
        'fecha_envio',
    ];

    protected $casts = [
        'fecha_envio'    => 'datetime',
        'fecha_atencion' => 'datetime',
    ];
}
