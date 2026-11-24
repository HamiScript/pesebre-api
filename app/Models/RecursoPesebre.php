<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecursoPesebre extends Model
{
    public const CATEGORIAS = ['PAJA', 'MANTA', 'AGUA', 'ALIMENTO_ANIMALES', 'LAMPARA_ACEITE'];
    public const ESTADOS = ['DISPONIBLE', 'BAJO_STOCK', 'AGOTADO', 'REABASTECIMIENTO_PENDIENTE'];

    protected $fillable = ['nombre', 'categoria', 'cantidad', 'stockMinimo', 'estado'];

    protected $hidden = ['created_at', 'updated_at'];
}
