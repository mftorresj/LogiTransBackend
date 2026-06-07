<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Vehiculo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VehiculoController
{
    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Vehiculo::query();

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        if (!empty($params['tipo'])) {
            $query->where('tipo_vehiculo', 'like', '%' . $params['tipo'] . '%');
        }

        if (!empty($params['placa'])) {
            $query->where('placa', 'like', '%' . $params['placa'] . '%');
        }

        if (!empty($params['search'])) {
            $s = $params['search'];
            $query->where(function ($q) use ($s) {
                $q->where('placa', 'like', "%{$s}%")
                  ->orWhere('marca', 'like', "%{$s}%")
                  ->orWhere('modelo', 'like', "%{$s}%");
            });
        }

        $vehiculos = $query->orderBy('placa')->get();

        return $this->json($response, true, 'Vehículos obtenidos correctamente.', $vehiculos);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $vehiculo = Vehiculo::find((int) $args['id']);

        if (!$vehiculo) {
            return $this->json($response, false, 'Vehículo no encontrado.', null, 404);
        }

        return $this->json($response, true, 'Vehículo obtenido.', $vehiculo);
    }

    public function store(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        $tipoVehiculo = trim($body['tipo_vehiculo'] ?? $body['tipo'] ?? '');

        $errores = [];
        foreach (['placa', 'capacidad_carga', 'marca', 'modelo'] as $campo) {
            if (empty($body[$campo]) && $body[$campo] !== '0') {
                $errores[] = "El campo '{$campo}' es obligatorio.";
            }
        }

        if ($tipoVehiculo === '') {
            $errores[] = "El campo 'tipo_vehiculo' es obligatorio.";
        }

        if (!empty($errores)) {
            return $this->json($response, false, implode(' ', $errores), null, 422);
        }

        $capacidad = (float) ($body['capacidad_carga'] ?? 0);
        if ($capacidad <= 0) {
            return $this->json($response, false, 'La capacidad de carga debe ser mayor a cero.', null, 422);
        }

        $placa = strtoupper(trim($body['placa']));
        if (Vehiculo::where('placa', $placa)->exists()) {
            return $this->json($response, false, 'La placa ya está registrada.', null, 409);
        }

        $estado = $body['estado'] ?? 'disponible';
        if (!in_array($estado, Vehiculo::ESTADOS)) {
            return $this->json($response, false, 'Estado inválido. Use: ' . implode(', ', Vehiculo::ESTADOS), null, 422);
        }

        if (!in_array($tipoVehiculo, Vehiculo::TIPOS)) {
            return $this->json($response, false, 'Tipo inválido. Use: ' . implode(', ', Vehiculo::TIPOS), null, 422);
        }

        $vehiculo = Vehiculo::create([
            'placa'           => $placa,
            'tipo_vehiculo'   => $tipoVehiculo,
            'capacidad_carga' => $capacidad,
            'modelo'          => trim($body['modelo']),
            'marca'           => trim($body['marca']),
            'estado'          => $estado,
        ]);

        return $this->json($response, true, 'Vehículo creado correctamente.', $vehiculo, 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $vehiculo = Vehiculo::find((int) $args['id']);

        if (!$vehiculo) {
            return $this->json($response, false, 'Vehículo no encontrado.', null, 404);
        }

        $body = (array) $request->getParsedBody();

        if (!empty($body['placa'])) {
            $placa = strtoupper(trim($body['placa']));
            if ($placa !== $vehiculo->placa && Vehiculo::where('placa', $placa)->where('id', '!=', $vehiculo->id)->exists()) {
                return $this->json($response, false, 'La placa ya está registrada en otro vehículo.', null, 409);
            }
            $body['placa'] = $placa;
        }

        if (isset($body['capacidad_carga']) && (float) $body['capacidad_carga'] <= 0) {
            return $this->json($response, false, 'La capacidad debe ser mayor a cero.', null, 422);
        }

        if (!empty($body['estado']) && !in_array($body['estado'], Vehiculo::ESTADOS)) {
            return $this->json($response, false, 'Estado inválido.', null, 422);
        }

        if (array_key_exists('tipo_vehiculo', $body) || array_key_exists('tipo', $body)) {
            $body['tipo_vehiculo'] = trim($body['tipo_vehiculo'] ?? $body['tipo'] ?? '');
        }

        $campos = ['placa', 'tipo_vehiculo', 'capacidad_carga', 'modelo', 'marca', 'estado'];
        foreach ($campos as $campo) {
            if (array_key_exists($campo, $body)) {                
                $vehiculo->$campo = is_string($body[$campo]) ? trim($body[$campo]) : $body[$campo];
            }
        }

        $vehiculo->save();

        return $this->json($response, true, 'Vehículo actualizado correctamente.', $vehiculo);
    }

    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $vehiculo = Vehiculo::find((int) $args['id']);

        if (!$vehiculo) {
            return $this->json($response, false, 'Vehículo no encontrado.', null, 404);
        }

        $body   = (array) $request->getParsedBody();
        $estado = $body['estado'] ?? '';

        if (!in_array($estado, Vehiculo::ESTADOS)) {
            return $this->json(
                $response, false,
                'Estado inválido. Use: ' . implode(', ', Vehiculo::ESTADOS),
                null, 422
            );
        }

        $vehiculo->estado = $estado;
        $vehiculo->save();

        return $this->json($response, true, "Estado cambiado a '{$estado}' correctamente.", $vehiculo);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $vehiculo = Vehiculo::find((int) $args['id']);

        if (!$vehiculo) {
            return $this->json($response, false, 'Vehículo no encontrado.', null, 404);
        }

        if ($vehiculo->estado === 'en_ruta') {
            return $this->json($response, false, 'No se puede eliminar un vehículo que está en ruta.', null, 409);
        }

        $vehiculo->delete();

        return $this->json($response, true, 'Vehículo eliminado correctamente.', null);
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