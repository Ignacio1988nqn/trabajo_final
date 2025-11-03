<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;

class SoapWrapperController extends Controller
{
    public function habitacionesDisponibles()
    {
        try {
            $client = new SoapClient(null, [
                'uri' => 'http://localhost:8081/soap/ocupacion',
                'location' => 'http://localhost:8081/soap_server.php',
                'soap_version' => SOAP_1_2,
                'trace' => true,
                'exceptions' => true,
            ]);

            $resultado = $client->getConteoPorEstado();
            $datos = [
                'disponibles' => $resultado['disponible'] ?? 0,
                'ocupadas'    => $resultado['ocupada'] ?? 0,
                'total'       => $resultado['total'] ?? 0,
            ];

            $datos['porcentaje_ocupacion'] = $datos['total'] > 0
                ? round(($datos['ocupadas'] / $datos['total']) * 100, 2)
                : 0;

            return response()->json($datos);
        } catch (\Exception $e) {

            return response()->json([
                'disponibles' => 0,
                'ocupadas'    => 0,
                'total'       => 0,
                'porcentaje_ocupacion' => 0,
                'error'       => 'Servicio temporalmente no disponible',
            ], 500);
        }
    }
}
