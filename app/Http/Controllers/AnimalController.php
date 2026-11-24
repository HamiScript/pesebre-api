<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Services\AnimalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnimalController extends Controller
{
    public function __construct(private AnimalService $servicio) {}

    public function index(Request $request)
    {
        $f = $request->validate(['especie' => ['nullable', Rule::in(Animal::ESPECIES)]]);

        return $this->servicio->listar($f['especie'] ?? null);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'especie' => ['required', Rule::in(Animal::ESPECIES)],
            'racionDiaria' => ['required', 'integer', 'between:1,10'],
        ]);

        return response()->json($this->servicio->crear($datos), 201);
    }

    public function alimentacion(int $id)
    {
        return $this->servicio->alimentar($id);
    }
}