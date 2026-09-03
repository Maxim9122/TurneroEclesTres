<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'empresa_id', 'nombre', 'email', 'password',
        'rol', 'activo', 'avatar_path',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function profesional(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Profesional::class);
    }

    public function esSuperAdmin(): bool
    {
        return $this->rol === 'super_admin';
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esOperador(): bool
    {
        return $this->rol === 'operador';
    }
}