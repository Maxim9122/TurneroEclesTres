<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'cliente_id', 'metodo_entrega', 'direccion_envio_id',
        'estado', 'total', 'notas',
    ];

    protected function casts(): array
    {
        return ['total' => 'decimal:2'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function direccionEnvio(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'direccion_envio_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
    }
}