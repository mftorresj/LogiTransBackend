<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Programacion;
use App\Models\Ruta;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProgramacionController
{
    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Programacion::with('ruta');

        if (!empty($params['conductor_id'])) {
            $query->where('conductor_id', (int) $params['conductor_id']);
        }

        if (!empty($params['vehiculo_id'])) {
            $query->where('vehiculo_id', (int) $params['vehiculo_id']);
        }

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        if (!empty($params['fecha'])) {
            $query->whereDate('fecha_salida', $params['fecha']);
        }

        if (!empty($params['ruta_id'])) {
            $query->where('ruta_id', (int) $params['ruta_id']);
        }

        $programacion = $query->orderBy('fecha_salida', 'desc')->get();

        return $this->json($response, true, 'Programación obtenida correctamente.', $programacion);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $prog = Programacion::with('ruta')->find((int) $args['id']);

        if (!$prog) {
            return $this->json($response, false, 'Programación no encontrada.', null, 404);
        }

        return $this->json($response, true, 'Programación obtenida.', $prog);
    }

    public function store(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        $errores = [];
        foreach (['conductor_id', 'vehiculo_id', 'ruta_id', 'fecha_salida', 'hora_salida'] as $campo) {
            if (empty($body[$campo])) {
                $errores[] = "El campo '{$campo}' es obligatorio.";
            }
        }

        if (!empty($errores)) {
            return $this->json($response, false, implode(' ', $errores), null, 422);
        }

        $ruta = Ruta::find((int) $body['ruta_id']);
        if (!$ruta) {
            return $this->json($response, false, 'La ruta especificada no existe.', null, 404);
        }

        if (!$this->esDateValida($body['fecha_salida'])) {
            return $this->json($response, false, 'Formato de fecha de salida inválido (YYYY-MM-DD).', null, 422);
        }

        if (!empty($body['fecha_estimada_llegada']) && !$this->esDateValida($body['fecha_estimada_llegada'])) {
            return $this->json($response, false, 'Formato de fecha estimada de llegada inválido.', null, 422);
        }

        $conductorOcupado = Programacion::where('conductor_id', (int) $body['conductor_id'])
            ->whereIn('estado', ['programado', 'en_transito', 'retrasado'])
            ->exists();

        if ($conductorOcupado) {
            return $this->json($response, false, 'El conductor ya tiene un viaje activo asignado.', null, 409);
        }

        $vehiculoOcupado = Programacion::where('vehiculo_id', (int) $body['vehiculo_id'])
            ->whereIn('estado', ['programado', 'en_transito', 'retrasado'])
            ->exists();

        if ($vehiculoOcupado) {
            return $this->json($response, false, 'El vehículo ya tiene un viaje activo asignado.', null, 409);
        }

        $prog = Programacion::create([
            'conductor_id'           => (int) $body['conductor_id'],
            'vehiculo_id'            => (int) $body['vehiculo_id'],
            'ruta_id'                => (int) $body['ruta_id'],
            'fecha_salida'           => $body['fecha_salida'],
            'hora_salida'            => $body['hora_salida'],
            'fecha_estimada_llegada' => $body['fecha_estimada_llegada'] ?? null,
            'observaciones'          => trim($body['observaciones'] ?? ''),
            'estado'                 => 'programado',
        ]);

        return $this->json($response, true, 'Viaje programado correctamente.', $prog->load('ruta'), 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $prog = Programacion::find((int) $args['id']);

        if (!$prog) {
            return $this->json($response, false, 'Programación no encontrada.', null, 404);
        }

        if (!in_array($prog->estado, ['programado'])) {
            return $this->json($response, false, "No se puede reprogramar un viaje en estado '{$prog->estado}'.", null, 409);
        }

        $body = (array) $request->getParsedBody();

        if (!empty($body['ruta_id'])) {
            $ruta = Ruta::find((int) $body['ruta_id']);
            if (!$ruta) {
                return $this->json($response, false, 'La ruta especificada no existe.', null, 404);
            }
        }

        if (!empty($body['fecha_salida']) && !$this->esDateValida($body['fecha_salida'])) {
            return $this->json($response, false, 'Formato de fecha de salida inválido.', null, 422);
        }

        if (!empty($body['conductor_id']) && (int) $body['conductor_id'] !== $prog->conductor_id) {
            $ocupado = Programacion::where('conductor_id', (int) $body['conductor_id'])
                ->whereIn('estado', ['programado', 'en_transito', 'retrasado'])
                ->where('id', '!=', $prog->id)
                ->exists();

            if ($ocupado) {
                return $this->json($response, false, 'El conductor ya tiene un viaje activo.', null, 409);
            }
        }

        if (!empty($body['vehiculo_id']) && (int) $body['vehiculo_id'] !== $prog->vehiculo_id) {
            $ocupado = Programacion::where('vehiculo_id', (int) $body['vehiculo_id'])
                ->whereIn('estado', ['programado', 'en_transito', 'retrasado'])
                ->where('id', '!=', $prog->id)
                ->exists();

            if ($ocupado) {
                return $this->json($response, false, 'El vehículo ya tiene un viaje activo.', null, 409);
            }
        }

        $campos = ['conductor_id', 'vehiculo_id', 'ruta_id', 'fecha_salida',
                   'hora_salida', 'fecha_estimada_llegada', 'observaciones', 'estado'];

        foreach ($campos as $campo) {
            if (array_key_exists($campo, $body)) {
                $prog->$campo = is_string($body[$campo]) ? trim($body[$campo]) : $body[$campo];
            }
        }

        $prog->save();

        return $this->json($response, true, 'Viaje reprogramado correctamente.', $prog->load('ruta'));
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $prog = Programacion::find((int) $args['id']);

        if (!$prog) {
            return $this->json($response, false, 'Programación no encontrada.', null, 404);
        }

        if (!in_array($prog->estado, ['programado', 'cancelado'])) {
            return $this->json($response, false, 'Solo se pueden eliminar programaciones canceladas o pendientes.', null, 409);
        }

        $prog->delete();

        return $this->json($response, true, 'Programación eliminada.', null);
    }

    private function esDateValida(string $fecha): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
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