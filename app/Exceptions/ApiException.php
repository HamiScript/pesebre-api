<?php

namespace App\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        public int $status,
        public string $codigo,
        string $mensaje,
        public array $detalles = [],
    ) {
        parent::__construct($mensaje);
    }
}