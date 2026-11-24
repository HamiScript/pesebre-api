<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Animal;
use App\Models\RecursoPesebre;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AnimalService
{
    public function __construct(private RecursoService $recursos) {}

    public function listar(?string $especie): Collection
    {
        return Animal::query()
            ->when($especie, fn ($q) => $q->where('especie', $especie))
            ->get();
    }

    public function crear(array $datos): Animal
    {
        return Animal::create($datos);
    }

    public function alimentar(int $id): Animal
    {
        return DB::transaction(function () use ($id) {
            $animal = Animal::findOrFail($id);
            $hoy = now()->toDateString();

            if ($animal->ultimaAlimentacion?->toDateString() === $hoy) {
                throw new ApiException(422, 'YA_ALIMENTADO', "{$animal->nombre} ya fue alimentado hoy");
            }

            $alimento = RecursoPesebre::where('categoria', 'ALIMENTO_ANIMALES')->orderBy('id')->first();

            if (! $alimento) {
                throw new ApiException(422, 'SIN_RECURSO_ALIMENTO', 'No hay un recurso de ALIMENTO_ANIMALES registrado');
            }

            if ($animal->racionDiaria > $alimento->cantidad) {
                throw new ApiException(409, 'ALIMENTO_INSUFICIENTE',
                    "La ración es {$animal->racionDiaria} y solo hay {$alimento->cantidad}");
            }

            $alimento->cantidad -= $animal->racionDiaria;
            $alimento->estado = $this->recursos->calcularEstado($alimento->cantidad, $alimento->stockMinimo);
            $alimento->save();

            $animal->ultimaAlimentacion = $hoy;
            $animal->save();

            return $animal;
        });
    }
}