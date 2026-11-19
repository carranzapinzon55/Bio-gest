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
        'fecha_envio',
    ];
}
