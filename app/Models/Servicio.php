<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'nombre', 'duracion_minutos', 'precio', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'precio' => 'decimal:2',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function profesionales(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'servicio_profesional');
    }
}