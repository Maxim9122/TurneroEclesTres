<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoHistorial extends Model
{
    use HasFactory;

    protected $table = 'pedido_historial';

    protected $fillable = [
        'pedido_id', 'usuario_id', 'motivo', 'items_anterior',
        'total_anterior', 'metodo_entrega_anterior', 'direccion_envio_id_anterior',
    ];

    protected function casts(): array
    {
        return [
            'items_anterior' => 'array',
            'total_anterior' => 'decimal:2',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}