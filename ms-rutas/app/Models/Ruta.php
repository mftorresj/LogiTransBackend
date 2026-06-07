<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    protected $table = 'rutas';

    protected $fillable = [
        'ciudad_origen',
        'ciudad_destino',
        'distancia',
        'tiempo_estimado',
        'observaciones',
    ];

    protected $casts = [
        'distancia' => 'float',
        'tiempo_estimado' => 'string',
    ];

    public function programaciones()
    {
        return $this->hasMany(Programacion::class, 'ruta_id');
    }
}