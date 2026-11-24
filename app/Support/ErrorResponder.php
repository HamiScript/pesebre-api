<?php

namespace App\Support;

use App\Exceptions\ApiException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ErrorResponder
{
    public static function render(Throwable $e, Request $request): JsonResponse
    {
        [$status, $codigo, $mensaje, $detalles] = match (true) {
            $e instanceof ApiException => [$e->status, $e->codigo, $e->getMessage(), $e->detalles],
            $e instanceof ValidationException => [400, 'VALIDACION', 'Formato de datos inválido', self::detalles($e)],
            $e instanceof ModelNotFoundException => [404, 'NO_ENCONTRADO', 'El recurso no existe', []],
            $e instanceof AuthenticationException => [401, 'NO_AUTENTICADO', 'Token ausente o inválido', []],
            $e instanceof HttpExceptionInterface => self::http($e),
            default => [500, 'ERROR_INTERNO', 'Error interno del servidor', []],
        };

        return response()->json([
            'codigo' => $codigo,
            'mensaje' => $mensaje,
            'detalles' => $detalles,
            'timestamp' => now()->utc()->toIso8601ZuluString(),
            'ruta' => '/'.$request->path(),
        ], $status);
    }

    private static function http(HttpExceptionInterface $e): array
    {
        return match ($e->getStatusCode()) {
            403 => [403, 'ROL_INSUFICIENTE', 'No tienes permiso para esta acción', []],
            404 => [404, 'NO_ENCONTRADO', 'El recurso no existe', []],
            405 => [405, 'METODO_NO_PERMITIDO', 'Método no permitido', []],
            default => [$e->getStatusCode(), 'ERROR_HTTP', $e->getMessage() ?: 'Error de solicitud', []],
        };
    }

    private static function detalles(ValidationException $e): array
    {
        return collect($e->errors())
            ->map(fn ($mensajes, $campo) => ['campo' => $campo, 'mensajes' => $mensajes])
            ->values()
            ->all();
    }
}