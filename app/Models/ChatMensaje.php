<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMensaje extends Model
{
    protected $table = 'chat_mensaje';
    protected $primaryKey = 'id_mensaje';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'mensaje',
        'es_admin',
        'id_admin',
        'fecha_envio',
    ];

    protected $casts = [
        'es_admin'    => 'boolean',
        'fecha_envio' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_admin');
    }
}
