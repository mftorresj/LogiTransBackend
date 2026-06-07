<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {

        $token = $this->extractToken($request);

        if (!$token) {
            return $this->unauthorized('Token no proporcionado.');
        }

        $user = User::findByToken($token);

        if (!$user) {
            return $this->unauthorized('Token inválido o sesión inactiva.');
        }

        $request = $request->withAttribute('auth_user', $user);

        return $handler->handle($request);
    }

    private function extractToken(Request $request): ?string
    {

        $authHeader = $request->getHeaderLine('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        $xToken = $request->getHeaderLine('X-Auth-Token');
        if ($xToken) {
            return $xToken;
        }

        return null;
    }

    private function unauthorized(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $message,
            'data'    => null,
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}