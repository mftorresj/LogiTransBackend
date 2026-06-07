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
        if (!$token) return $this->unauthorized('Token no proporcionado.');

        $authUrl = rtrim($_ENV['AUTH_SERVICE_URL'] ?? 'http://localhost:8001', '/');
        if (!$this->validateTokenRemote($authUrl, $token)) {
            return $this->unauthorized('Token inválido o sesión inactiva.');
        }

        return $handler->handle($request->withAttribute('auth_token', $token));
    }

    private function validateTokenRemote(string $authUrl, string $token): bool
    {
        $context = stream_context_create([
            'http' => ['method' => 'GET', 'header' => "Authorization: Bearer {$token}\r\n", 'timeout' => 5, 'ignore_errors' => true],
        ]);
        $result = @file_get_contents($authUrl . '/auth/validate', false, $context);
        if (!$result) return false;
        $data = json_decode($result, true);
        return isset($data['success']) && $data['success'] === true;
    }

    private function extractToken(Request $request): ?string
    {
        $h = $request->getHeaderLine('Authorization');
        if ($h && str_starts_with($h, 'Bearer ')) return substr($h, 7);
        $x = $request->getHeaderLine('X-Auth-Token');
        return $x ?: null;
    }

    private function unauthorized(string $message): Response
    {
        $r = new SlimResponse();
        $r->getBody()->write(json_encode(['success' => false, 'message' => $message, 'data' => null]));
        return $r->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}