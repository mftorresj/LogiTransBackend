<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Ruta;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RutaController
{
    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Ruta::query();

        if (!empty($params['ciudad'])) {
            $c = $params['ciudad'];
            $query->where(function ($q) use ($c) {
                $q->where('ciudad_origen', 'like', "%{$c}%")
                  ->orWhere('ciudad_destino', 'like', "%{$c}%");
            });
        }

        $rutas = $query->with('programaciones')->orderBy('ciudad_origen')->get();

        return $this->json($response, true, 'Rutas obtenidas correctamente.', $rutas);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $ruta = Ruta::with('programaciones')->find((int) $args['id']);

        if (!$ruta) {
            return $this->json($response, false, 'Ruta no encontrada.', null, 404);
        }

        return $this->json($response, true, 'Ruta obtenida.', $ruta);
    }

    public function store(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();
        
        $distancia = (float) ($body['distancia'] ?? $body['distancia'] ?? 0);
        $tiempoEstimado = trim((string)$body['tiempo_estimado'] ?? (string)$body['tiempo_estimado'] ?? '');

        $errores = [];
        foreach (['ciudad_origen', 'ciudad_destino'] as $campo) {
            if (empty($body[$campo])) {
                $errores[] = "El campo '{$campo}' es obligatorio.";
            }
        }

        if ($distancia <= 0) {
            $errores[] = 'El campo "distancia" es obligatorio y debe ser mayor a cero.';
        }

        if ($tiempoEstimado === '') {
            $errores[] = 'El campo "tiempo_estimado" es obligatorio.';
        }

        if (!empty($errores)) {
            return $this->json($response, false, implode(' ', $errores), null, 422);
        }
        if ($distancia <= 0) {
            return $this->json($response, false, 'La distancia debe ser mayor a cero.', null, 422);
        }

        $origen  = strtolower(trim($body['ciudad_origen']));
        $destino = strtolower(trim($body['ciudad_destino']));

        if (Ruta::whereRaw('LOWER(ciudad_origen) = ?', [$origen])
                ->whereRaw('LOWER(ciudad_destino) = ?', [$destino])
                ->exists()) {
            return $this->json($response, false, 'Ya existe una ruta con ese origen y destino.', null, 409);
        }

        $ruta = Ruta::create([
            'ciudad_origen' => trim($body['ciudad_origen']),
            'ciudad_destino' => trim($body['ciudad_destino']),
            'distancia' => $distancia,
            'tiempo_estimado' => $tiempoEstimado,
            'observaciones' => trim($body['observaciones'] ?? ''),
        ]);

        return $this->json($response, true, 'Ruta creada correctamente.', $ruta, 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $ruta = Ruta::find((int) $args['id']);

        if (!$ruta) {
            return $this->json($response, false, 'Ruta no encontrada.', null, 404);
        }

        $body = (array) $request->getParsedBody();

        if (array_key_exists('distancia_km', $body)) {
            $body['distancia'] = $body['distancia_km'];
        }

        if (array_key_exists('tiempo_estimado_horas', $body)) {
            $body['tiempo_estimado'] = $body['tiempo_estimado_horas'];
        }

        if (isset($body['distancia']) && (float) $body['distancia'] <= 0) {
            return $this->json($response, false, 'La distancia debe ser mayor a cero.', null, 422);
        }

        $nuevoOrigen  = strtolower(trim($body['ciudad_origen']  ?? $ruta->ciudad_origen));
        $nuevoDestino = strtolower(trim($body['ciudad_destino'] ?? $ruta->ciudad_destino));

        $actualOrigen  = strtolower($ruta->ciudad_origen);
        $actualDestino = strtolower($ruta->ciudad_destino);

        if (($nuevoOrigen !== $actualOrigen || $nuevoDestino !== $actualDestino)) {
            if (Ruta::whereRaw('LOWER(ciudad_origen) = ?', [$nuevoOrigen])
                    ->whereRaw('LOWER(ciudad_destino) = ?', [$nuevoDestino])
                    ->where('id', '!=', $ruta->id)
                    ->exists()) {
                return $this->json($response, false, 'Ya existe una ruta con ese origen y destino.', null, 409);
            }
        }

        $campos = ['ciudad_origen', 'ciudad_destino', 'distancia', 'tiempo_estimado', 'observaciones'];
        foreach ($campos as $campo) {
            if (array_key_exists($campo, $body)) {
                $ruta->$campo = is_string($body[$campo]) ? trim($body[$campo]) : $body[$campo];
            }
        }

        $ruta->save();

        return $this->json($response, true, 'Ruta actualizada correctamente.', $ruta);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $ruta = Ruta::find((int) $args['id']);

        if (!$ruta) {
            return $this->json($response, false, 'Ruta no encontrada.', null, 404);
        }

        $tieneViajes = $ruta->programaciones()
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->exists();

        if ($tieneViajes) {
            return $this->json($response, false, 'No se puede eliminar una ruta con viajes activos.', null, 409);
        }

        $ruta->delete();

        return $this->json($response, true, 'Ruta eliminada correctamente.', null);
    }

    private function json(Response $response, bool $success, string $message, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}