<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function login(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        $credential = trim($body['username'] ?? $body['email'] ?? '');
        $password   = (string) ($body['password'] ?? '');

        if (empty($credential) || $password === '') {
            return $this->json($response, false, 'Usuario/email y contraseña son obligatorios.', null, 422);
        }

        $user = User::findByCredential($credential);

        if (!$user) {
            return $this->json($response, false, 'Credenciales incorrectas.', null, 401);
        }

        $valid = password_verify($password, (string) $user->contrasena)
            || $user->contrasena === $password;

        if (!$valid) {
            return $this->json($response, false, 'Credenciales incorrectas.', null, 401);
        }

        if ($user->estado !== 'activo') {
            return $this->json($response, false, 'Usuario inactivo.', null, 403);
        }

        if ($user->sesion_activa) {
            return $this->json($response, false, 'El usuario ya tiene una sesión activa.', null, 409);
        }

        $token = User::generateToken();

        $user->token         = $token;
        $user->sesion_activa = true;
        $user->save();

        return $this->json($response, true, 'Inicio de sesión exitoso.', [
            'token' => $token,
            'user'  => [
                'id'      => $user->id,
                'nombre'  => $user->nombre,
                'correo'  => $user->correo,
                'usuario' => $user->usuario,
                'rol'     => $user->rol,
            ],
        ], 200);
    }

    public function logout(Request $request, Response $response): Response
    {

        $user = $request->getAttribute('auth_user');

        $user->token         = null;
        $user->sesion_activa = false;
        $user->save();

        return $this->json($response, true, 'Sesión cerrada correctamente.', null, 200);
    }

    public function validate(Request $request, Response $response): Response
    {

        $user = $request->getAttribute('auth_user');

        return $this->json($response, true, 'Token válido.', [
            'user' => [
                'id'      => $user->id,
                'nombre'  => $user->nombre,
                'correo'  => $user->correo,
                'usuario' => $user->usuario,
                'rol'     => $user->rol,
            ],
        ], 200);
    }

    private function json(
        Response $response,
        bool $success,
        string $message,
        mixed $data,
        int $status = 200
    ): Response {
        $response->getBody()->write(json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}