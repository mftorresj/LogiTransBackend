<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

require __DIR__ . '/../app/Config/Database.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(
    displayErrorDetails: ($_ENV['APP_ENV'] === 'development'),
    logErrors: true,
    logErrorDetails: true
);

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Auth-Token')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
});

$app->options('/{routes:.+}', function ($request, $response) {
    return $response->withStatus(200);
});

require __DIR__ . '/../app/Routes/api.php';

$app->run();
//Revisar que el código de index.php es igual en ms-vehiculos, ms-conductores y ms-rutas. Solo debe haber diferencias en el namespace de los controladores y rutas.