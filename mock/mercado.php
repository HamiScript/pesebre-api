<?php
// Mock local del MercadoBelenService, solo para pruebas.
header('Content-Type: text/xml; charset=utf-8');

$cuerpo = file_get_contents('php://input');
preg_match('/<mb:cantidad>(.*?)<\/mb:cantidad>/', $cuerpo, $n);
$cantidad = (int) ($n[1] ?? 0);

if ($cantidad === 999) { // lo pide el enunciado: provoca el Fault
    http_response_code(500);
    echo '<?xml version="1.0"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><soap:Fault><faultcode>soap:Server</faultcode><faultstring>Mercado cerrado</faultstring></soap:Fault></soap:Body></soap:Envelope>';
    exit;
}

if ($cantidad === 888) { // mi invento: simula un servicio lento (timeout)
    sleep(5);
}

$resultado = $cantidad > 100 ? 'SIN_EXISTENCIAS' : 'DISPONIBLE'; // mi invento

echo '<?xml version="1.0"?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mb="http://belen.beatlech/mercado"><soapenv:Body><mb:ConsultarExistenciasResponse><mb:resultado>' . $resultado . '</mb:resultado></mb:ConsultarExistenciasResponse></soapenv:Body></soapenv:Envelope>';