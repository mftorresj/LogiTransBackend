<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Viaje extends Model
{
    protected $table = 'viajes';

    protected $fillable = [
        'programacion_id',
        'conductor_id',
        'vehiculo_id',
        'ruta_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin'    => 'datetime',
    ];

    public const ESTADOS = ['programado', 'en_transito', 'retrasado', 'finalizado', 'cancelado'];

    public function novedades()
    {
        return $this->hasMany(Novedad::class, 'viaje_id')->orderBy('created_at', 'desc');
    }
}