<?php

use App\Services\OcupacionSoapService;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$options = [
    'uri' => 'http://localhost:8000/soap/ocupacion',
    'soap_version' => SOAP_1_2,
];

$server = new SoapServer(null, $options);
$server->setClass(OcupacionSoapService::class);
$server->handle();
