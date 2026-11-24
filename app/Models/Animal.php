<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    public const ESPECIES = ['BUEY', 'MULA', 'OVEJA'];

    protected $table = 'animales';

    protected $fillable = ['nombre', 'especie', 'racionDiaria'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = ['ultimaAlimentacion' => 'date:Y-m-d'];
}