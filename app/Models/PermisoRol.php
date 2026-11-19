<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermisoRol extends Model
{
    protected $table = 'permiso_rol';
    protected $primaryKey = 'id_permiso';
    public $timestamps = false;

    protected $fillable = ['id_rol', 'nombre_permiso'];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
}
