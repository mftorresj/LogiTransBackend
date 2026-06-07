<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'correo',
        'usuario',
        'contrasena',
        'rol',
        'token',
        'sesion_activa',
        'estado',
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected $casts = [
        'sesion_activa' => 'boolean',
    ];

    public static function findByCredential(string $credential): ?self
    {
        return static::where('usuario', $credential)
            ->orWhere('correo', $credential)
            ->first();
    }

    public static function findByToken(string $token): ?self
    {
        return static::where('token', $token)
            ->where('sesion_activa', true)
            ->where('estado', 'activo')
            ->first();
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}