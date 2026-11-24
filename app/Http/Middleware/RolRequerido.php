<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;

class RolRequerido
{
    public function handle(Request $request, Closure $next, string $rolPermitido)
    {
        $token = $request->bearerToken();

        if (! $token) {
            throw new ApiException(401, 'NO_AUTENTICADO', 'Falta el token en el header Authorization');
        }

        $rolDelToken = null;
        foreach (config('pesebre.tokens') as $rol => $valor) {
            if ($valor && hash_equals($valor, $token)) {
                $rolDelToken = $rol;
                break;
            }
        }

        if (! $rolDelToken) {
            throw new ApiException(401, 'TOKEN_INVALIDO', 'El token no es válido');
        }

        if ($rolDelToken !== $rolPermitido) {
            throw new ApiException(403, 'ROL_INSUFICIENTE', "Se requiere el rol {$rolPermitido}");
        }

        return $next($request);
    }
}