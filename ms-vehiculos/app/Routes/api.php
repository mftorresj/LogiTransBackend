<?php

declare(strict_types=1);

use App\Controllers\VehiculoController;
use App\Middleware\AuthMiddleware;

global $app;
$app->group('/vehiculos', function ($group) {

    $group->get('',                        [VehiculoController::class, 'index']);
    $group->post('',                       [VehiculoController::class, 'store']);
    $group->get('/{id:[0-9]+}',            [VehiculoController::class, 'show']);
    $group->put('/{id:[0-9]+}',            [VehiculoController::class, 'update']);
    $group->delete('/{id:[0-9]+}',         [VehiculoController::class, 'destroy']);
    $group->patch('/{id:[0-9]+}/estado',   [VehiculoController::class, 'cambiarEstado']);

})->add(new AuthMiddleware());