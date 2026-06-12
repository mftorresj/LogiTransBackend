<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Viaje extends Model
{
    protected $table = 'seguimientos_viajes';

    protected $fillable = [
        'programacion_viaje_id',
        'fecha',
        'hora',
        'estado',
        'novedad',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora'  => 'string',
    ];

    public const ESTADOS = ['programado', 'en_transito', 'retrasado', 'finalizado', 'cancelado'];
}