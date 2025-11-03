<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use SoapClient;
use SoapFault;
use Illuminate\Support\Facades\Http;

class EstadisticasController extends Controller
{
    public function index(Request $request)
    {
        $porcentajeOcupacion = null;
        $habitacionesDisponibles = null;

        try {
            $client = new SoapClient(null, [
                'uri' => 'http://localhost:8081/soap/ocupacion',
                'location' => 'http://localhost:8081/soap_server.php',
                'soap_version' => SOAP_1_2,
                'trace' => true,
                'exceptions' => true,
            ]);
            $porcentajeOcupacion = $client->getConteoPorEstado();
        } catch (SoapFault $e) {
            $porcentajeOcupacion = ['error' => $e->getMessage()];
        }
        try {
            $response = Http::get('http://localhost:8082/api/ocupacion');
            if ($response->successful()) {
                $habitacionesDisponibles = $response->json('total');
            }
        } catch (\Exception $e) {
            $habitacionesDisponibles = null;
        }
        return Inertia::render('Estadisticas/Index', [
            'porcentajeOcupacion' => $porcentajeOcupacion,
            'habitacionesDisponibles' => $habitacionesDisponibles,
        ]);
    }
}
