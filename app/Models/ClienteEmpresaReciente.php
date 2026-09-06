<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteEmpresaReciente extends Model
{
    use HasFactory;

    protected $table = 'clientes_empresas_recientes';

    protected $fillable = [
        'cliente_id', 'empresa_id', 'ultima_interaccion',
    ];

    protected function casts(): array
    {
        return ['ultima_interaccion' => 'datetime'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Registra (o actualiza) la interacción de un cliente con una empresa,
     * y recorta el historial a un máximo de 3 empresas por cliente.
     */
    public static function registrar(int $clienteId, int $empresaId): void
    {
        static::updateOrCreate(
            ['cliente_id' => $clienteId, 'empresa_id' => $empresaId],
            ['ultima_interaccion' => now()]
        );

        $idsAConservar = static::where('cliente_id', $clienteId)
            ->orderByDesc('ultima_interaccion')
            ->limit(3)
            ->pluck('id');

        static::where('cliente_id', $clienteId)
            ->whereNotIn('id', $idsAConservar)
            ->delete();
    }
}