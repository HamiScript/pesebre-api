<?php

namespace App\Services;

use App\Exceptions\MercadoNoDisponibleException;
use Illuminate\Support\Facades\Http;
use Throwable;

class MercadoAdapter
{
    /** Devuelve 'DISPONIBLE' o 'SIN_EXISTENCIAS'. Lanza MercadoNoDisponibleException si el servicio falla. */
    public function consultarExistencias(string $categoria, int $cantidad): string
    {
        try {
            $respuesta = Http::timeout(3)
                ->withHeaders(['SOAPAction' => 'ConsultarExistencias'])
                ->withBody($this->construirSolicitud($categoria, $cantidad), 'text/xml; charset=utf-8')
                ->post((string) config('pesebre.tokens.mercado_url'));
        } catch (Throwable $e) {
            throw new MercadoNoDisponibleException('El Mercado de Belén no respondió a tiempo');
        }

        return $this->interpretarRespuesta($respuesta->body());
    }

    private function construirSolicitud(string $categoria, int $cantidad): string
    {
        $categoria = htmlspecialchars($categoria, ENT_XML1);

        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mb="http://belen.beatlech/mercado">
  <soapenv:Body>
    <mb:ConsultarExistenciasRequest>
      <mb:categoria>{$categoria}</mb:categoria>
      <mb:cantidad>{$cantidad}</mb:cantidad>
    </mb:ConsultarExistenciasRequest>
  </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    private function interpretarRespuesta(string $cuerpo): string
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($cuerpo);

        if ($doc === false) {
            throw new MercadoNoDisponibleException('Respuesta ilegible del Mercado de Belén');
        }

        if ($doc->xpath("//*[local-name()='Fault']")) {
            throw new MercadoNoDisponibleException('El Mercado de Belén respondió con un Fault');
        }

        $nodo = $doc->xpath("//*[local-name()='resultado']");
        $valor = isset($nodo[0]) ? trim((string) $nodo[0]) : '';

        if (! in_array($valor, ['DISPONIBLE', 'SIN_EXISTENCIAS'], true)) {
            throw new MercadoNoDisponibleException('Resultado desconocido del Mercado de Belén');
        }

        return $valor;
    }
}