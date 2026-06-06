<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = 'vehiculos';

    protected $fillable = [
        'placa',
        'tipo',
        'capacidad_carga',
        'modelo',
        'marca',
        'estado',
    ];

    protected $casts = [
        'capacidad_carga' => 'float',
    ];

    public const ESTADOS = ['disponible', 'en_ruta', 'mantenimiento', 'inactivo'];
    public const TIPOS   = ['camion', 'furgon', 'tractomula', 'camioneta', 'otro'];
}