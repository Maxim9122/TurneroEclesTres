<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebauthnCredencial extends Model
{
    protected $table = 'webauthn_credenciales';

    protected $fillable = [
        'usuario_id', 'credencial_hash', 'registro', 'nombre', 'ultimo_uso_at',
    ];

    protected $hidden = ['registro'];

    protected function casts(): array
    {
        return ['ultimo_uso_at' => 'datetime'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public static function hashId(string $credencialIdBinario): string
    {
        return hash('sha256', $credencialIdBinario);
    }
}
