<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    protected $table = 'conductores';

    protected $fillable = [
        'nombres',
        'apellidos',
        'documento',
        'telefono',
        'correo',
        'numero_licencia',
        'categoria_licencia',
        'fecha_vencimiento_licencia',
        'estado',
    ];

    protected $casts = [
        'fecha_vencimiento_licencia' => 'date',
    ];

    public const ESTADOS = ['disponible', 'en_ruta', 'inactivo'];

    public function scopeEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeDocumento($query, string $doc)
    {
        return $query->where('documento', 'like', "%{$doc}%");
    }

    public function scopeLicencia($query, string $lic)
    {
        return $query->where('numero_licencia', 'like', "%{$lic}%");
    }

    public function licenciaVencida(): bool
    {
        return $this->fecha_vencimiento_licencia < Carbon::now();
    }
}