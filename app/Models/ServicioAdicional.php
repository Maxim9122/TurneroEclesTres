<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioAdicional extends Model
{
    protected $table = 'servicio_adicionales';

    protected $fillable = [
        'servicio_id', 'nombre', 'precio', 'duracion_minutos', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'precio' => 'decimal:2',
        ];
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    /** Texto corto para mostrar junto al nombre: "+$2.000 · +10 min". */
    public function detalle(): string
    {
        $partes = ['+$' . number_format((float) $this->precio, 0, ',', '.')];

        if ($this->duracion_minutos > 0) {
            $partes[] = '+' . $this->duracion_minutos . ' min';
        }

        return implode(' · ', $partes);
    }
}
