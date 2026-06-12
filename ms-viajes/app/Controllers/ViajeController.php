<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Viaje;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ViajeController
{
    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Viaje::query();

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        if (!empty($params['programacion_viaje_id'])) {
            $query->where('programacion_viaje_id', (int) $params['programacion_viaje_id']);
        }

        if (!empty($params['fecha'])) {
            $query->whereDate('fecha', $params['fecha']);
        }

        $viajes = $query->orderBy('created_at', 'desc')->get();

        return $this->json($response, true, 'Viajes obtenidos correctamente.', $viajes);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $viaje = Viaje::find((int) $args['id']);

        if (!$viaje) {
            return $this->json($response, false, 'Viaje no encontrado.', null, 404);
        }

        return $this->json($response, true, 'Viaje obtenido.', $viaje);
    }

    public function store(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        $errores = [];
        foreach (['programacion_viaje_id', 'fecha', 'hora'] as $campo) {
            if (empty($body[$campo])) {
                $errores[] = "El campo '{$campo}' es obligatorio.";
            }
        }

        if (!empty($errores)) {
            return $this->json($response, false, implode(' ', $errores), null, 422);
        }

        $estado = trim($body['estado'] ?? 'programado');
        if (!in_array($estado, Viaje::ESTADOS, true)) {
            return $this->json($response, false, 'Estado inválido. Use: ' . implode(', ', Viaje::ESTADOS), null, 422);
        }

        $viaje = Viaje::create([
            'programacion_viaje_id' => (int) $body['programacion_viaje_id'],
            'fecha'                 => $body['fecha'],
            'hora'                  => $body['hora'],
            'estado'                => $estado,
            'novedad'               => trim($body['novedad'] ?? ''),
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

        $viaje->estado = 'en_transito';
        $viaje->fecha  = $viaje->fecha ?: Carbon::today()->toDateString();
        $viaje->hora   = $viaje->hora ?: Carbon::now()->toTimeString();
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
            $viaje->fecha = $viaje->fecha ?: Carbon::today()->toDateString();
            $viaje->hora  = $viaje->hora ?: Carbon::now()->toTimeString();
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

        $viaje->estado = 'finalizado';
        $viaje->fecha  = $viaje->fecha ?: Carbon::today()->toDateString();
        $viaje->hora   = $viaje->hora ?: Carbon::now()->toTimeString();
        $viaje->novedad = trim($body['novedad'] ?? $viaje->novedad ?? '');
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
        $descripcion = trim($body['descripcion']);

        if ($tipo === 'retraso' && $viaje->estado === 'en_transito') {
            $viaje->estado = 'retrasado';
        }

        $texto = $descripcion;
        if (!empty($viaje->novedad)) {
            $texto = $viaje->novedad . "\n" . $texto;
        }

        $viaje->novedad = $texto;
        $viaje->save();

        return $this->json($response, true, 'Novedad registrada correctamente.', $viaje, 201);
    }

    public function seguimiento(Request $request, Response $response, array $args): Response
    {
        $viaje = Viaje::find((int) $args['id']);

        if (!$viaje) {
            return $this->json($response, false, 'Viaje no encontrado.', null, 404);
        }

        return $this->json($response, true, 'Seguimiento obtenido.', [
            'viaje' => $viaje,
            'novedades' => !empty($viaje->novedad) ? explode("\n", $viaje->novedad) : [],
            'resumen' => [
                'total_novedades' => !empty($viaje->novedad) ? 1 : 0,
                'retrasos' => stripos((string) $viaje->novedad, 'retraso') !== false ? 1 : 0,
                'incidentes' => stripos((string) $viaje->novedad, 'incidente') !== false ? 1 : 0,
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