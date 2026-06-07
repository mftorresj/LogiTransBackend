<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programacion extends Model
{
    protected $table = 'programaciones_viajes';

    protected $fillable = [
        'conductor_id',
        'vehiculo_id',
        'ruta_id',
        'fecha_salida',
        'hora_salida',
        'fecha_estimada_llegada',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha_salida'           => 'date',
        'fecha_estimada_llegada' => 'date',
    ];

    public const ESTADOS = ['programado', 'en_transito', 'retrasado', 'finalizado', 'cancelado'];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }
}