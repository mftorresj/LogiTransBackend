<?php

declare(strict_types=1);

use App\Controllers\ViajeController;
use App\Middleware\AuthMiddleware;

/** @var \Slim\App $app */
global $app;

$app->group('/viajes', function ($group) {

    $group->get('',                              [ViajeController::class, 'index']);
    $group->post('',                             [ViajeController::class, 'store']);
    $group->get('/{id:[0-9]+}',                  [ViajeController::class, 'show']);

    $group->post('/{id:[0-9]+}/iniciar',         [ViajeController::class, 'iniciar']);
    $group->patch('/{id:[0-9]+}/estado',         [ViajeController::class, 'actualizarEstado']);
    $group->post('/{id:[0-9]+}/finalizar',       [ViajeController::class, 'finalizar']);

    $group->post('/{id:[0-9]+}/novedades',       [ViajeController::class, 'registrarNovedad']);
    $group->get('/{id:[0-9]+}/seguimiento',      [ViajeController::class, 'seguimiento']);

})->add(new AuthMiddleware());