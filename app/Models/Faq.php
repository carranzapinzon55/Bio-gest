<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faq';
    protected $primaryKey = 'id_faq';
    public $timestamps = false;

    protected $fillable = [
        'pregunta',
        'respuesta',
        'categoria',
    ];
}
