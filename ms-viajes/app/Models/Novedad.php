<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    protected $table = 'seguimientos_viajes';

    protected $fillable = [
        'programacion_viaje_id',
        'fecha',
        'hora',
        'estado',
        'novedad',
    ];

    public const TIPOS = ['retraso', 'incidente', 'observacion', 'cambio_operativo'];
}