<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\RecursoPesebre;
use Illuminate\Database\Eloquent\Collection;

class RecursoService
{
    public function listar(?string $categoria, ?string $estado): Collection
    {
        return RecursoPesebre::query()
            ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->get();
    }

    public function obtener(int $id): RecursoPesebre
    {
        return RecursoPesebre::findOrFail($id);
    }

    public function crear(array $datos): RecursoPesebre
    {
        if (RecursoPesebre::where('nombre', $datos['nombre'])->exists()) {
            throw new ApiException(409, 'NOMBRE_DUPLICADO', "Ya existe un recurso llamado {$datos['nombre']}");
        }

        $datos['estado'] = $this->calcularEstado($datos['cantidad'], $datos['stockMinimo']);

        return RecursoPesebre::create($datos);
    }

    public function consumir(int $id, int $cantidad): RecursoPesebre
    {
        $recurso = $this->obtener($id);

        if ($cantidad > $recurso->cantidad) {
            throw new ApiException(409, 'STOCK_INSUFICIENTE',
                "No se puede consumir {$cantidad}; solo hay {$recurso->cantidad}");
        }

        $recurso->cantidad -= $cantidad;
        $recurso->estado = $this->calcularEstado($recurso->cantidad, $recurso->stockMinimo);
        $recurso->save();

        return $recurso;
    }

    public function eliminar(int $id): void
    {
        $recurso = $this->obtener($id);

        if ($recurso->cantidad > 0) {
            throw new ApiException(409, 'RECURSO_CON_STOCK',
                'No se puede eliminar un recurso con cantidad mayor a 0');
        }

        $recurso->delete();
    }

    public function calcularEstado(int $cantidad, int $stockMinimo): string
    {
        if ($cantidad === 0) {
            return 'AGOTADO';
        }

        return $cantidad < $stockMinimo ? 'BAJO_STOCK' : 'DISPONIBLE';
    }
}