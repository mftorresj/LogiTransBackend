<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    protected $table = 'novedades';

    protected $fillable = [
        'viaje_id',
        'tipo',
        'descripcion',
        'registrado_por',
    ];

    public const TIPOS = ['retraso', 'incidente', 'observacion', 'cambio_operativo'];

    public function viaje()
    {
        return $this->belongsTo(Viaje::class, 'viaje_id');
    }
}