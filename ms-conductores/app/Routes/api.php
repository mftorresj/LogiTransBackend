<?php

declare(strict_types=1);

use App\Controllers\ConductorController;
use App\Middleware\AuthMiddleware;

global $app;
$app->group('/conductores', function ($group) {

    $group->get('',               [ConductorController::class, 'index']);
    $group->post('',              [ConductorController::class, 'store']);
    $group->get('/{id:[0-9]+}',   [ConductorController::class, 'show']);
    $group->put('/{id:[0-9]+}',   [ConductorController::class, 'update']);
    $group->delete('/{id:[0-9]+}',[ConductorController::class, 'destroy']);
    $group->patch('/{id:[0-9]+}/estado', [ConductorController::class, 'cambiarEstado']);

})->add(new AuthMiddleware());