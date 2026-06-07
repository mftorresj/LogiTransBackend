<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

global $app;

$app->group('/auth', function ($group) {

    $group->post('/login', [AuthController::class, 'login']);

    $group->post('/logout',   [AuthController::class, 'logout'])
          ->add(new AuthMiddleware());

    $group->get('/validate',  [AuthController::class, 'validate'])
          ->add(new AuthMiddleware());
});