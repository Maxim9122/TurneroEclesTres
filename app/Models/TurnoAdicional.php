<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TurnoAdicional extends Model
{
    protected $table = 'turno_adicionales';

    protected $fillable = [
        'turno_id', 'servicio_id', 'servicio_adicional_id',
        'nombre_al_momento', 'precio_al_momento', 'duracion_al_momento',
    ];

    protected function casts(): array
    {
        return ['precio_al_momento' => 'decimal:2'];
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    public function adicional(): BelongsTo
    {
        return $this->belongsTo(ServicioAdicional::class, 'servicio_adicional_id');
    }
}
