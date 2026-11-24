# Pesebre API — Escenario C (Gestión de recursos del pesebre)

API REST en Laravel para administrar los recursos del pesebre y sus animales, integrada con el servicio SOAP heredado MercadoBelenService.

## Cómo levantar el proyecto

1. `composer install`
2. Copiar `.env.example` a `.env`
3. `php artisan key:generate`
4. `php artisan migrate` (acepta crear el archivo SQLite si lo pregunta)
5. `php artisan serve` → http://127.0.0.1:8000

Requiere PHP 8.3 o superior.

## Variables de entorno

| Variable | Descripción |
|---|---|
| `APP_TIMEZONE` | `America/Guayaquil`; define "el mismo día" para la regla de alimentación |
| `TOKEN_ENCARGADO` | Token del rol ENCARGADO_PESEBRE |
| `TOKEN_PASTOR` | Token de un rol distinto (para probar el 403) |
| `MERCADO_BELEN_URL` | Endpoint del servicio SOAP (el mock del docente) |

## Usuarios / tokens de prueba

Header `Authorization: Bearer <token>`.

| Rol | Token |
|---|---|
| ENCARGADO_PESEBRE | `demo-encargado-2026` |
| PASTOR | `demo-pastor-2026` |

Sin token → 401. Token de otro rol → 403.

## Endpoints

| Método | Ruta | Seguridad |
|---|---|---|
| POST | /api/recursos | ENCARGADO_PESEBRE |
| GET | /api/recursos (?categoria=, ?estado=) | pública |
| GET | /api/recursos/{id} | pública |
| PATCH | /api/recursos/{id}/consumo | ENCARGADO_PESEBRE |
| PATCH | /api/recursos/{id}/reabastecimiento | ENCARGADO_PESEBRE |
| DELETE | /api/recursos/{id} | ENCARGADO_PESEBRE |
| POST | /api/animales | pública |
| GET | /api/animales (?especie=) | pública |
| PATCH | /api/animales/{id}/alimentacion | pública |

## Arquitectura

Diagrama: [docs/arquitectura.png](docs/arquitectura.png)

Rutas (`routes/api.php`) → middleware `RolRequerido` (401/403) → controladores (solo validan formato, 400) → servicios (reglas de negocio, 409/422) → modelos Eloquent (SQLite).
`MercadoAdapter` es la única clase que conoce el XML del SOAP. El manejador global de errores (`ErrorResponder`) da a todas las respuestas de error el mismo formato JSON.

Fallo del SOAP (Fault o más de 3 s): el adaptador lanza `MercadoNoDisponibleException`; el servicio deja el recurso en `REABASTECIMIENTO_PENDIENTE` sin cambiar la cantidad, y la API responde 202.

## Decisiones donde el enunciado no define

- Nombre de recurso repetido → 409.
- Token inexistente → 401.
- Alimentar: primero se valida "ya alimentado hoy" (422) y luego el alimento (409).
- Si hay varios recursos ALIMENTO_ANIMALES se usa el de menor id.
- Un recurso sale de REABASTECIMIENTO_PENDIENTE al recalcularse su estado (consumo o reabastecimiento exitoso).

## Pruebas

Colección de Postman: `docs/Pesebre-API.postman_collection.json`.
Mock local del SOAP para pruebas: `php -S 127.0.0.1:8001 mock/mercado.php` (la cantidad 999 provoca el Fault).

## Microservicios

Separaría tres servicios. **Inventario**: dueño de los recursos (nombre, categoría, cantidad, stockMinimo, estado) y de las reglas de stock. **Animales**: dueño de los animales (nombre, especie, ración, última alimentación); para alimentar, pide a Inventario descontar la ración. **Reabastecimiento**: dueño del adaptador SOAP y de los pedidos pendientes; avisa a Inventario cuando el Mercado confirma. Cada servicio tendría su propia base de datos.

Si el 24 de diciembre a las 23:00 cae Inventario, Animales no podría alimentar: respondería 503 sin marcar al animal como alimentado, pero seguiría listando animales. Si cae el Mercado, el pedido queda REABASTECIMIENTO_PENDIENTE, la API responde 202 y el inventario actual sigue operando hasta reintentar.