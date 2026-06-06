<?php

declare(strict_types=1);

use App\Controllers\RutaController;
use App\Controllers\ProgramacionController;
use App\Middleware\AuthMiddleware;

$app->group('/rutas', function ($group) {
    $group->get('',                 [RutaController::class, 'index']);
    $group->post('',                [RutaController::class, 'store']);
    $group->get('/{id:[0-9]+}',     [RutaController::class, 'show']);
    $group->put('/{id:[0-9]+}',     [RutaController::class, 'update']);
    $group->delete('/{id:[0-9]+}',  [RutaController::class, 'destroy']);
})->add(new AuthMiddleware());

$app->group('/programacion', function ($group) {
    $group->get('',                 [ProgramacionController::class, 'index']);
    $group->post('',                [ProgramacionController::class, 'store']);
    $group->get('/{id:[0-9]+}',     [ProgramacionController::class, 'show']);
    $group->put('/{id:[0-9]+}',     [ProgramacionController::class, 'update']);
    $group->delete('/{id:[0-9]+}',  [ProgramacionController::class, 'destroy']);
})->add(new AuthMiddleware());