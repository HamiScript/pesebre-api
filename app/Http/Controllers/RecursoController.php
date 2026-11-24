<?php

namespace App\Http\Controllers;

use App\Models\RecursoPesebre;
use App\Services\RecursoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecursoController extends Controller
{
    public function __construct(private RecursoService $servicio) {}

    public function index(Request $request)
    {
        $f = $request->validate([
            'categoria' => ['nullable', Rule::in(RecursoPesebre::CATEGORIAS)],
            'estado' => ['nullable', Rule::in(RecursoPesebre::ESTADOS)],
        ]);

        return $this->servicio->listar($f['categoria'] ?? null, $f['estado'] ?? null);
    }

    public function show(int $id)
    {
        return $this->servicio->obtener($id);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:60'],
            'categoria' => ['required', Rule::in(RecursoPesebre::CATEGORIAS)],
            'cantidad' => ['required', 'integer', 'min:0'],
            'stockMinimo' => ['required', 'integer', 'min:1'],
        ]);

        return response()->json($this->servicio->crear($datos), 201);
    }

    public function consumo(Request $request, int $id)
    {
        $datos = $request->validate(['cantidad' => ['required', 'integer', 'min:1']]);

        return $this->servicio->consumir($id, $datos['cantidad']);
    }
    public function reabastecimiento(Request $request, int $id)
    {
        $datos = $request->validate(['cantidad' => ['required', 'integer', 'min:1']]);

        $resultado = $this->servicio->reabastecer($id, $datos['cantidad']);

        if ($resultado['confirmado']) {
            return $resultado['recurso'];
        }

        return response()->json([
            'mensaje' => 'El Mercado de Belén no está disponible. El reabastecimiento quedó pendiente y se confirmará después.',
            'recurso' => $resultado['recurso'],
        ], 202);
    }
    public function destroy(int $id)
    {
        $this->servicio->eliminar($id);

        return response()->noContent();
    }
}
