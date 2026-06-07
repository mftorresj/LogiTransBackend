<?php
use Slim\Factory\AppFactory;
require __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Crear la app Slim
$app = AppFactory::create();

// Middleware globales (ejemplo: manejo de errores)
$app->addErrorMiddleware(true, true, true);

// Registrar rutas

require __DIR__ . '/../app/Routes/api.php';

// Ejecutar la app
$app->run();
