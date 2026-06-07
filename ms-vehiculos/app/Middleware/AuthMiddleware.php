<?php

declare(strict_types=1);

namespace App\Middleware;

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

        $authUrl = rtrim($_ENV['AUTH_SERVICE_URL'] ?? 'http://localhost:8001', '/');
        $valid   = $this->validateTokenRemote($authUrl, $token);

        if (!$valid) {
            return $this->unauthorized('Token inválido o sesión inactiva.');
        }

        $request = $request->withAttribute('auth_token', $token);

        return $handler->handle($request);
    }

    private function validateTokenRemote(string $authUrl, string $token): bool
    {
        $url     = $authUrl . '/auth/validate';
        $context = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'header'        => "Authorization: Bearer {$token}\r\nContent-Type: application/json\r\n",
                'timeout'       => 5,
                'ignore_errors' => true,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);
        if ($result === false) return false;

        $data = json_decode($result, true);
        return isset($data['success']) && $data['success'] === true;
    }

    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        $xToken = $request->getHeaderLine('X-Auth-Token');
        if ($xToken) return $xToken;

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