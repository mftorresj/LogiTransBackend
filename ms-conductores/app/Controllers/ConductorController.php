<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Conductor;
use Illuminate\Support\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConductorController
{

    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $query = Conductor::query();

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        if (!empty($params['documento'])) {
            $query->where('documento', 'like', '%' . $params['documento'] . '%');
        }

        if (!empty($params['licencia'])) {
            $query->where('numero_licencia', 'like', '%' . $params['licencia'] . '%');
        }

        if (!empty($params['search'])) {
            $s = $params['search'];
            $query->where(function ($q) use ($s) {
                $q->where('nombres', 'like', "%{$s}%")
                  ->orWhere('apellidos', 'like', "%{$s}%")
                  ->orWhere('documento', 'like', "%{$s}%")
                  ->orWhere('correo', 'like', "%{$s}%");
            });
        }

        $conductores = $query->orderBy('nombres')->get();

        return $this->json($response, true, 'Conductores obtenidos correctamente.', $conductores);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $conductor = Conductor::find((int) $args['id']);

        if (!$conductor) {
            return $this->json($response, false, 'Conductor no encontrado.', null, 404);
        }

        return $this->json($response, true, 'Conductor obtenido.', $conductor);
    }

    public function store(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        $errores = $this->validarCamposObligatorios($body);
        if (!empty($errores)) {
            return $this->json($response, false, implode(' ', $errores), null, 422);
        }

        if (Conductor::where('documento', $body['documento'])->exists()) {
            return $this->json($response, false, 'El documento ya está registrado.', null, 409);
        }

        if (Conductor::where('numero_licencia', $body['numero_licencia'])->exists()) {
            return $this->json($response, false, 'El número de licencia ya está registrado.', null, 409);
        }

        if (!empty($body['correo']) && Conductor::where('correo', $body['correo'])->exists()) {
            return $this->json($response, false, 'El correo ya está registrado.', null, 409);
        }

        if (!empty($body['fecha_vencimiento_licencia'])) {
            if (!$this->esDateValida($body['fecha_vencimiento_licencia'])) {
                return $this->json($response, false, 'Formato de fecha de vencimiento inválido (YYYY-MM-DD).', null, 422);
            }
        }

        $conductor = Conductor::create([
            'nombres'                    => trim($body['nombres']),
            'apellidos'                  => trim($body['apellidos']),
            'documento'                  => trim($body['documento']),
            'telefono'                   => trim($body['telefono'] ?? ''),
            'correo'                     => trim($body['correo'] ?? ''),
            'numero_licencia'            => trim($body['numero_licencia']),
            'categoria_licencia'         => trim($body['categoria_licencia'] ?? ''),
            'fecha_vencimiento_licencia' => $body['fecha_vencimiento_licencia'] ?? Carbon::now(),
            'estado'                     => $body['estado'] ?? 'disponible',
        ]);

        return $this->json($response, true, 'Conductor creado correctamente.', $conductor, 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $conductor = Conductor::find((int) $args['id']);

        if (!$conductor) {
            return $this->json($response, false, 'Conductor no encontrado.', null, 404);
        }

        $body = (array) $request->getParsedBody();

        if (!empty($body['documento']) && $body['documento'] !== $conductor->documento) {
            if (Conductor::where('documento', $body['documento'])->where('id', '!=', $conductor->id)->exists()) {
                return $this->json($response, false, 'El documento ya está registrado por otro conductor.', null, 409);
            }
        }

        if (!empty($body['numero_licencia']) && $body['numero_licencia'] !== $conductor->numero_licencia) {
            if (Conductor::where('numero_licencia', $body['numero_licencia'])->where('id', '!=', $conductor->id)->exists()) {
                return $this->json($response, false, 'La licencia ya está registrada por otro conductor.', null, 409);
            }
        }

        if (!empty($body['correo']) && $body['correo'] !== $conductor->correo) {
            if (Conductor::where('correo', $body['correo'])->where('id', '!=', $conductor->id)->exists()) {
                return $this->json($response, false, 'El correo ya está registrado por otro conductor.', null, 409);
            }
        }

        if (!empty($body['estado']) && !in_array($body['estado'], Conductor::ESTADOS)) {
            return $this->json($response, false, 'Estado inválido. Use: ' . implode(', ', Conductor::ESTADOS), null, 422);
        }

        if (!empty($body['fecha_vencimiento_licencia'])) {
            if (!$this->esDateValida($body['fecha_vencimiento_licencia'])) {
                return $this->json($response, false, 'Formato de fecha inválido (YYYY-MM-DD).', null, 422);
            }
        }

        $campos = ['nombres','apellidos','documento','telefono','correo',
                   'numero_licencia','categoria_licencia','fecha_vencimiento_licencia','estado'];

        foreach ($campos as $campo) {
            if (array_key_exists($campo, $body)) {
                $conductor->$campo = is_string($body[$campo]) ? trim($body[$campo]) : $body[$campo];
            }
        }

        $conductor->save();

        return $this->json($response, true, 'Conductor actualizado correctamente.', $conductor);
    }

    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $conductor = Conductor::find((int) $args['id']);

        if (!$conductor) {
            return $this->json($response, false, 'Conductor no encontrado.', null, 404);
        }

        $body   = (array) $request->getParsedBody();
        $estado = $body['estado'] ?? '';

        if (!in_array($estado, Conductor::ESTADOS)) {
            return $this->json(
                $response, false,
                'Estado inválido. Use: ' . implode(', ', Conductor::ESTADOS),
                null, 422
            );
        }

        $conductor->estado = $estado;
        $conductor->save();

        return $this->json($response, true, "Estado cambiado a '{$estado}' correctamente.", $conductor);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $conductor = Conductor::find((int) $args['id']);

        if (!$conductor) {
            return $this->json($response, false, 'Conductor no encontrado.', null, 404);
        }

        if ($conductor->estado === 'en_ruta') {
            return $this->json($response, false, 'No se puede eliminar un conductor que está en ruta.', null, 409);
        }

        $conductor->delete();

        return $this->json($response, true, 'Conductor eliminado correctamente.', null);
    }

    private function validarCamposObligatorios(array $body): array
    {
        $errores = [];
        $requeridos = ['nombres', 'apellidos', 'documento', 'numero_licencia'];

        foreach ($requeridos as $campo) {
            if (empty($body[$campo])) {
                $errores[] = "El campo '{$campo}' es obligatorio.";
            }
        }

        return $errores;
    }

    private function esDateValida(string $fecha): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
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