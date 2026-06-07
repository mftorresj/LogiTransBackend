<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Viaje;
use App\Models\Novedad;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ViajeController
{
    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Viaje::with('novedades');

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        if (!empty($params['conductor_id'])) {
            $query->where('conductor_id', (int) $params['conductor_id']);
        }

        if (!empty($params['vehiculo_id'])) {
            $query->where('vehiculo_id', (int) $params['vehiculo_id']);
        }

        if (!empty($params['programacion_id'])) {
            $query->where('programacion_id', (int) $params['programacion_id']);
        }

        $viajes = $query->orderBy('created_at', 'desc')->get();

        return $this->json($response, true, 'Viajes obtenidos correctamente.', $viajes);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $viaje = Viaje::with('novedades')->find((int) $args['id']);

        if (!$viaje) {
            return $this->json($response, false, 'Viaje no encontrado.', null, 404);
        }

        return $this->json($response, true, 'Viaje obtenido.', $viaje);
    }

    public function store(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        $errores = [];
        foreach (['programacion_id', 'conductor_id', 'vehiculo_id', 'ruta_id'] as $campo) {
            if (empty($body[$campo])) {
                $errores[] = "El campo '{$campo}' es obligatorio.";
            }
        }

        if (!empty($errores)) {
            return $this->json($response, false, implode(' ', $errores), null, 422);
        }

        $existente = Viaje::where('programacion_id', (int) $body['programacion_id'])
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->first();

        if ($existente) {
            return $this->json($response, false, 'Ya existe un viaje activo para esta programación.', null, 409);
        }

        $viaje = Viaje::create([
            'programacion_id' => (int) $body['programacion_id'],
            'conductor_id'    => (int) $body['conductor_id'],
            'vehiculo_id'     => (int) $body['vehiculo_id'],
            'ruta_id'         => (int) $body['ruta_id'],
            'estado'          => 'programado',
            'observaciones'   => trim($body['observaciones'] ?? ''),
        ]);

        return $this->json($response, true, 'Viaje registrado correctamente.', $viaje, 201);
    }

    public function iniciar(Request $request, Response $response, array $args): Response
    {
        $viaje = Viaje::find((int) $args['id']);

        if (!$viaje) {
            return $this->json($response, false, 'Viaje no encontrado.', null, 404);
        }

        if ($viaje->estado === 'cancelado') {
            return $this->json($response, false, 'No se puede iniciar un viaje cancelado.', null, 409);
        }

        if ($viaje->estado !== 'programado') {
            return $this->json($response, false, "El viaje ya está en estado '{$viaje->estado}'. Solo se pueden iniciar viajes programados.", null, 409);
        }

        $viaje->estado       = 'en_transito';
        $viaje->fecha_inicio = now();
        $viaje->save();

        return $this->json($response, true, 'Viaje iniciado correctamente.', $viaje);
    }

    public function actualizarEstado(Request $request, Response $response, array $args): Response
    {
        $viaje = Viaje::find((int) $args['id']);

        if (!$viaje) {
            return $this->json($response, false, 'Viaje no encontrado.', null, 404);
        }

        $body   = (array) $request->getParsedBody();
        $estado = $body['estado'] ?? '';

        if (!in_array($estado, Viaje::ESTADOS)) {
            return $this->json($response, false, 'Estado inválido. Use: ' . implode(', ', Viaje::ESTADOS), null, 422);
        }

        if ($estado === 'finalizado' && !in_array($viaje->estado, ['en_transito', 'retrasado'])) {
            return $this->json($response, false, 'Solo se pueden finalizar viajes que estén en tránsito o retrasados.', null, 409);
        }

        if ($estado === 'cancelado' && $viaje->estado === 'finalizado') {
            return $this->json($response, false, 'No se puede cancelar un viaje ya finalizado.', null, 409);
        }

        $viaje->estado = $estado;

        if ($estado === 'finalizado') {
            $viaje->fecha_fin = now();
        }

        $viaje->save();

        return $this->json($response, true, "Estado actualizado a '{$estado}' correctamente.", $viaje);
    }

    public function finalizar(Request $request, Response $response, array $args): Response
    {
        $viaje = Viaje::find((int) $args['id']);

        if (!$viaje) {
            return $this->json($response, false, 'Viaje no encontrado.', null, 404);
        }

        if (!in_array($viaje->estado, ['en_transito', 'retrasado'])) {
            return $this->json($response, false, 'Solo se pueden finalizar viajes en tránsito o retrasados.', null, 409);
        }

        $body = (array) $request->getParsedBody();

        $viaje->estado       = 'finalizado';
        $viaje->fecha_fin    = now();
        $viaje->observaciones = trim($body['observaciones'] ?? $viaje->observaciones ?? '');
        $viaje->save();

        return $this->json($response, true, 'Viaje finalizado correctamente.', $viaje);
    }

    public function registrarNovedad(Request $request, Response $response, array $args): Response
    {
        $viaje = Viaje::find((int) $args['id']);

        if (!$viaje) {
            return $this->json($response, false, 'Viaje no encontrado.', null, 404);
        }

        if (in_array($viaje->estado, ['finalizado', 'cancelado'])) {
            return $this->json($response, false, 'No se pueden registrar novedades en un viaje finalizado o cancelado.', null, 409);
        }

        $body = (array) $request->getParsedBody();

        if (empty($body['descripcion'])) {
            return $this->json($response, false, "El campo 'descripcion' es obligatorio.", null, 422);
        }

        $tipo = $body['tipo'] ?? 'observacion';
        if (!in_array($tipo, Novedad::TIPOS)) {
            return $this->json($response, false, 'Tipo inválido. Use: ' . implode(', ', Novedad::TIPOS), null, 422);
        }

        $novedad = Novedad::create([
            'viaje_id'       => $viaje->id,
            'tipo'           => $tipo,
            'descripcion'    => trim($body['descripcion']),
            'registrado_por' => trim($body['registrado_por'] ?? 'Sistema'),
        ]);

        if ($tipo === 'retraso' && $viaje->estado === 'en_transito') {
            $viaje->estado = 'retrasado';
            $viaje->save();
        }

        return $this->json($response, true, 'Novedad registrada correctamente.', $novedad, 201);
    }

    public function seguimiento(Request $request, Response $response, array $args): Response
    {
        $viaje = Viaje::with('novedades')->find((int) $args['id']);

        if (!$viaje) {
            return $this->json($response, false, 'Viaje no encontrado.', null, 404);
        }

        return $this->json($response, true, 'Seguimiento obtenido.', [
            'viaje'     => $viaje,
            'novedades' => $viaje->novedades,
            'resumen'   => [
                'total_novedades' => $viaje->novedades->count(),
                'retrasos'        => $viaje->novedades->where('tipo', 'retraso')->count(),
                'incidentes'      => $viaje->novedades->where('tipo', 'incidente')->count(),
            ],
        ]);
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